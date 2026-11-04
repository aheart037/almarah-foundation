<?php

declare(strict_types=1);

/** Destructive integration suite: run only against an explicitly named disposable test database. */
require __DIR__.'/bootstrap.php';

use App\Core\Request;
use App\Core\Router;
use App\Core\Csrf;
use App\Core\Session;
use App\Core\Database;
use App\Services\AuthService;
use App\Services\RbacService;
use App\Repositories\UserRepository;
use App\Exceptions\HttpException;
use Tests\Reports\Fixture;

$name=(string)getenv('TEST_DB_NAME');
if ($name==='' || !str_contains($name,'test')) { throw new RuntimeException('Set disposable TEST_DB_NAME containing test.'); }
putenv('DB_HOST='. (getenv('TEST_DB_HOST') ?: '127.0.0.1'));
putenv('DB_DATABASE='.$name);
putenv('DB_USERNAME='. (getenv('TEST_DB_USER') ?: 'alm'));
putenv('DB_PASSWORD='. (getenv('TEST_DB_PASS') ?: 'almpass'));
$db=Fixture::connect();
Fixture::migrate($db);
$app=require __DIR__.'/../bootstrap/app.php';
$database=$app->make(Database::class);
$logger=$app->make(\App\Core\Logger::class);
require_once __DIR__.'/../database/seeders/RoleSeeder.php';
require_once __DIR__.'/../database/seeders/SiteContentSeeder.php';
(new \Database\Seeders\RoleSeeder($database,$logger))->run();
(new \Database\Seeders\SiteContentSeeder($database,$logger))->run();
$users=$app->make(UserRepository::class);
$rbac=$app->make(RbacService::class);
$auth=$app->make(AuthService::class);
$app->make(Session::class)->start();
$router=$app->make(Router::class);
$n=0;
// The suggested roles and reserved permissions are catalogue-only until routes exist.

