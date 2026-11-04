<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use DomainException;

/** Minimal grant register and durable, unique user assignments. */
final class GrantService
{
    public function __construct(private Database $db, private AuditService $audit) {}

    public function all(): array
    {
        return $this->db->select("SELECT g.*, (SELECT COUNT(*) FROM grant_user_assignments a WHERE a.grant_id=g.id AND a.status<>'removed') AS assigned_count FROM grants g ORDER BY g.id DESC");
    }

    public function find(int $id): ?array
    {
        return $this->db->selectOne('SELECT * FROM grants WHERE id=:id',['id'=>$id]);
    }

    public function forGrant(int $id): array
    {
        return $this->db->select("SELECT a.*,u.first_name,u.last_name,u.email FROM grant_user_assignments a JOIN users u ON u.id=a.user_id WHERE a.grant_id=:id ORDER BY (a.status='removed'),a.id DESC",['id'=>$id]);
    }

    public function forUser(int $id): array
    {
        return $this->db->select("SELECT a.*,g.reference,g.title,g.status AS grant_status FROM grant_user_assignments a JOIN grants g ON g.id=a.grant_id WHERE a.user_id=:id ORDER BY (a.status='removed'),a.id DESC",['id'=>$id]);
    }

    public function eventsForGrant(int $id): array
    {
        return $this->db->select('SELECT ev.*,a.user_id FROM grant_assignment_events ev JOIN grant_user_assignments a ON a.id=ev.assignment_id WHERE a.grant_id=:id ORDER BY ev.id DESC',['id'=>$id]);
    }

    public function activeUsers(): array
    {
        return $this->db->select("SELECT id,first_name,last_name,email FROM users WHERE status='active' AND deleted_at IS NULL ORDER BY first_name,last_name,id");
    }

