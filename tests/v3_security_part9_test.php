<?php

declare(strict_types=1);

/** Destructive security regression test: ONLY explicitly named disposable *_test DB. */
require __DIR__.'/bootstrap.php';

use App\Core\Request;
use App\Core\Router;
use App\Core\Csrf;
use App\Core\Session;
use App\Core\Database;
use App\Exceptions\HttpException;
use App\Services\AuthService;
use App\Services\FinanceService;
use App\Repositories\UserRepository;
use Tests\Reports\Fixture;

$name=(string)getenv('TEST_DB_NAME');
if ($name==='' || !str_contains($name,'test')) { throw new RuntimeException('Set disposable TEST_DB_NAME containing test.'); }
putenv('DB_HOST='.(getenv('TEST_DB_HOST') ?: '127.0.0.1'));
putenv('DB_DATABASE='.$name);
putenv('DB_USERNAME='.(getenv('TEST_DB_USER') ?: 'alm'));
putenv('DB_PASSWORD='.(getenv('TEST_DB_PASS') ?: 'almpass'));
$db=Fixture::connect();
Fixture::migrate($db);
Fixture::seed($db);
$app=require __DIR__.'/../bootstrap/app.php';
$database=$app->make(Database::class);
$finance=$app->make(FinanceService::class);
$users=$app->make(UserRepository::class);
$auth=$app->make(AuthService::class);
$app->make(Session::class)->start();
$router=$app->make(Router::class);
$count=0;
function check9(bool $yes,string $label): void { global $count; if (!$yes) throw new RuntimeException('FAIL '.$label); $count++; }
function denied9(callable $fn): bool { try { $fn(); return false; } catch (InvalidArgumentException|PDOException $e) { return true; } }
function route9(Router $router,$app,string $path,string $method='GET',array $data=[],bool $csrf=true): array {
    if ($method==='POST' && $csrf) $data['_token']=$app->make(Csrf::class)->token();
    try { $response=$router->dispatch(new Request($method,$path,$method==='GET'?$data:[], $method==='POST'?$data:[],[],[]),$app); return [$response->status(),$response->body()]; }
    catch (HttpException $e) { return [$e->statusCode(),'']; }
}
$before=$database->selectOne('SELECT amount_minor,status FROM donations WHERE id=1');
$transactionCount=$database->int('SELECT COUNT(*) FROM payment_transactions');
check9(denied9(fn()=> $database->run('DELETE FROM donations WHERE id=1')),'payment transaction FK prevents deletion of historical donation');
check9($database->int('SELECT COUNT(*) FROM payment_transactions')===$transactionCount && $database->selectOne('SELECT amount_minor,status FROM donations WHERE id=1')==$before,'payment and donation rows preserved');
check9(denied9(fn()=> $database->run('DELETE FROM payment_transactions WHERE id=1')),'refund FK protects payment transaction');
$database->insert('payment_callbacks',['payment_transaction_id'=>8,'gateway_id'=>1,'callback_method'=>'POST','validation_result'=>'accepted']);
check9(denied9(fn()=> $database->run('DELETE FROM payment_transactions WHERE id=8')),'callback FK protects transaction without refund');
check9(denied9(fn()=> $database->insert('financial_adjustments',['donation_id'=>1,'payment_transaction_id'=>999999,'adjustment_type'=>'mark_failed','reason'=>'No transaction should link here'])),'financial adjustment FK rejects nonexistent payment');
check9(denied9(fn()=> $database->insert('grant_user_assignments',['grant_id'=>999999,'user_id'=>2])),'assignment FK rejects nonexistent grant');
check9($database->int("SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema=DATABASE() AND table_name='financial_adjustments' AND index_name='fa_payment_transaction_idx'")>0,'transaction lookup index exists');
check9($database->int("SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema=DATABASE() AND table_name='grant_user_assignments' AND index_name='grant_user_unique'")>0,'assignment unique index exists');
check9(denied9(fn()=> $finance->adjust(1,'amount_correction',['reason'=>'Try to alter history','evidence_reference'=>'statement','new_amount_minor'=>99999],2)),'historical completed amount cannot change');
check9(denied9(fn()=> $finance->adjust(1,'refund_recorded',['reason'=>'Try to refund online','evidence_reference'=>'fake'],2)),'manual online refund prohibited');
check9(denied9(fn()=> $finance->adjust(13,'mark_completed',['reason'=>'Legacy payment bypass'],2)),'legacy pending cannot be marked completed');
check9($database->selectOne('SELECT amount_minor,status FROM donations WHERE id=1')==$before,'historical donation unchanged after rejected finance changes');
$database->insert('donations',['public_reference'=>'SEC-9-LEGACY','amount_minor'=>999999,'currency'=>'PKR','gateway_id'=>null,'status'=>'completed','verification_status'=>'legacy','created_at'=>'2026-10-20 12:00:00','completed_at'=>'2026-10-20 12:00:00']);
$database->insert('donations',['public_reference'=>'SEC-9-USD','amount_minor'=>12300,'currency'=>'USD','gateway_id'=>null,'status'=>'completed','verification_status'=>'verified','created_at'=>'2026-10-20 12:00:00','completed_at'=>'2026-10-20 12:00:00']);
$summary=$finance->monthlySummary('2026-10');
$expectedPkr=$database->int("SELECT COALESCE(SUM(amount_minor),0) FROM donations WHERE status='completed' AND verification_status='verified' AND currency='PKR' AND completed_at BETWEEN '2026-10-01' AND '2026-10-31 23:59:59'");
check9($summary['completed']===$expectedPkr,'finance confirmed total excludes legacy and other currencies');
check9(count(array_filter($summary['by_currency'],fn($r)=>$r['currency']==='USD'))===1,'finance shows foreign verified currency separately');
$pending=$database->insert('donations',['public_reference'=>'SEC-9','amount_minor'=>12300,'currency'=>'PKR','gateway_id'=>null,'status'=>'pending','verification_status'=>'submitted','donor_message'=>'PRIVATE DONOR MESSAGE 9']);
check9(denied9(fn()=> $finance->adjust($pending,'amount_correction',['reason'=>'Attempt without authorization','evidence_reference'=>'statement','new_amount_minor'=>12500],1)),'service rejects unauthorized finance actor');
$users->assignRole(2,'super_admin');
$gatewayPending=$database->insert('donations',['public_reference'=>'SEC-9-GW','amount_minor'=>12300,'currency'=>'PKR','gateway_id'=>1,'status'=>'pending','verification_status'=>'submitted']);
check9(denied9(fn()=> $finance->adjust($gatewayPending,'mark_failed',['reason'=>'Attempt direct gateway failure'],2)),'online payment cannot be manually marked failed');
check9(denied9(fn()=> $finance->adjust($gatewayPending,'amount_correction',['reason'=>'Attempt gateway correction','evidence_reference'=>'statement','new_amount_minor'=>12500],2)),'online gateway amount cannot be manually changed');
check9(denied9(fn()=> $finance->adjust($pending,'amount_correction',['reason'=>'Lacking evidence details','new_amount_minor'=>12500],2)),'amount correction requires evidence');
check9(denied9(fn()=> $finance->adjust($pending,'amount_correction',['reason'=>'Wrong transaction belongs elsewhere','evidence_reference'=>'statement','new_amount_minor'=>12500,'payment_transaction_id'=>1],2)),'cross-donation transaction association rejected');
$result=$finance->adjust($pending,'amount_correction',['reason'=>'Correct the typed amount','evidence_reference'=>'statement-row-42','new_amount_minor'=>12500],2);
check9($result['id']>0 && $database->int('SELECT amount_minor FROM donations WHERE id=:id',['id'=>$pending])===12500,'authorized unverified adjustment succeeds');
check9($database->int("SELECT COUNT(*) FROM audit_logs WHERE action='finance.adjustment.amount_correction' AND entity_id=:id",['id'=>$pending])===1,'financial adjustment audit persisted');
check9($database->int('SELECT COUNT(*) FROM financial_adjustments WHERE donation_id=:id',['id'=>$pending])===1,'financial adjustment detail persisted');
$login=static function (int $id) use ($users,$auth): void { $auth->logout(); $auth->login($users->find($id),'127.0.0.1'); };
$login(3);
check9(route9($router,$app,'/admin/finance')[0]===403,'ordinary user denied finance desk');
check9(route9($router,$app,'/admin/finance/donations/1/adjust','POST',['adjustment_type'=>'amount_correction','reason'=>'attempt unauthorized'])[0]===403,'ordinary user denied finance mutation');
$login(2);
check9(route9($router,$app,'/admin/finance/donations/'.$pending.'/adjust','POST',['adjustment_type'=>'amount_correction','reason'=>'valid but missing csrf'],false)[0]===419,'finance mutation enforces CSRF');
check9(route9($router,$app,'/admin/finance/donations/'.$pending.'/adjust','POST',['adjustment_type'=>'amount_correction','reason'=>'attempt to bypass history','new_amount'=>'125.999','evidence_reference'=>'statement'])[0]===302 && $database->int('SELECT amount_minor FROM donations WHERE id=:id',['id'=>$pending])===12500,'invalid fractional amount rejected server-side without change');
check9(route9($router,$app,'/admin/finance/donations/'.$pending.'/adjust','POST',['adjustment_type'=>'amount_correction','reason'=>'attempt to bypass history','new_amount'=>'1e7','evidence_reference'=>'statement'])[0]===302 && $database->int('SELECT amount_minor FROM donations WHERE id=:id',['id'=>$pending])===12500,'scientific notation rejected server-side');
$database->update('donors',['name'=>'=HYPERLINK("bad")'],'id=:id',['id'=>1]);
$csv=route9($router,$app,'/admin/finance/export','GET',['month'=>'2026-10']);
check9($csv[0]===200 && str_contains($csv[1],"'=HYPERLINK"),'finance CSV neutralizes spreadsheet formula input');
check9($database->int("SELECT COUNT(*) FROM audit_logs WHERE action='finance.export.downloaded'")===1,'sensitive finance export requires audit event');
$public=route9($router,$app,'/donation/SEC-9');
check9($public[0]===200 && !str_contains($public[1],'PRIVATE DONOR MESSAGE 9'),'public reference page does not expose donor message');
check9($database->int('SELECT COUNT(*) FROM donations')>0 && $database->int('SELECT COUNT(*) FROM payment_transactions')===$transactionCount,'security checks did not destroy financial history');
echo "$count V3 Part 9 security/integrity checks passed.\n";
