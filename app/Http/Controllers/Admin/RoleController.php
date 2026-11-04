<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Http\Controllers\Controller;
use App\Services\RbacService;
use DomainException;
use Throwable;

final class RoleController extends Controller
{
    public function index(Request $request): Response
    {
        return $this->render('admin/roles/index', [
            'pageTitle'=>'Roles & permissions',
            'roles'=>app(RbacService::class)->roles(),
        ], 'layouts/admin');
    }

    public function form(Request $request): Response
    {
        $id = (int)$request->routeParam('id',0);
        $rbac = app(RbacService::class);
        $role = $id > 0 ? $rbac->role($id) : null;
        if ($id > 0 && $role === null) { abort(404, 'Role not found.'); }
        return $this->render('admin/roles/form', [
            'pageTitle'=>$role ? 'Edit role' : 'Create role',
            'role'=>$role,
            'permissions'=>$rbac->permissions(),
            'grants'=>$role ? $rbac->grants($id) : [],
        ], 'layouts/admin');
    }

    public function save(Request $request): Response
    {
        $id = (int)$request->routeParam('id',0);
        $grants = $request->input('permissions',[]);
        if (!is_array($grants)) { $grants=[]; }
        try {
            $roleId = app(RbacService::class)->saveRole($id ?: null,
                trim((string)$request->input('name','')),
                trim((string)$request->input('label','')),
                trim((string)$request->input('description','')),
                $grants, (int)$this->auth->id());
            $this->flashSuccess('Role and permissions saved.');
            return $this->redirect('/admin/roles/'.$roleId.'/edit');
        } catch (DomainException $e) {
            $this->flashError($e->getMessage());
        } catch (\PDOException $e) {
            $this->flashError('Role name is already in use or could not be saved.');
        }
        return $this->redirect($id ? '/admin/roles/'.$id.'/edit' : '/admin/roles/create');
    }

    public function delete(Request $request): Response
    {
        try {
            app(RbacService::class)->deleteRole((int)$request->routeParam('id',0), (int)$this->auth->id());
            $this->flashSuccess('Unused role deleted.');
        } catch (DomainException $e) { $this->flashError($e->getMessage()); }
        return $this->redirect('/admin/roles');
    }
}
