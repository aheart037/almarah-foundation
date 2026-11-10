<?php

declare(strict_types=1);
/** Public route, admin navigation, login and gateway hand-off regression smoke. */
require __DIR__ . '/bootstrap.php';
use Tests\Reports\Fixture;
use App\Core\Request;
use App\Core\Router;
use App\Core\Session;
use App\Core\Csrf;
use App\Services\AuthService;
use App\Http\Controllers\Public\PageController;
putenv('DB_HOST=' . (getenv('TEST_DB_HOST') ?: '127.0.0.1'));
putenv('DB_DATABASE=' . (getenv('TEST_DB_NAME') ?: 'almarah_test'));
putenv('DB_USERNAME=' . (getenv('TEST_DB_USER') ?: 'alm'));
putenv('DB_PASSWORD=' . (getenv('TEST_DB_PASS') ?: 'almpass'));
$db=Fixture::connect(); Fixture::migrate($db); Fixture::seed($db);
$app=require __DIR__ . '/../bootstrap/app.php'; $router=$app->make(Router::class); $session=$app->make(Session::class); $session->start();
$n=0;
function qaRoute(bool $condition, string $label): void { global $n; if (!$condition) throw new RuntimeException('FAIL '.$label); $n++; }
function requestRoute($router,$app,$method,$path,$data=[]): \App\Core\Response { $_SERVER['REQUEST_URI']=$path; return $router->dispatch(new Request($method,$path, $method==='GET'?$data:[], $method==='POST'?$data:[],[],[]),$app); }
foreach (['/','/donate','/donate/transfer','/login',...array_map(static fn($s)=>'/'.$s,array_keys(PageController::PAGES))] as $path) {
    $response=requestRoute($router,$app,'GET',$path);
    qaRoute($response->status()===200 && str_contains($response->body(),'<html'),$path.' renders');
}
qaRoute(str_contains(requestRoute($router,$app,'GET','/donate')->body(),'fundraise-submit.php'), 'existing gateway submission handoff remains');
$publicStatus=requestRoute($router,$app,'GET','/donation/DON-0001')->body();
qaRoute(!str_contains($publicStatus,'Donor One') && !str_contains($publicStatus,'Bank transaction') && str_contains($publicStatus,'DON-0001'), 'public status keeps reference but hides donor identity and provider transaction ID');
for ($i=1;$i<60;$i++) requestRoute($router,$app,'GET','/donation/UNKNOWN-'.$i);
qaRoute(requestRoute($router,$app,'GET','/donation/UNKNOWN-LAST')->status()===429, 'public donation reference enumeration throttled after 60 checks per minute');
foreach (['/payments/meezan/return','/payments/meezan/callback','/payments/etisalat/return','/payments/etisalat/callback'] as $path) {
    $routes=(new ReflectionProperty(Router::class,'routes'))->getValue($router);
    qaRoute(count(array_filter($routes,static fn($r)=>$r['pattern']===$path))>0,$path.' is still registered');
}
$db->update('users',['password_hash'=>password_hash('Correct-Horse-QA-2026',PASSWORD_DEFAULT)],'id=:id',['id'=>1]);
try { requestRoute($router,$app,'POST','/login',['email'=>'admin@example.org','password'=>'Correct-Horse-QA-2026']); throw new RuntimeException('Login without CSRF succeeded'); }
catch (\App\Exceptions\HttpException $e) { qaRoute($e->statusCode()===419, 'login requires CSRF'); }
$token=$app->make(Csrf::class)->token();
$bad=requestRoute($router,$app,'POST','/login',['_token'=>$token,'email'=>'admin@example.org','password'=>'wrong']);
qaRoute($bad->status()===302 && !$app->make(AuthService::class)->check(),'incorrect password rejected');
$good=requestRoute($router,$app,'POST','/login',['_token'=>$token,'email'=>'admin@example.org','password'=>'Correct-Horse-QA-2026']);
qaRoute($good->status()===302 && $app->make(AuthService::class)->check(),'existing login accepts hashed password');
foreach (['dashboard.view','content.manage','donations.view','reports.view','logs.view'] as $name) {
    $id=$db->insert('permissions',['name'=>$name,'group_name'=>explode('.',$name)[0],'label'=>$name]);
    $db->insert('role_permissions',['role_id'=>1,'permission_id'=>$id]);
}
foreach (['/admin','/admin/campaigns','/admin/donations','/admin/reports','/admin/audit'] as $path) {
    $response=requestRoute($router,$app,'GET',$path);
    qaRoute($response->status()===200 && str_contains($response->body(),'dash-nav'),$path.' admin shell and navigation render');
}
$auth=$app->make(AuthService::class); $auth->logout();
try { requestRoute($router,$app,'GET','/admin/donations'); throw new RuntimeException('Guest accessed donations'); }
catch (\App\Exceptions\HttpException $e) { qaRoute($e->statusCode()===302,'guest redirected from financial records'); }
echo "$n Part 7 public/admin/login route smoke assertions passed.\n";
