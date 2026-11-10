<?php

declare(strict_types=1);

/** Destructive Part 4 reporting integration checks; only use a disposable *_test DB. */
require __DIR__ . '/bootstrap.php';
use Tests\Reports\Fixture;
use App\Services\ReportService;

$db=Fixture::connect();
Fixture::migrate($db);
Fixture::seed($db);
$svc=new ReportService($db);
$range=['date_from'=>'2026-10-01','date_to'=>'2026-10-31','currency'=>'PKR'];
$count=0;
function p4(bool $value,string $label): void { global $count; if (!$value) throw new RuntimeException('FAIL '.$label); $count++; }
function p4card(array $report,string $label): int { foreach ($report['summary'] as $c) if ($c['label']===$label) return (int)$c['value']; throw new RuntimeException('Card missing: '.$label); }
$db->insert('donors',['id'=>50,'name'=>'Unverified Historical','email'=>'old@example.org']);
$db->insert('donations',['public_reference'=>'LEGACY-COMPLETE','donor_id'=>50,'gateway_id'=>1,'amount_minor'=>900000,'currency'=>'PKR','status'=>'completed','verification_status'=>'legacy','created_at'=>'2026-10-19 12:00:00','completed_at'=>'2026-10-19 12:00:00']);
$db->insert('payment_transactions',['donation_id'=>2,'gateway_id'=>2,'environment'=>'live','merchant_order_id'=>'DUP-ORDER','provider_transaction_id'=>'DUP-TXN','callback_state_hash'=>hash('sha256','dup'),'callback_state_expires_at'=>'2030-01-01 00:00:00','amount_minor'=>250000,'currency'=>'PKR','status'=>'completed','request_reference'=>'dup','idempotency_key'=>'dup-idem','created_at'=>'2026-10-10 12:00:00']);
$db->insert('payment_refunds',['payment_transaction_id'=>2,'donation_id'=>2,'gateway_id'=>2,'amount_minor'=>50000,'currency'=>'PKR','status'=>'completed','idempotency_key'=>'partial-refund','completed_at'=>'2026-10-22 12:00:00','created_at'=>'2026-10-22 12:00:00']);
$db->update('donations',['receipt_number'=>'R-0001','recipient_name'=>'A Recipient','certificate_status'=>'issued'],'id=:id',['id'=>1]);
$db->insert('donation_review_events',['donation_id'=>1,'actor_user_id'=>1,'stage_key'=>'final_verification','decision'=>'approve','comment'=>'Approved','created_at'=>'2026-10-05 12:00:00']);
$overview=$svc->run('overview',$range);
p4(p4card($overview,'Confirmed donations')===1030000,'legacy money not classified verified');
p4(p4card($overview,'Failed payments')===2,'failed payment attempts (incl retry) are distinct from donations');
p4(count($overview['extra'][0]['rows'])>0 && count($overview['extra'][1]['rows'])===1,'recent transactions and actual verification events');
$financial=$svc->run('financial',$range);
p4(p4card($financial,'Gross received')===1330000,'gross includes original verified refunded gift once; duplicate gateway payment not a second donation');
p4(p4card($financial,'Refunded')===350000,'completed full plus partial refunds counted once');
p4(p4card($financial,'Net received')===980000,'net is verified gross minus actual completed refunds');
p4(array_sum(array_map(static fn ($g): int => (int)$g['net_minor'],$financial['extra'][0]['rows']))===980000,'gateway subtotals reconcile to net');
p4(array_sum(array_map(static fn ($g): int => (int)$g['net_minor'],$financial['extra'][1]['rows']))===980000,'campaign subtotals reconcile to net');
p4(p4card($svc->run('financial',array_merge($range,['currency'=>'USD'])),'Gross received')===1000,'currencies isolated');
p4(p4card($svc->run('financial',array_merge($range,['q'=>'old@example.org'])),'Gross received')===0,'financial search does not revive legacy funds');
p4(p4card($svc->run('financial',array_merge($range,['q'=>'DON-0002'])),'Refunded')===50000,'financial search applies to refunds as well as donations');
$partial=$svc->run('refunds',array_merge($range,['type'=>'partial_refund','q'=>'DON-0002']));
p4($partial['total']===1 && (int)$partial['rows'][0]['refund_amount_minor']===50000,'partial refund appears as independently searchable paginated transaction');
p4(p4card($svc->run('refunds',$range),'Refunded amount')===350000,'refund report sums actual completed full and partial refunds');
p4(p4card($svc->run('donations',array_merge($range,['q'=>'old@example.org'])),'Confirmed amount')===0,'donation search respects staff verification');
p4(p4card($svc->run('donations',array_merge($range,['q'=>'d2@example.org'])),'Confirmed amount')===320000,'donation summary search filters real donor records');
$payment=$svc->run('payments',array_merge($range,['q'=>'DUP-ORDER']));
p4($payment['total']===1 && $payment['rows'][0]['donor']==='Donor Two' && $payment['rows'][0]['verification_status']==='verified','payment attempts show donor and verification without duplicating donation revenue');
$receipt=$svc->run('receipts',array_merge($range,['q'=>'DON-0001']));
p4($receipt['total']===1 && $receipt['rows'][0]['receipt_number']==='R-0001' && $receipt['rows'][0]['recipient']==='A Recipient','receipt uses issued number, not public reference');
p4(!in_array('LEGACY-COMPLETE',array_column($svc->run('receipts',$range)['rows'],'public_reference'),true),'legacy completed donation not falsely presented as official receipt');
$donors=$svc->run('donors',array_merge($range,['q'=>'d1@example.org']));
p4($donors['total']===1 && str_contains($donors['rows'][0]['recipients'],'A Recipient'),'donor acknowledgment recipient from actual gift');
p4(isset($svc->run('campaigns',$range)['rows'][0]['remaining_minor']),'campaign remaining target derived from real combined fundraiser goals');
$unbackedBefore=p4card($svc->run('reconciliation',$range),'Confirmed without payment');
$offlineId=$db->insert('donations',['public_reference'=>'OFFLINE-REAL','donor_id'=>1,'gateway_id'=>null,'source'=>'bank_transfer','payment_method'=>'bank_transfer','bank_reference'=>'BANK-REAL-9','transaction_at'=>'2026-10-20 09:00:00','amount_minor'=>50000,'currency'=>'PKR','status'=>'completed','verification_status'=>'verified','verified_by'=>1,'created_at'=>'2026-10-20 09:00:00','completed_at'=>'2026-10-20 09:00:00']);
$bank=$svc->run('reconciliation',array_merge($range,['issue'=>'offline_transfers','q'=>'BANK-REAL-9']));
p4($bank['total']===1 && $bank['rows'][0]['verified_by']!==null && $bank['rows'][0]['reconciliation_status']==='Not recorded','actual offline transfer visible without invented bank match');
p4(p4card($bank,'Verified amount')===50000,'verified offline transfer counted in its own report');
p4(p4card($svc->run('reconciliation',$range),'Confirmed without payment')===$unbackedBefore,'offline transfers are not mislabeled as missing gateway payments');
p4(p4card($svc->run('financial',$range),'Net received')===1030000,'staff-verified offline donation added once to net');
$methods=$svc->run('financial',$range)['extra'][0]['rows'];
p4(in_array('bank_transfer',array_column($methods,'gateway'),true),'offline verified gifts appear under actual payment method rather than unknown gateway');
$db->insert('payment_refunds',['payment_transaction_id'=>1,'donation_id'=>1,'gateway_id'=>1,'amount_minor'=>10000,'currency'=>'PKR','status'=>'completed','idempotency_key'=>'next-month-partial','completed_at'=>'2026-11-01 12:00:00','created_at'=>'2026-11-01 12:00:00']);
$nov=$svc->run('financial',['date_from'=>'2026-11-01','date_to'=>'2026-11-30','currency'=>'PKR']);
p4(p4card($nov,'Gross received')===0 && p4card($nov,'Refunded')===10000 && p4card($nov,'Net received')===-10000,'refund-only month has negative net without fabricated gross');
p4(array_sum(array_column($nov['extra'][0]['rows'],'net_minor'))===-10000 && array_sum(array_column($nov['extra'][1]['rows'],'net_minor'))===-10000,'refund-only method and campaign subtotals reconcile');
$searched=$svc->run('overview',array_merge($range,['q'=>'Fundraiser One','sort'=>'title','dir'=>'asc']));
p4($searched['total']===1 && $searched['rows'][0]['title']==='Fundraiser One','overview results searchable and sortable without altering dashboard cards');
p4(p4card($searched,'Confirmed donations')===1080000,'overview search does not secretly narrow financial dashboard cards');
$db->insert('audit_logs',['user_id'=>1,'action'=>'donation.review.approve','entity_type'=>'donation','entity_id'=>'1','safe_metadata_json'=>json_encode(['previous'=>['status'=>'pending'],'new'=>['status'=>'verified']]),'ip_address'=>'192.0.2.1','created_at'=>'2026-10-31 13:00:00']);
$audit=$svc->run('audit',array_merge($range,['who'=>'all']));
p4($audit['total']>0 && array_key_exists('role',$audit['rows'][0]) && array_key_exists('previous_value',$audit['rows'][0]),'audit role and stored before/after fields exposed without inventing values');
p4(str_contains($audit['rows'][0]['previous_value'],'pending') && str_contains($audit['rows'][0]['new_value'],'verified') && $audit['rows'][0]['ip_address']==='192.0.2.1','audit before/after and IP reflect stored safe metadata only');
foreach (ReportService::catalog() as $slug=>$item) {
    $r=$svc->run($slug,$range);
    p4(isset($r['rows'],$r['columns'],$r['total'],$r['lastPage']) && $r['total']>=0,$slug.' report executes on real records');
}
// Full HTTP/view smoke test: all 13 existing routes and the expandable sidebar.
putenv('DB_HOST=' . (getenv('TEST_DB_HOST') ?: '127.0.0.1'));
putenv('DB_DATABASE=' . (getenv('TEST_DB_NAME') ?: 'almarah_test'));
putenv('DB_USERNAME=' . (getenv('TEST_DB_USER') ?: 'alm'));
putenv('DB_PASSWORD=' . (getenv('TEST_DB_PASS') ?: 'almpass'));
$app=require __DIR__.'/../bootstrap/app.php';
foreach (['dashboard.view','reports.view'] as $name) {
    $id=$db->insert('permissions',['name'=>$name,'group_name'=>'reports','label'=>$name]);
    $db->insert('role_permissions',['role_id'=>1,'permission_id'=>$id]);
}
$session=$app->make(\App\Core\Session::class); $session->start();
$app->make(\App\Services\AuthService::class)->login($db->selectOne('SELECT * FROM users WHERE id=1'),'127.0.0.1');
$router=$app->make(\App\Core\Router::class);
foreach (array_keys(ReportService::catalog()) as $slug) {
    $path=$slug==='overview' ? '/admin/reports' : '/admin/reports/'.$slug;
    $resp=$router->dispatch(new \App\Core\Request('GET',$path,$range,[],[],[]),$app);
    p4($resp->status()===200 && str_contains($resp->body(),'<table') && str_contains($resp->body(),'Reports'),$slug.' existing admin route renders live report');
    if ($slug==='overview') p4(substr_count($resp->body(),'class="nav-sub')>=13,'13 report submenu links in expandable existing sidebar');
}
echo "{$count} Part 4 reporting assertions passed.\n";
