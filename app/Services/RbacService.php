<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use DomainException;

/** Transactional administration of role grants and account access. */
final class RbacService
{
    public const SYSTEM_ROLES = ['super_admin','admin','finance_manager','content_manager','volunteer_manager','initial_reviewer','initial_review','verification_officer','donor','fundraiser'];
    public function __construct(private Database $db, private AuditService $audit) {}

    public function roles(): array
    {
        return $this->db->select('SELECT r.*, (SELECT COUNT(*) FROM user_roles ur WHERE ur.role_id=r.id) AS members FROM roles r ORDER BY r.label');
    }

    public function permissions(): array
    {
        return $this->db->select('SELECT * FROM permissions ORDER BY group_name,name');
    }

    public function role(int $id): ?array
    {
        return $this->db->selectOne('SELECT * FROM roles WHERE id=:id', ['id'=>$id]);
    }

    public function grants(int $id): array
    {
        return array_map(static fn (array $r): int => (int)$r['permission_id'],
            $this->db->select('SELECT permission_id FROM role_permissions WHERE role_id=:id', ['id'=>$id]));
    }

    /** Only a current super administrator may change roles. */
    public function saveRole(?int $id, string $name, string $label, string $description, array $grants, int $actor): int
    {
        if (!preg_match('/^[a-z][a-z0-9_]{2,49}$/D', $name) || mb_strlen($label) < 2 || mb_strlen($label) > 100 || mb_strlen($description) > 255) {
            throw new DomainException('Provide a valid role key, label and description.');
        }
        $ids = array_values(array_unique(array_map('intval', $grants)));
        if (count($ids) !== count($grants) || in_array(0, $ids, true)) {
            throw new DomainException('Invalid permission selection.');
        }
        return $this->db->transaction(function () use ($id,$name,$label,$description,$ids,$actor): int {
            $this->requireSuper($actor);
            if ($id !== null) {
                $role = $this->db->selectOne('SELECT * FROM roles WHERE id=:id FOR UPDATE', ['id'=>$id]);
                if ($role === null) { throw new DomainException('Role not found.'); }
                if ($role['name'] === 'super_admin') { throw new DomainException('The Super Admin role is protected.'); }
                if (in_array($role['name'],self::SYSTEM_ROLES,true) && $role['name'] !== $name) { throw new DomainException('Built-in role keys cannot be renamed.'); }
                $old = $this->grants($id);
                $this->db->update('roles', ['name'=>$name,'label'=>$label,'description'=>$description], 'id=:id', ['id'=>$id]);
            } else {
                if (in_array($name,self::SYSTEM_ROLES,true)) { throw new DomainException('Built-in role keys are reserved.'); }
                $id = $this->db->insert('roles', ['name'=>$name,'label'=>$label,'description'=>$description]);
                $old = [];
            }
            if ($ids !== []) {
                $params = [];
                $holders = [];
                foreach ($ids as $i=>$permissionId) { $params['p'.$i]=$permissionId; $holders[]=':p'.$i; }
                if ($this->db->int('SELECT COUNT(*) FROM permissions WHERE id IN ('.implode(',',$holders).')', $params) !== count($ids)) {
                    throw new DomainException('Unknown permission selection.');
                }
            }
            $this->db->run('DELETE FROM role_permissions WHERE role_id=:id', ['id'=>$id]);
            foreach ($ids as $permissionId) {
                $this->db->insert('role_permissions', ['role_id'=>$id,'permission_id'=>$permissionId]);
            }
            $this->audit->logRequired('role.saved', 'role', $id, ['name'=>$name,'previous_permission_ids'=>$old,'permission_ids'=>$ids], $actor);
            return $id;
        });
    }

    public function deleteRole(int $id, int $actor): void
    {
        $this->db->transaction(function () use ($id,$actor): void {
            $this->requireSuper($actor);
            $role = $this->db->selectOne('SELECT * FROM roles WHERE id=:id FOR UPDATE', ['id'=>$id]);
            if ($role === null) { throw new DomainException('Role not found.'); }
            if (in_array($role['name'],self::SYSTEM_ROLES,true)) { throw new DomainException('Built-in roles cannot be deleted.'); }
            if ($this->db->int('SELECT COUNT(*) FROM user_roles WHERE role_id=:id', ['id'=>$id]) > 0) {
                throw new DomainException('Unassign this role from every user before deleting it.');
            }
            $this->db->run('DELETE FROM roles WHERE id=:id', ['id'=>$id]);
            $this->audit->logRequired('role.deleted', 'role', $id, ['name'=>$role['name']], $actor);
        });
    }

