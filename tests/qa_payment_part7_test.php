<?php

declare(strict_types=1);
/** Provider-state regression with synthetic verified/unverified results; no bank network. */
require __DIR__ . '/bootstrap.php';
use Tests\Reports\Fixture;
use App\Payments\Dtos\PaymentVerificationResult;
use App\Services\PaymentStatusService;
use App\Services\ReportService;
putenv('DB_HOST=' . (getenv('TEST_DB_HOST') ?: '127.0.0.1'));
putenv('DB_DATABASE=' . (getenv('TEST_DB_NAME') ?: 'almarah_test'));
putenv('DB_USERNAME=' . (getenv('TEST_DB_USER') ?: 'alm'));
putenv('DB_PASSWORD=' . (getenv('TEST_DB_PASS') ?: 'almpass'));
$db=Fixture::connect(); Fixture::migrate($db); Fixture::seed($db);
$app=require __DIR__.'/../bootstrap/app.php';
$service=$app->make(PaymentStatusService::class);
$db->update('donations',['verification_status'=>'submitted'],'id=:id',['id'=>13]);
$donation=$db->selectOne('SELECT * FROM donations WHERE id=13');
$txn=$db->selectOne('SELECT * FROM payment_transactions WHERE donation_id=13');
$bad=new PaymentVerificationResult('meezan','completed',false,providerCode:'unverified',providerMessage:'Provider amount mismatched');
$result=$service->applyVerifiedResult($txn,$donation,$bad);
if ($result['changed'] || $db->selectOne('SELECT status FROM donations WHERE id=13')['status']!=='pending') throw new RuntimeException('Unverified provider response completed a donation');
$good=new PaymentVerificationResult('meezan','completed',true,providerCode:'00',providerTransactionId:'QA-PAID-13');
$result=$service->applyVerifiedResult($txn,$donation,$good);
if (!$result['changed'] || $db->selectOne('SELECT status,verification_status FROM donations WHERE id=13')['verification_status']!=='submitted') throw new RuntimeException('Gateway confirmation incorrectly bypassed staff verification');
$after=$db->selectOne('SELECT * FROM donations WHERE id=13');
$again=$service->applyVerifiedResult($txn,$after,$good);
if ($again['changed'] || $again['reason']!=='already_terminal') throw new RuntimeException('Duplicate provider callback changed completed donation');
$report=$app->make(ReportService::class)->run('financial',['date_from'=>'2000-01-01','date_to'=>'2100-12-31','currency'=>'PKR']);
$gross=0; foreach ($report['summary'] as $card) if ($card['label']==='Gross received') $gross=(int)$card['value'];
if ($gross!==2480000) throw new RuntimeException('Gateway-confirmed but unverified donation polluted verified gross');
echo "Part 7 synthetic payment result checks passed: unverified result, bank confirmation, staff gate, duplicate callback and verified report exclusion.\n";
