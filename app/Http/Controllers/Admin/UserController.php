<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Core\View;
use App\Http\Controllers\Controller;
use App\Repositories\DonorRepository;
use App\Repositories\UserRepository;
use App\Services\AuditService;
use App\Services\AuthService;
use App\Services\RbacService;
use App\Services\GrantService;
use DomainException;
use App\Mail\Mailer;

/**
 * User administration. Role changes are super-admin only and guarded against
 * removing the last super administrator.
 */
final class UserController extends Controller
{
    public function __construct(
        View $view,
        Session $session,
        Csrf $csrf,
        AuthService $auth,
        private UserRepository $users,
        private DonorRepository $donors,
        private AuditService $audit,
        private Mailer $mailer,
        private Database $db,
        private RbacService $rbac
    ) {
        parent::__construct($view, $session, $csrf, $auth);
    }

    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('q', ''));
        $status = (string) $request->input('status', '');
        $role = (string) $request->input('role', '');

        $page = max(1, (int) $request->input('page', 1));
        $perPage = 25;

        $result = $this->users->paginate($search, $status, $page, $perPage);

        $rows = $result['rows'];

        if ($role !== '') {
            $rows = array_values(array_filter($rows, static function (array $row) use ($role): bool {
                $roles = array_filter(array_map('trim', explode(',', (string) ($row['roles'] ?? ''))));
                return in_array($role, $roles, true);
            }));
        }

        return $this->render('admin/users/index', [
            'pageTitle' => 'Users',
            'users'     => $rows,
            'total'     => $result['total'],
            'page'      => $page,
            'lastPage'  => max(1, (int) ceil($result['total'] / $perPage)),
            'filters'   => ['q' => $search, 'status' => $status, 'role' => $role],
            'counts'    => [
                'active'    => $this->users->countByStatus('active'),
                'suspended' => $this->users->countByStatus('suspended'),
                'all'       => $this->users->countAll(),
            ],
        ], 'layouts/admin');
    }

    public function show(Request $request): Response
    {
        $user = $this->requireUser((int) $request->routeParam('id', 0));
        $userId = (int) $user['id'];

        $donations = $this->db->select(
            "SELECT d.public_reference, d.amount_minor, d.currency, d.status, d.created_at
             FROM donations d JOIN donors dn ON dn.id = d.donor_id
             WHERE dn.user_id = :uid ORDER BY d.id DESC LIMIT 15",
            ['uid' => $userId]
        );

        return $this->render('admin/users/show', [
            'pageTitle'      => (string) $user['email'],
            'user'           => $user,
            'roles'          => $this->users->rolesFor($userId),
            'availableRoles' => array_column($this->rbac->roles(), 'name'),
            'fundraisers'    => app(\App\Repositories\FundraiserRepository::class)->forOwner($userId),
            'donations'      => $donations,
            'auditTrail'     => $this->audit->forUser($userId, 25),
            'isSelf'         => $userId === (int) $this->auth->id(),
            'assignedGrants' => $this->auth->can('grants.view') && $this->auth->canAny(['grant_assignments.view','grant_assignments.manage']) ? app(GrantService::class)->forUser($userId) : null,
            'assignableGrants' => $this->auth->can('grants.view') && $this->auth->canAny(['grant_assignments.create','grant_assignments.manage']) ? app(GrantService::class)->all() : [],
        ], 'layouts/admin');
    }

    public function suspend(Request $request): Response
    {
        $user = $this->requireUser((int) $request->routeParam('id', 0));
        $userId = (int) $user['id'];

        if ($userId === (int) $this->auth->id()) {
            $this->flashError('You cannot suspend your own account.');
            return $this->redirect('/admin/users/' . $userId);
        }

        $validator = Validator::make($request->all(), ['reason' => 'required|string|min:5|max:500']);

        if ($validator->fails()) {
            return $this->flashFormState($validator->errors(), $request->all());
        }

        $reason = (string) $request->input('reason');

        try { $this->rbac->changeStatus($userId, 'suspended', (int)$this->auth->id(), $reason); }
        catch (DomainException $e) { $this->flashError($e->getMessage()); return $this->redirect('/admin/users/' . $userId); }

        $this->mailer->accountSuspended((string) $user['email'], (string) $user['first_name'], $reason);

        $this->flashSuccess('Account suspended and the user has been notified.');
        return $this->redirect('/admin/users/' . $userId);
    }

    public function reactivate(Request $request): Response
    {
        $user = $this->requireUser((int) $request->routeParam('id', 0));
        $userId = (int) $user['id'];

        try { $this->rbac->changeStatus($userId, 'active', (int)$this->auth->id()); }
        catch (DomainException $e) { $this->flashError($e->getMessage()); return $this->redirect('/admin/users/' . $userId); }

        $this->mailer->accountReactivated((string) $user['email'], (string) $user['first_name']);

        $this->flashSuccess('Account reactivated.');
        return $this->redirect('/admin/users/' . $userId);
    }

    public function forceVerify(Request $request): Response
    {
        $user = $this->requireUser((int) $request->routeParam('id', 0));

        $this->protectSuper($user);
        $this->users->markEmailVerified((int) $user['id']);
        $this->audit->log('user.email_verified_by_admin', 'user', (string) $user['id'], [], (int) $this->auth->id());

        $this->flashSuccess('Email address marked as verified.');
        return $this->redirect('/admin/users/' . $user['id']);
    }

    public function forcePasswordReset(Request $request): Response
    {
        $user = $this->requireUser((int) $request->routeParam('id', 0));

        $this->protectSuper($user);
        // Send the standard reset email rather than setting a password we
        // would then know.
        $this->auth->sendPasswordReset((string) $user['email']);
        $this->audit->log('user.password_reset_forced', 'user', (string) $user['id'], [], (int) $this->auth->id());

        $this->flashSuccess('Password reset requested for ' . (string) $user['email'] . '.');
        return $this->redirect('/admin/users/' . $user['id']);
    }

    public function updateRoles(Request $request): Response
    {
        $user = $this->requireUser((int) $request->routeParam('id', 0));
        $userId = (int) $user['id'];

        $submitted = $request->input('roles', []);
        if (!is_array($submitted) || array_filter($submitted, static fn ($r): bool => !is_string($r))) {
            $this->flashError('Invalid role selection.');
            return $this->redirect('/admin/users/' . $userId);
        }
        try { $this->rbac->assignRoles($userId, $submitted, (int)$this->auth->id()); }
        catch (DomainException $e) { $this->flashError($e->getMessage()); return $this->redirect('/admin/users/' . $userId); }

        $this->flashSuccess('Roles updated. The change applies the next time the user loads a page.');
        return $this->redirect('/admin/users/' . $userId);
    }

    public function createForm(Request $request): Response
    {
        return $this->render('admin/users/create', ['pageTitle'=>'Create user'], 'layouts/admin');
    }

    public function create(Request $request): Response
    {
        $first = trim((string)$request->input('first_name',''));
        $last = trim((string)$request->input('last_name',''));
        $email = mb_strtolower(trim((string)$request->input('email','')));
        if ($first === '' || mb_strlen($first)>100 || $last === '' || mb_strlen($last)>100 || !filter_var($email,FILTER_VALIDATE_EMAIL) || mb_strlen($email)>191) {
            $this->flashError('Enter valid names and an email address.');
            return $this->redirect('/admin/users/create');
        }
        if ($this->users->findByEmail($email) !== null) {
            $this->flashError('This email address is already in use.');
            return $this->redirect('/admin/users/create');
        }
        try {
            $id = $this->db->transaction(function () use ($first,$last,$email): int {
                $id = $this->users->create(['first_name'=>$first,'last_name'=>$last,'email'=>$email,
                    'password_hash'=>password_hash(bin2hex(random_bytes(32)),PASSWORD_DEFAULT), 'status'=>'active']);
                $this->audit->logRequired('user.created','user',$id,['email_hash'=>hash('sha256',$email)],(int)$this->auth->id());
                return $id;
            });
        } catch (\PDOException $e) {
            $this->flashError('User could not be created; check that this email is available.');
            return $this->redirect('/admin/users/create');
        }
        $sent = $this->auth->sendPasswordReset($email);
        $this->flashSuccess($sent ? 'User created. Password setup requested; check the email queue before sharing access. Assign roles on the user page.' : 'User created, but the password setup email could not be sent. Retry from the user page.');
        return $this->redirect('/admin/users/'.$id);
    }

    public function edit(Request $request): Response
    {
        $user = $this->requireUser((int)$request->routeParam('id',0));
        $this->protectSuper($user);
        $first = trim((string)$request->input('first_name',''));
        $last = trim((string)$request->input('last_name',''));
        $phone = trim((string)$request->input('phone',''));
        $email = mb_strtolower(trim((string)$request->input('email','')));
        if ($first==='' || mb_strlen($first)>100 || $last==='' || mb_strlen($last)>100 || mb_strlen($phone)>40 || !filter_var($email,FILTER_VALIDATE_EMAIL) || mb_strlen($email)>191) {
            $this->flashError('Enter valid names, phone and email.');
            return $this->redirect('/admin/users/'.$user['id']);
        }
        try {
            $this->db->transaction(function () use ($user,$first,$last,$phone,$email): void {
                $this->db->selectOne("SELECT id FROM roles WHERE name='super_admin' FOR UPDATE");
                if ($this->rbac->isSuper((int)$user['id']) && !$this->auth->isSuperAdmin()) { abort(403, 'Only Super Admin can edit a Super Admin.'); }
                $this->users->update((int)$user['id'],['first_name'=>$first,'last_name'=>$last,'phone'=>$phone ?: null,'email'=>$email]);
                $this->audit->logRequired('user.updated','user',$user['id'],['previous_email_hash'=>hash('sha256',(string)$user['email']),'email_hash'=>hash('sha256',$email)],(int)$this->auth->id());
            });
            $this->flashSuccess('User details saved.');
        } catch (\PDOException $e) { $this->flashError('User could not be saved; the email may already be in use.'); }
        return $this->redirect('/admin/users/'.$user['id']);
    }

    private function protectSuper(array $user): void
    {
        if ($this->rbac->isSuper((int)$user['id']) && !$this->auth->isSuperAdmin()) {
            abort(403, 'Only Super Admin can change a Super Admin account.');
        }
    }

    public function donors(Request $request): Response
    {
        $search = trim((string) $request->input('q', ''));
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 25;

        $result = $this->donors->paginate($search, $page, $perPage);

        return $this->render('admin/donors', [
            'pageTitle' => 'Donors',
            'donors'    => $result['rows'],
            'total'     => $result['total'],
            'page'      => $page,
            'lastPage'  => max(1, (int) ceil($result['total'] / $perPage)),
            'filters'   => ['q' => $search],
        ], 'layouts/admin');
    }

    /** @return array<string,mixed> */
    private function requireUser(int $id): array
    {
        $user = $this->users->find($id);

        if ($user === null) {
            abort(404, 'User not found.');
        }

        return $user;
    }
}