    public function save(?int $id, string $reference, string $title, string $description, string $status, int $actor): int
    {
        $reference=trim($reference);
        $title=trim($title);
        $description=trim($description);
        if (!preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]{1,49}$/D',$reference) || $title==='' || mb_strlen($title)>200 || mb_strlen($description)>10000 || !in_array($status,['draft','active','closed'],true)) {
            throw new DomainException('Enter a valid grant reference (2–50 letters/numbers, dots, dashes or underscores), title, description and status.');
        }
        return $this->db->transaction(function () use ($id,$reference,$title,$description,$status,$actor): int {
            if ($id!==null) { $this->requirePermission($actor,['grants.view']); }
            $this->requirePermission($actor,$id===null ? ['grants.create','grants.manage'] : ['grants.edit','grants.manage']);
            if ($id !== null && $this->db->selectOne('SELECT id FROM grants WHERE id=:id FOR UPDATE',['id'=>$id])===null) {
                throw new DomainException('Grant not found.');
            }
            $data=['reference'=>$reference,'title'=>$title,'description'=>$description ?: null,'status'=>$status];
            if ($id===null) { $id=$this->db->insert('grants',$data+['created_by'=>$actor]); }
            else { $this->db->update('grants',$data,'id=:id',['id'=>$id]); }
            $this->audit->logRequired('grant.saved','grant',$id,['reference'=>$reference,'status'=>$status],$actor);
            return $id;
        });
    }

    /** A single transaction for a single user, or many distinct users. */
    public function assignUsers(int $grantId, array $userIds, string $responsibilities, string $notes, int $actor): void
    {
        $ids=$this->validIds($userIds);
        $this->db->transaction(function () use ($grantId,$ids,$responsibilities,$notes,$actor): void {
            $this->requirePermission($actor,['grants.view']);
            $this->requirePermission($actor,['grant_assignments.create','grant_assignments.manage']);
            $grant=$this->db->selectOne('SELECT id,status FROM grants WHERE id=:id FOR UPDATE',['id'=>$grantId]);
            if ($grant===null || $grant['status']==='closed') { throw new DomainException('Grant not found or closed to new assignments.'); }
            foreach ($ids as $uid) { $this->assignOne($grantId,$uid,$responsibilities,$notes,$actor); }
        });
    }

    /** A single transaction for one user assigned to several distinct grants. */
    public function assignGrants(int $userId, array $grantIds, string $responsibilities, string $notes, int $actor): void
    {
        $ids=$this->validIds($grantIds);
        sort($ids,SORT_NUMERIC);
        $this->db->transaction(function () use ($userId,$ids,$responsibilities,$notes,$actor): void {
            $this->requirePermission($actor,['grants.view']);
            $this->requirePermission($actor,['grant_assignments.create','grant_assignments.manage']);
            $user=$this->db->selectOne('SELECT id FROM users WHERE id=:id AND status=\'active\' AND deleted_at IS NULL',['id'=>$userId]);
            if ($user===null) { throw new DomainException('Only active users can be assigned.'); }
            foreach ($ids as $grantId) {
                $grant=$this->db->selectOne('SELECT id,status FROM grants WHERE id=:id FOR UPDATE',['id'=>$grantId]);
                if ($grant===null || $grant['status']==='closed') { throw new DomainException('Grant not found or closed to new assignments.'); }
                $this->assignOne($grantId,$userId,$responsibilities,$notes,$actor);
            }
        });
    }

    public function updateAssignment(int $grantId, int $userId, string $status, string $responsibilities, string $notes, int $actor): void
    {
        if (!in_array($status,['active','on_hold','completed'],true)) { throw new DomainException('Invalid assignment status.'); }
        $this->validateText($responsibilities,$notes);
        $this->db->transaction(function () use ($grantId,$userId,$status,$responsibilities,$notes,$actor): void {
            $this->requirePermission($actor,['grants.view']);
            $this->requirePermission($actor,['grant_assignments.edit','grant_assignments.manage']);
            $assignment=$this->db->selectOne('SELECT id,status,responsibilities,notes FROM grant_user_assignments WHERE grant_id=:g AND user_id=:u FOR UPDATE',['g'=>$grantId,'u'=>$userId]);
            if ($assignment===null || $assignment['status']==='removed') { throw new DomainException('Assignment not found.'); }
            $this->db->update('grant_user_assignments',['status'=>$status,'responsibilities'=>$responsibilities ?: null,'notes'=>$notes ?: null],'id=:id',['id'=>$assignment['id']]);
            $this->event((int)$assignment['id'],$actor,'updated',$assignment['status'],$status,$assignment['responsibilities'],$responsibilities ?: null,$assignment['notes'],$notes ?: null);
            $this->audit->logRequired('grant.assignment_updated','grant_assignment',$assignment['id'],['grant_id'=>$grantId,'user_id'=>$userId,'from'=>$assignment['status'],'to'=>$status],$actor);
        });
    }

    /** Atomically end an assignment and assign its replacement without losing history. */
    public function changeAssignee(int $grantId, int $oldUserId, int $newUserId, string $responsibilities, string $notes, int $actor): void
    {
        if ($oldUserId===$newUserId) { throw new DomainException('Choose a different user.'); }
        $this->db->transaction(function () use ($grantId,$oldUserId,$newUserId,$responsibilities,$notes,$actor): void {
            $this->requirePermission($actor,['grants.view']);
            $this->requirePermission($actor,['grant_assignments.manage']);
            $grant=$this->db->selectOne('SELECT id,status FROM grants WHERE id=:id FOR UPDATE',['id'=>$grantId]);
            if ($grant===null || $grant['status']==='closed') { throw new DomainException('Grant not found or closed to new assignments.'); }
            $old=$this->db->selectOne('SELECT id,status,responsibilities,notes FROM grant_user_assignments WHERE grant_id=:g AND user_id=:u FOR UPDATE',['g'=>$grantId,'u'=>$oldUserId]);
            if ($old===null || $old['status']==='removed') { throw new DomainException('Current assignee not found.'); }
            // This fails if the proposed user is already assigned; the old row remains unchanged.
            $this->assignOne($grantId,$newUserId,$responsibilities,$notes,$actor);
            $this->db->update('grant_user_assignments',['status'=>'removed'],'id=:id',['id'=>$old['id']]);
            $this->event((int)$old['id'],$actor,'transferred',$old['status'],'removed',$old['responsibilities'],null,$old['notes'],null);
            $this->audit->logRequired('grant.assignee_changed','grant_assignment',$old['id'],['grant_id'=>$grantId,'from_user_id'=>$oldUserId,'to_user_id'=>$newUserId],$actor);
        });
    }

    public function remove(int $grantId, int $userId, int $actor): void
    {
        $this->db->transaction(function () use ($grantId,$userId,$actor): void {
            $this->requirePermission($actor,['grants.view']);
            $this->requirePermission($actor,['grant_assignments.delete','grant_assignments.manage']);
            $assignment=$this->db->selectOne('SELECT id,status,responsibilities,notes FROM grant_user_assignments WHERE grant_id=:g AND user_id=:u FOR UPDATE',['g'=>$grantId,'u'=>$userId]);
            if ($assignment===null || $assignment['status']==='removed') { throw new DomainException('Active assignment not found.'); }
            $this->db->update('grant_user_assignments',['status'=>'removed'],'id=:id',['id'=>$assignment['id']]);
            $this->event((int)$assignment['id'],$actor,'removed',$assignment['status'],'removed',$assignment['responsibilities'],null,$assignment['notes'],null);
            $this->audit->logRequired('grant.assignment_removed','grant_assignment',$assignment['id'],['grant_id'=>$grantId,'user_id'=>$userId,'from'=>$assignment['status']],$actor);
        });
    }

    private function assignOne(int $grantId, int $userId, string $responsibilities, string $notes, int $actor): void
    {
        $this->validateText($responsibilities,$notes);
        if ($this->db->selectOne("SELECT id FROM users WHERE id=:id AND status='active' AND deleted_at IS NULL",['id'=>$userId])===null) {
            throw new DomainException('Only active users can be assigned.');
        }
        $old=$this->db->selectOne('SELECT id,status,responsibilities,notes FROM grant_user_assignments WHERE grant_id=:g AND user_id=:u FOR UPDATE',['g'=>$grantId,'u'=>$userId]);
        if ($old!==null && $old['status']!=='removed') { throw new DomainException('This user is already assigned to that grant.'); }
        if ($old!==null) {
            $this->db->run("UPDATE grant_user_assignments SET status='active',assigned_at=UTC_TIMESTAMP(),assigned_by=:by,responsibilities=:r,notes=:n WHERE id=:id",['by'=>$actor,'r'=>$responsibilities ?: null,'n'=>$notes ?: null,'id'=>$old['id']]);
            $id=$old['id'];
        } else {
            $id=$this->db->insert('grant_user_assignments',['grant_id'=>$grantId,'user_id'=>$userId,'assigned_by'=>$actor,'status'=>'active','responsibilities'=>$responsibilities ?: null,'notes'=>$notes ?: null]);
        }
        $this->event((int)$id,$actor,$old!==null ? 'reassigned' : 'assigned',$old['status'] ?? null,'active',$old['responsibilities'] ?? null,$responsibilities ?: null,$old['notes'] ?? null,$notes ?: null);
        $this->audit->logRequired('grant.assignment_created','grant_assignment',$id,['grant_id'=>$grantId,'user_id'=>$userId,'reassigned'=>$old!==null],$actor);
    }

    private function event(int $id, int $actor, string $action, ?string $from, ?string $to, ?string $responsibilitiesBefore, ?string $responsibilitiesAfter, ?string $notesBefore, ?string $notesAfter): void
    {
        $this->db->insert('grant_assignment_events',[
            'assignment_id'=>$id,'actor_user_id'=>$actor,'action'=>$action,'from_status'=>$from,'to_status'=>$to,
            'responsibilities_before'=>$responsibilitiesBefore,'responsibilities_after'=>$responsibilitiesAfter,
            'notes_before'=>$notesBefore,'notes_after'=>$notesAfter,
        ]);
    }

    private function validIds(array $values): array
    {
        if ($values===[] || count($values)>100 || count($values)!==count(array_unique(array_map('strval',$values)))) {
            throw new DomainException('Select 1–100 distinct users or grants.');
        }
        $ids=[];
        foreach ($values as $value) {
            if ((!is_string($value) && !is_int($value)) || !preg_match('/^[1-9][0-9]*$/D',(string)$value) || (int)$value>2147483647) {
                throw new DomainException('Invalid user or grant ID.');
            }
            $ids[]=(int)$value;
        }
        return $ids;
    }

    private function validateText(string $responsibilities, string $notes): void
    {
        if (mb_strlen($responsibilities)>1000 || mb_strlen($notes)>10000) { throw new DomainException('Responsibilities or notes are too long.'); }
    }

    private function requirePermission(int $actor, array $permissions): void
    {
        $user=$this->db->selectOne("SELECT id FROM users WHERE id=:id AND status='active' AND deleted_at IS NULL",['id'=>$actor]);
        if ($user===null) { throw new DomainException('Only active authorized users may manage grants.'); }
        if ($this->db->int("SELECT COUNT(*) FROM user_roles ur JOIN roles r ON r.id=ur.role_id WHERE ur.user_id=:id AND r.name='super_admin'",['id'=>$actor])>0) { return; }
        $params=['id'=>$actor]; $holders=[];
        foreach ($permissions as $i=>$permission) { $params['p'.$i]=$permission; $holders[]=':p'.$i; }
        if ($this->db->int('SELECT COUNT(*) FROM user_roles ur JOIN role_permissions rp ON rp.role_id=ur.role_id JOIN permissions p ON p.id=rp.permission_id WHERE ur.user_id=:id AND p.name IN ('.implode(',',$holders).')',$params)===0) {
            throw new DomainException('Your role does not permit this grant operation.');
        }
    }
}