    public function assignRoles(int $userId, array $requested, int $actor): void
    {
        $requested = array_values(array_unique(array_map('strval', $requested)));
        $this->db->transaction(function () use ($userId,$requested,$actor): void {
            $this->requireSuper($actor);
            // A single shared row serializes all Super Admin membership/status changes.
            $super = $this->db->selectOne("SELECT id FROM roles WHERE name='super_admin' FOR UPDATE");
            if ($super === null) { throw new DomainException('Super Admin role is missing.'); }
            $target = $this->db->selectOne('SELECT id,status FROM users WHERE id=:id AND deleted_at IS NULL FOR UPDATE', ['id'=>$userId]);
            if ($target === null) { throw new DomainException('User not found.'); }
            $roles = $this->db->select('SELECT id,name FROM roles');
            $ids = array_column($roles, 'id', 'name');
            foreach ($requested as $name) {
                if (!isset($ids[$name])) { throw new DomainException('Unknown role selection.'); }
            }
            $current = array_map(static fn ($r): string => $r['name'], $this->db->select('SELECT r.name FROM user_roles ur JOIN roles r ON r.id=ur.role_id WHERE ur.user_id=:id', ['id'=>$userId]));
            if ($userId === $actor && in_array('super_admin',$current,true) && !in_array('super_admin',$requested,true)) {
                throw new DomainException('You cannot remove your own Super Admin access.');
            }
            if (in_array('super_admin',$current,true) && !in_array('super_admin',$requested,true) && $target['status']==='active' && $this->activeSupers() <= 1) {
                throw new DomainException('Promote another active Super Admin before removing the last one.');
            }
            $this->db->run('DELETE FROM user_roles WHERE user_id=:id', ['id'=>$userId]);
            foreach ($requested as $name) { $this->db->insert('user_roles', ['user_id'=>$userId,'role_id'=>(int)$ids[$name]]); }
            $this->audit->logRequired('user.roles_updated','user',$userId,['from'=>$current,'to'=>$requested],$actor);
        });
    }

    public function changeStatus(int $userId, string $status, int $actor, string $reason = ''): void
    {
        if (!in_array($status,['active','suspended'],true)) { throw new DomainException('Invalid status.'); }
        $this->db->transaction(function () use ($userId,$status,$actor,$reason): void {
            $this->db->selectOne("SELECT id FROM roles WHERE name='super_admin' FOR UPDATE");
            $user = $this->db->selectOne('SELECT id,status FROM users WHERE id=:id AND deleted_at IS NULL FOR UPDATE', ['id'=>$userId]);
            if ($user === null) { throw new DomainException('User not found.'); }
            if ($userId === $actor && $status === 'suspended') { throw new DomainException('You cannot suspend your own account.'); }
            if ($this->isSuper($userId)) {
                $this->requireSuper($actor);
                if ($status === 'suspended' && $user['status']==='active' && $this->activeSupers() <= 1) {
                    throw new DomainException('Promote another active Super Admin before suspending the last one.');
                }
            }
            $this->db->update('users',['status'=>$status],'id=:id',['id'=>$userId]);
            $this->audit->logRequired($status === 'active' ? 'user.reactivated' : 'user.suspended','user',$userId,['from'=>$user['status'],'reason'=>$reason],$actor);
        });
    }

    public function isSuper(int $userId): bool
    {
        return $this->db->int("SELECT COUNT(*) FROM user_roles ur JOIN roles r ON r.id=ur.role_id WHERE ur.user_id=:id AND r.name='super_admin'",['id'=>$userId])>0;
    }

    private function activeSupers(): int
    {
        return $this->db->int("SELECT COUNT(DISTINCT u.id) FROM users u JOIN user_roles ur ON ur.user_id=u.id JOIN roles r ON r.id=ur.role_id WHERE r.name='super_admin' AND u.status='active' AND u.deleted_at IS NULL");
    }

    private function requireSuper(int $actor): void
    {
        if (!$this->isSuper($actor)) { throw new DomainException('Only Super Admin may perform this action.'); }
    }
}
