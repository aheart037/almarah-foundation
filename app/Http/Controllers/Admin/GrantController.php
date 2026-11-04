<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Http\Controllers\Controller;
use App\Services\GrantService;
use DomainException;

final class GrantController extends Controller
{
    public function index(Request $request): Response
    {
        return $this->render('admin/grants/index', ['pageTitle'=>'Grants','grants'=>app(GrantService::class)->all()], 'layouts/admin');
    }

    public function show(Request $request): Response
    {
        $id=(int)$request->routeParam('id',0);
        $service=app(GrantService::class);
        $grant=$service->find($id);
        if ($grant===null) { abort(404,'Grant not found.'); }
        $canAssignments=$this->auth->canAny(['grant_assignments.view','grant_assignments.manage']);
        return $this->render('admin/grants/show', ['pageTitle'=>$grant['title'],'grant'=>$grant,
            'assignments'=>$canAssignments ? $service->forGrant($id) : null,
            'assignmentEvents'=>$canAssignments ? $service->eventsForGrant($id) : [],
            'activeUsers'=>$this->auth->canAny(['grant_assignments.create','grant_assignments.manage']) ? $service->activeUsers() : [],
        ], 'layouts/admin');
    }

    public function form(Request $request): Response
    {
        $id=(int)$request->routeParam('id',0);
        $grant=$id>0 ? app(GrantService::class)->find($id) : null;
        if ($id>0 && $grant===null) { abort(404,'Grant not found.'); }
        return $this->render('admin/grants/form',['pageTitle'=>$grant ? 'Edit grant' : 'Create grant','grant'=>$grant], 'layouts/admin');
    }

    public function save(Request $request): Response
    {
        $id=(int)$request->routeParam('id',0);
        try {
            $id=app(GrantService::class)->save($id ?: null,(string)$request->input('reference',''),(string)$request->input('title',''),(string)$request->input('description',''),(string)$request->input('status','draft'),(int)$this->auth->id());
            $this->flashSuccess('Grant saved.');
            return $this->redirect('/admin/grants/'.$id);
        } catch (DomainException $e) { $this->flashError($e->getMessage()); }
        catch (\PDOException $e) { $this->flashError('Grant reference already exists or could not be saved.'); }
        return $this->redirect($id ? '/admin/grants/'.$id.'/edit' : '/admin/grants/create');
    }

    public function assignUsers(Request $request): Response
    {
        $id=(int)$request->routeParam('id',0);
        $values=$request->input('user_ids',[]);
        if (!is_array($values)) { $values=[$values]; }
        try {
            app(GrantService::class)->assignUsers($id,$values,trim((string)$request->input('responsibilities','')),trim((string)$request->input('notes','')),(int)$this->auth->id());
            $this->flashSuccess('Users assigned.');
        } catch (DomainException $e) { $this->flashError($e->getMessage()); }
        catch (\PDOException $e) { $this->flashError('Duplicate assignment or invalid user; no changes were saved.'); }
        return $this->redirect('/admin/grants/'.$id);
    }

    public function assignGrants(Request $request): Response
    {
        $userId=(int)$request->routeParam('id',0);
        $values=$request->input('grant_ids',[]);
        if (!is_array($values)) { $values=[$values]; }
        try {
            app(GrantService::class)->assignGrants($userId,$values,trim((string)$request->input('responsibilities','')),trim((string)$request->input('notes','')),(int)$this->auth->id());
            $this->flashSuccess('Grants assigned to user.');
        } catch (DomainException $e) { $this->flashError($e->getMessage()); }
        catch (\PDOException $e) { $this->flashError('Duplicate assignment or invalid grant; no changes were saved.'); }
        return $this->redirect('/admin/users/'.$userId);
    }

    public function updateAssignment(Request $request): Response
    {
        $grantId=(int)$request->routeParam('id',0);
        $userId=(int)$request->routeParam('userId',0);
        try {
            app(GrantService::class)->updateAssignment($grantId,$userId,(string)$request->input('status','active'),trim((string)$request->input('responsibilities','')),trim((string)$request->input('notes','')),(int)$this->auth->id());
            $this->flashSuccess('Assignment updated.');
        } catch (DomainException $e) { $this->flashError($e->getMessage()); }
        return $this->redirect('/admin/grants/'.$grantId);
    }

    public function changeAssignee(Request $request): Response
    {
        $grantId=(int)$request->routeParam('id',0);
        try {
            app(GrantService::class)->changeAssignee($grantId,(int)$request->routeParam('userId',0),(int)$request->input('new_user_id',0),trim((string)$request->input('responsibilities','')),trim((string)$request->input('notes','')),(int)$this->auth->id());
            $this->flashSuccess('Assignee changed; the former assignment remains in history.');
        } catch (DomainException $e) { $this->flashError($e->getMessage()); }
        catch (\PDOException $e) { $this->flashError('Assignee could not be changed.'); }
        return $this->redirect('/admin/grants/'.$grantId);
    }

    public function removeAssignment(Request $request): Response
    {
        $grantId=(int)$request->routeParam('id',0);
        try {
            app(GrantService::class)->remove($grantId,(int)$request->routeParam('userId',0),(int)$this->auth->id());
            $this->flashSuccess('Assignment removed; its history is retained.');
        } catch (DomainException $e) { $this->flashError($e->getMessage()); }
        return $this->redirect('/admin/grants/'.$grantId);
    }
}
