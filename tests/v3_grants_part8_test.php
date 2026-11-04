<?php

declare(strict_types=1);

/** Destructive integration test: use only a disposable *_test database. */
require __DIR__.'/bootstrap.php';

use App\Core\Request;
use App\Core\Router;
use App\Core\Csrf;
use App\Core\Session;
use App\Core\Database;
use App\Services\AuthService;
use App\Services\GrantService;
use App\Repositories\UserRepository;
use App\Exceptions\HttpException;
use Tests\Reports\Fixture;

$name=(string)getenv('TEST_DB_NAME');
if ($name==='' || !str_contains($name,'test')) { throw new RuntimeException('Set a disposable TEST_DB_NAME containing test.'); }
putenv('DB_HOST='.(getenv('TEST_DB_HOST') ?: '127.0.0.1'));
putenv('DB_DATABASE='.$name);
putenv('DB_USERNAME='.(getenv('TEST_DB_USER') ?: 'alm'));
putenv('DB_PASSWORD='.(getenv('TEST_DB_PASS') ?: 'almpass'));
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
$grants=$app->make(GrantService::class);
$auth=$app->make(AuthService::class);
$app->make(Session::class)->start();
$router=$app->make(Router::class);
$checks=0;
function verify8(bool $condition,string $label): void { global $checks; if (!$condition) throw new RuntimeException('FAIL '.$label); $checks++; }
function call8(Router $router,$app,string $path,string $method='GET',array $data=[]): array {
    if ($method==='POST') { $data['_token']=$app->make(Csrf::class)->token(); }
    try { $response=$router->dispatch(new Request($method,$path,$method==='GET'?$data:[],$method==='POST'?$data:[],[],[]),$app); return [$response->status(),$response->body()]; }
    catch (HttpException $e) { return [$e->statusCode(),'']; }
}
$owner=$users->create(['first_name'=>'Super','last_name'=>'Owner','email'=>'owner8@example.org','password_hash'=>password_hash('Password123!',PASSWORD_DEFAULT)]);
$manager=$users->create(['first_name'=>'Grant','last_name'=>'Manager','email'=>'manager8@example.org','password_hash'=>password_hash('Password123!',PASSWORD_DEFAULT)]);
$viewer=$users->create(['first_name'=>'Just','last_name'=>'Viewer','email'=>'viewer8@example.org','password_hash'=>password_hash('Password123!',PASSWORD_DEFAULT)]);
$u1=$users->create(['first_name'=>'Alice','last_name'=>'One','email'=>'alice8@example.org','password_hash'=>password_hash('Password123!',PASSWORD_DEFAULT)]);
$u2=$users->create(['first_name'=>'Bob','last_name'=>'Two','email'=>'bob8@example.org','password_hash'=>password_hash('Password123!',PASSWORD_DEFAULT)]);
$users->assignRole($owner,'super_admin');
$users->assignRole($manager,'grant_manager');
$users->assignRole($viewer,'viewer');
$login=static function (int $id) use ($auth,$users): void { $auth->logout(); $auth->login($users->find($id),'127.0.0.1'); };
$login($manager);
verify8(call8($router,$app,'/admin/grants')[0]===200,'manager views empty grant register');
verify8(call8($router,$app,'/admin/grants/create')[0]===200,'manager can open create form');
verify8(call8($router,$app,'/admin/grants','POST',['reference'=>'GR-001','title'=>'Education support','status'=>'active','description'=>'Small internal grant record'])[0]===302,'grant created through HTTP');
$g1=$database->int("SELECT id FROM grants WHERE reference='GR-001'");
verify8($g1>0 && $grants->find($g1)['title']==='Education support','new grant persists');
verify8(call8($router,$app,'/admin/grants/'.$g1.'/edit')[0]===200,'manager can edit grant');
verify8(call8($router,$app,'/admin/grants/'.$g1,'POST',['reference'=>'GR-001','title'=>'Updated education','status'=>'active'])[0]===302 && $grants->find($g1)['title']==='Updated education','grant edited without duplication');
verify8($database->int('SELECT COUNT(*) FROM grants')===1,'edit does not duplicate grant');
$g2=$grants->save(null,'GR-002','Health support','', 'active',$manager);
verify8($g2!==$g1,'second grant created');
verify8(call8($router,$app,'/admin/grants/'.$g1.'/users','POST',['user_ids'=>[(string)$u1],'responsibilities'=>'Reporting','notes'=>'Initial review'])[0]===302,'single-user assignment via HTTP');
verify8(count(array_filter($grants->forGrant($g1),fn($a)=>$a['status']!=='removed'))===1,'grant detail lists one assignee');
verify8($grants->forGrant($g1)[0]['assigned_by']===$manager && $grants->forGrant($g1)[0]['responsibilities']==='Reporting','actor and responsibilities stored');
verify8(call8($router,$app,'/admin/grants/'.$g1.'/users','POST',['user_ids'=>[(string)$u2]])[0]===302 && count($grants->forGrant($g1))===2,'multiple users belong to one grant');
verify8(call8($router,$app,'/admin/users/'.$u2.'/grants','POST',['grant_ids'=>[(string)$g2]])[0]===302,'one user assigned from user page');
verify8(count($grants->forUser($u2))===2,'one user belongs to multiple grants');
$u3=$users->create(['first_name'=>'Cora','last_name'=>'Three','email'=>'cora8@example.org','password_hash'=>'x']);
$u4=$users->create(['first_name'=>'Dani','last_name'=>'Four','email'=>'dani8@example.org','password_hash'=>'x']);
verify8(call8($router,$app,'/admin/grants/'.$g2.'/users','POST',['user_ids'=>[(string)$u3,(string)$u4]])[0]===302 && count($grants->forGrant($g2))===3,'bulk assign two users to one grant');
verify8(call8($router,$app,'/admin/users/'.$u1.'/grants','POST',['grant_ids'=>[(string)$g2]])[0]===302 && count($grants->forUser($u1))===2,'multiple grants per second user');
$before=$database->int('SELECT COUNT(*) FROM grant_user_assignments');
try { $grants->assignUsers($g1,[$u1],'','',$manager); verify8(false,'duplicate should fail'); } catch (DomainException $e) { verify8($database->int('SELECT COUNT(*) FROM grant_user_assignments')===$before,'duplicate assignment rejected without new row'); }
try { $database->insert('grant_user_assignments',['grant_id'=>$g1,'user_id'=>$u1,'assigned_by'=>$manager]); verify8(false,'unique constraint should fail'); } catch (\PDOException $e) { verify8($e->getCode()==='23000','database unique pair constraint enforced'); }
try { $grants->assignUsers($g1,[$u4,$u1],'','',$manager); verify8(false,'batch should rollback'); } catch (DomainException $e) { verify8($database->int('SELECT COUNT(*) FROM grant_user_assignments')===$before,'failed bulk assignment is atomic'); }
verify8(call8($router,$app,'/admin/grants/'.$g1.'/users/'.$u1,'POST',['status'=>'on_hold','responsibilities'=>'Monitoring','notes'=>'Review next month'])[0]===302 && $database->int("SELECT COUNT(*) FROM grant_user_assignments WHERE grant_id=:g AND user_id=:u AND status='on_hold'",['g'=>$g1,'u'=>$u1])===1,'assignment status edited');
verify8($database->int('SELECT COUNT(*) FROM grant_user_assignments WHERE grant_id=:g AND user_id=:u AND responsibilities=:r',['g'=>$g1,'u'=>$u1,'r'=>'Monitoring'])===1,'assignment responsibilities edited');
verify8(call8($router,$app,'/admin/grants/'.$g1.'/users/'.$u1.'/remove','POST')[0]===302,'remove assignment through HTTP');
verify8($database->int("SELECT COUNT(*) FROM grant_user_assignments WHERE grant_id=:g AND user_id=:u AND status='removed'",['g'=>$g1,'u'=>$u1])===1,'removal retains historic row');
verify8(call8($router,$app,'/admin/grants/'.$g1.'/users','POST',['user_ids'=>[(string)$u1]])[0]===302 && $database->int('SELECT COUNT(*) FROM grant_user_assignments WHERE grant_id=:g AND user_id=:u',['g'=>$g1,'u'=>$u1])===1,'reassignment reuses unique row');
verify8($database->int("SELECT COUNT(*) FROM grant_assignment_events ev JOIN grant_user_assignments a ON a.id=ev.assignment_id WHERE a.grant_id=:g AND a.user_id=:u AND ev.action='reassigned'",['g'=>$g1,'u'=>$u1])===1,'reassignment keeps immutable event');
verify8($database->int("SELECT COUNT(*) FROM grant_assignment_events ev JOIN grant_user_assignments a ON a.id=ev.assignment_id WHERE a.grant_id=:g AND a.user_id=:u AND ev.action='removed' AND ev.responsibilities_before='Monitoring'",['g'=>$g1,'u'=>$u1])===1,'previous responsibilities preserved in event history');
$detail=call8($router,$app,'/admin/grants/'.$g1);
verify8($detail[0]===200 && str_contains($detail[1],'Alice') && str_contains($detail[1],'Bob'),'grant details show assignees');
verify8(call8($router,$app,'/admin/grants/'.$g1.'/users/'.$u1.'/change','POST',['new_user_id'=>(string)$u4,'responsibilities'=>'Transferred review','notes'=>'Reassigned'])[0]===302,'change assignee through HTTP');
verify8($database->int("SELECT COUNT(*) FROM grant_user_assignments WHERE grant_id=:g AND user_id=:u AND status='removed'",['g'=>$g1,'u'=>$u1])===1 && $database->int("SELECT COUNT(*) FROM grant_user_assignments WHERE grant_id=:g AND user_id=:u AND status='active'",['g'=>$g1,'u'=>$u4])===1,'change assignee preserves old history and creates replacement');
try { $grants->changeAssignee($g1,$u4,$u2,'','',$manager); verify8(false,'occupied transfer should fail'); } catch (DomainException $e) { verify8($database->int("SELECT COUNT(*) FROM grant_user_assignments WHERE grant_id=:g AND user_id=:u AND status='active'",['g'=>$g1,'u'=>$u4])===1,'duplicate transfer rolls back'); }
$login($owner);
$userDetail=call8($router,$app,'/admin/users/'.$u1);
verify8($userDetail[0]===200 && str_contains($userDetail[1],'Assigned grants') && str_contains($userDetail[1],'GR-001') && str_contains($userDetail[1],'GR-002'),'user details show grants');
$login($viewer);
verify8(call8($router,$app,'/admin/grants')[0]===403,'viewer cannot list grants');
verify8(call8($router,$app,'/admin/grants/'.$g1)[0]===403,'viewer cannot view grant details');
verify8(call8($router,$app,'/admin/grants','POST',['reference'=>'BAD','title'=>'Denied'])[0]===403,'viewer cannot create grant');
verify8(call8($router,$app,'/admin/grants/'.$g1.'/users','POST',['user_ids'=>[(string)$u1]])[0]===403,'viewer cannot assign');
$login($manager);
verify8(call8($router,$app,'/admin/users/'.$u1)[0]===200,'grant manager can view assigned grants on user page');
$grantRole=$database->int("SELECT id FROM roles WHERE name='grant_manager'");
$viewPermission=$database->int("SELECT id FROM permissions WHERE name='grant_assignments.view'");
$database->run('DELETE FROM role_permissions WHERE role_id=:r AND permission_id=:p',['r'=>$grantRole,'p'=>$viewPermission]);
$managePermission=$database->int("SELECT id FROM permissions WHERE name='grant_assignments.manage'");
$database->run('DELETE FROM role_permissions WHERE role_id=:r AND permission_id=:p',['r'=>$grantRole,'p'=>$managePermission]);
$login($manager);
$hidden=call8($router,$app,'/admin/grants/'.$g1);
verify8($hidden[0]===200 && !str_contains($hidden[1],'Alice One') && !str_contains($hidden[1],'Bob Two'),'assignment names hidden without assignment-view grant');
$database->insert('role_permissions',['role_id'=>$grantRole,'permission_id'=>$managePermission]);
$grantView=$database->int("SELECT id FROM permissions WHERE name='grants.view'");
$database->run('DELETE FROM role_permissions WHERE role_id=:r AND permission_id=:p',['r'=>$grantRole,'p'=>$grantView]);
$login($manager);
verify8(call8($router,$app,'/admin/grants/'.$g1.'/users','POST',['user_ids'=>[(string)$u1]])[0]===403,'assignment write also requires grant visibility');
try { $grants->assignUsers($g1,[$u4],'','',$manager); verify8(false,'service permission should fail'); } catch (DomainException $e) { verify8(true,'service enforces grant visibility even without route'); }
$login($owner);
$grants->save($g2,'GR-002','Health support','','closed',$owner);
try { $grants->assignUsers($g2,[$viewer],'','',$owner); verify8(false,'closed grant add should fail'); } catch (DomainException $e) { verify8(true,'closed grants reject new assignees'); }
$users->update($u4,['status'=>'suspended']);
try { $grants->assignUsers($g1,[$u4],'','',$owner); verify8(false,'suspended add should fail'); } catch (DomainException $e) { verify8(true,'suspended user cannot be assigned'); }
verify8($database->int("SELECT COUNT(*) FROM audit_logs WHERE action IN ('grant.saved','grant.assignment_created','grant.assignment_updated','grant.assignment_removed')")>=8,'grant actions are audited');
verify8($database->int('SELECT COUNT(*) FROM grants')===2,'no assignment duplicated a grant record');
echo "$checks V3 Part 8 grant/assignment checks passed.\n";