function ok7(bool $yes,string $message): void { global $n; if (!$yes) throw new RuntimeException('FAIL: '.$message); $n++; }
function request7(Router $router,$app,string $path,string $method='GET',array $data=[]): int {
    if ($method==='POST') { $data['_token']=$app->make(Csrf::class)->token(); }
    try { return $router->dispatch(new Request($method,$path,$method==='GET'?$data:[],$method==='POST'?$data:[],[],[]),$app)->status(); }
    catch (HttpException $e) { return $e->statusCode(); }
}
$super=$users->create(['first_name'=>'A','last_name'=>'Owner','email'=>'owner7@example.org','password_hash'=>password_hash('Secret123!',PASSWORD_DEFAULT)]);
$users->assignRole($super,'super_admin');
$staff=$users->create(['first_name'=>'B','last_name'=>'Staff','email'=>'staff7@example.org','password_hash'=>password_hash('Secret123!',PASSWORD_DEFAULT)]);
$users->assignRole($staff,'admin');
$login=static function (int $id) use ($auth,$users): void { $auth->logout(); $auth->login($users->find($id),'127.0.0.1'); };
$login($super);
ok7($database->int("SELECT COUNT(*) FROM roles WHERE name IN ('grant_manager','donation_manager','reporting_officer','viewer')")===4,'suggested roles seeded');
ok7($database->int("SELECT COUNT(*) FROM permissions WHERE name='grants.manage'")===1,'reserved grant permission catalogued');
ok7(request7($router,$app,'/admin/roles')===200,'super views roles');
ok7(request7($router,$app,'/admin/roles/create')===200,'super create page');
ok7(request7($router,$app,'/admin/roles','POST',['name'=>'http_role','label'=>'HTTP Role','permissions'=>[(string)$database->int("SELECT id FROM permissions WHERE name='dashboard.view'")]])===302,'HTTP role creation');
$httpRole=$database->int("SELECT id FROM roles WHERE name='http_role'");
ok7($httpRole>0 && request7($router,$app,'/admin/roles/'.$httpRole.'/edit')===200,'HTTP role view/edit form');
ok7(request7($router,$app,'/admin/roles/'.$httpRole,'POST',['name'=>'http_role','label'=>'HTTP Edited','permissions'=>[]])===302 && $rbac->grants($httpRole)===[],'HTTP role edit removes grant');
ok7(request7($router,$app,'/admin/roles/'.$httpRole.'/delete','POST')===302 && $rbac->role($httpRole)===null,'HTTP role delete');
$id=$rbac->saveRole(null,'qa_custom','QA Custom','Integration role',[$database->int("SELECT id FROM permissions WHERE name='dashboard.view'")],$super);
ok7($rbac->role($id)['name']==='qa_custom','create configurable role');
$view=$database->int("SELECT id FROM permissions WHERE name='users.view'");
$create=$database->int("SELECT id FROM permissions WHERE name='users.create'");
$rbac->saveRole($id,'qa_custom','QA Changed','Revised',[$view,$create,$database->int("SELECT id FROM permissions WHERE name='dashboard.view'")],$super);
ok7(count($rbac->grants($id))===3,'add permissions');
$rbac->saveRole($id,'qa_custom','QA Changed','Revised',[$view],$super);
ok7($rbac->grants($id)===[$view],'remove permissions');
$rbac->saveRole($id,'qa_custom','QA Changed','Revised',[$view,$create],$super);
$rbac->assignRoles($staff,['qa_custom'],$super);
ok7(in_array('qa_custom',$users->rolesFor($staff),true),'assign custom role');
$login($staff);
ok7(request7($router,$app,'/admin/users')===200,'custom role grants users view and staff access');
ok7(request7($router,$app,'/admin/users/create')===200,'custom role grants create');
ok7(request7($router,$app,'/admin/roles')===403,'no role catalogue without grant');
ok7(request7($router,$app,'/admin/users/'.$super.'/edit','POST',['email'=>'changed@example.org','first_name'=>'C','last_name'=>'Owner'])===403,'no edit permission to modify super');
ok7(request7($router,$app,'/admin/users/'.$super.'/roles','POST',['roles'=>[]])===403,'role assignment route denies non-super');
ok7(request7($router,$app,'/admin/roles','POST',['name'=>'attack','label'=>'Attack'])===403,'role creation route denies non-super');
$login($super);
$rbac->saveRole($id,'qa_custom','QA Changed','Revised',[$view,$create,$database->int("SELECT id FROM permissions WHERE name='dashboard.view'"),$database->int("SELECT id FROM permissions WHERE name='users.edit'")],$super);
$login($staff);
ok7(request7($router,$app,'/admin/users/'.$super.'/edit','POST',['email'=>'changed@example.org','first_name'=>'C','last_name'=>'Owner'])===403,'users.edit cannot take over super account');
ok7(request7($router,$app,'/admin/users/'.$super.'/suspend','POST',['reason'=>'test abuse'])===302 && $users->find($super)['status']==='active','users.edit cannot suspend super');
$login($super);
ok7(request7($router,$app,'/admin/users','POST',['first_name'=>'Created','last_name'=>'Member','email'=>'created7@example.org'])===302,'HTTP user create');
$created=$users->findByEmail('created7@example.org');
ok7($created!==null,'created account persists');
ok7($database->int('SELECT COUNT(*) FROM password_reset_tokens WHERE user_id=:id',['id'=>$created['id']])>0,'created user receives a time-limited setup token');
ok7(request7($router,$app,'/admin/users/'.$created['id'].'/edit','POST',['first_name'=>'Edited','last_name'=>'Member','email'=>'created7@example.org','phone'=>'123'])===302 && $users->find((int)$created['id'])['first_name']==='Edited','HTTP user edit');
ok7(request7($router,$app,'/admin/users/'.$created['id'].'/roles','POST',['roles'=>['qa_custom']])===302 && in_array('qa_custom',$users->rolesFor((int)$created['id']),true),'HTTP role assignment');
ok7(request7($router,$app,'/admin/users/'.$created['id'].'/force-password-reset','POST')===302,'admin can initiate password reset');
$rbac->changeStatus((int)$created['id'],'suspended',$super,'Testing deactivation');
$login((int)$created['id']);
ok7(!$auth->check(),'disabled user session blocked');
ok7(request7($router,$app,'/admin/users')===302,'disabled user denied route');
$login($super);
$pending=$users->create(['first_name'=>'Pending','last_name'=>'Member','email'=>'pending7@example.org','password_hash'=>password_hash('Secret123!',PASSWORD_DEFAULT),'status'=>'pending']);
ok7(!$auth->attempt('pending7@example.org','Secret123!','127.0.0.1')['ok'],'pending user cannot sign in');
$users->markEmailVerified($pending);
ok7($users->find($pending)['status']==='active','email verification still activates pending account');
$users->markEmailVerified((int)$created['id']);
ok7($users->find((int)$created['id'])['status']==='suspended','email verification cannot reactivate suspension');
$rbac->changeStatus((int)$created['id'],'active',$super);
ok7($users->find((int)$created['id'])['status']==='active','user reactivated');
try { $rbac->assignRoles($super,[],$super); ok7(false,'self-demotion must fail'); } catch (DomainException $e) { ok7(true,'self-demotion rejected'); }
try { $rbac->changeStatus($super,'suspended',$staff,'test'); ok7(false,'last super suspend must fail'); } catch (DomainException $e) { ok7(true,'last super suspend rejected'); }
try { $rbac->saveRole($id,'qa_custom','QA Changed','Revised',[999999],$super); ok7(false,'unknown permission must fail'); } catch (DomainException $e) { ok7(count($rbac->grants($id))===4,'invalid grant update rolled back'); }
try { $rbac->deleteRole($id,$super); ok7(false,'assigned role delete must fail'); } catch (DomainException $e) { ok7(true,'assigned role delete rejected'); }
try { $rbac->saveRole($database->int("SELECT id FROM roles WHERE name='super_admin'"),'super_admin','Changed','',[],$super); ok7(false,'super edit must fail'); } catch (DomainException $e) { ok7(true,'super role protected'); }
$rbac->assignRoles($staff,[],$super);
$rbac->assignRoles((int)$created['id'],[],$super);
$rbac->deleteRole($id,$super);
ok7($rbac->role($id)===null,'unused role safely deleted');
(new \Database\Seeders\RoleSeeder($database,$logger))->run();
ok7($rbac->role($id)===null && $database->int("SELECT COUNT(*) FROM roles WHERE name='http_role'")===0,'reseed cannot resurrect deleted roles');
$adminId=$database->int("SELECT id FROM roles WHERE name='admin'");
$permId=$database->int("SELECT id FROM permissions WHERE name='dashboard.view'");
$database->run('DELETE FROM role_permissions WHERE role_id=:r AND permission_id=:p',['r'=>$adminId,'p'=>$permId]);
(new \Database\Seeders\SiteContentSeeder($database,$logger))->run();
ok7($database->int('SELECT COUNT(*) FROM role_permissions WHERE role_id=:r AND permission_id=:p',['r'=>$adminId,'p'=>$permId])===0,'reseed cannot restore revoked grant');
ok7($database->int("SELECT COUNT(*) FROM audit_logs WHERE action IN ('role.saved','role.deleted','user.roles_updated','user.suspended','user.created','user.updated')")>=7,'important actions audited');
echo "$n V3 Part 7 RBAC integration checks passed.\n";
