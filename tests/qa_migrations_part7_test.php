<?php

declare(strict_types=1);
/** Destructive upgrade rehearsal on disposable *_test DB: historical donation retained. */
require __DIR__ . '/bootstrap.php';
use Tests\Reports\Fixture;
$db=Fixture::connect();
$pdo=$db->pdo();
$pdo->exec('SET FOREIGN_KEY_CHECKS=0');
foreach ($db->select("SELECT table_name t FROM information_schema.tables WHERE table_schema=DATABASE() AND table_type='BASE TABLE'") as $r) $pdo->exec('DROP TABLE IF EXISTS `' . $r['t'] . '`');
foreach ($db->select("SELECT table_name t FROM information_schema.views WHERE table_schema=DATABASE()") as $r) $pdo->exec('DROP VIEW IF EXISTS `' . $r['t'] . '`');
$pdo->exec('SET FOREIGN_KEY_CHECKS=1');
$files=glob(__DIR__.'/../database/migrations/*.sql'); sort($files);
foreach ($files as $file) if (basename($file)< '0012_donation_review.sql') $pdo->exec((string)file_get_contents($file));
$db->insert('users',['id'=>1,'first_name'=>'Historical','last_name'=>'Admin','email'=>'historical-qa@example.org','password_hash'=>password_hash('safe-password', PASSWORD_DEFAULT),'status'=>'active']);
$db->insert('donors',['id'=>1,'name'=>'Old donor','email'=>'old-donor-qa@example.org']);
$db->insert('gateways',['id'=>1,'code'=>'meezan','name'=>'Meezan','enabled'=>1]);
$old=$db->insert('donations',['public_reference'=>'HISTORICAL-QA','donor_id'=>1,'gateway_id'=>1,'amount_minor'=>12345,'currency'=>'PKR','status'=>'completed','completed_at'=>'2025-01-02 12:00:00']);
$before=$db->selectOne('SELECT id,public_reference,donor_id,gateway_id,amount_minor,currency,status,completed_at FROM donations WHERE id=:id',['id'=>$old]);
foreach ($files as $file) if (basename($file)>='0012_donation_review.sql') $pdo->exec((string)file_get_contents($file));
$after=$db->selectOne('SELECT id,public_reference,donor_id,gateway_id,amount_minor,currency,status,completed_at,verification_status FROM donations WHERE id=:id',['id'=>$old]);
foreach ($before as $key=>$value) if ((string)$after[$key] !== (string)$value) throw new RuntimeException('Historical donation field changed: '.$key);
if ($after['verification_status']!=='legacy' || $db->int('SELECT COUNT(*) FROM donors WHERE id=1')!==1) throw new RuntimeException('Historical gift or donor not preserved');
if ($db->int("SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema=DATABASE() AND table_name='donations' AND index_name='donation_review_status_idx'") < 1) throw new RuntimeException('Verification index absent');
if ($db->int("SELECT COUNT(*) FROM information_schema.key_column_usage WHERE table_schema=DATABASE() AND table_name='donation_review_events' AND referenced_table_name='donations'") < 1) throw new RuntimeException('Review history FK absent');
try { $db->insert('donation_review_events',['donation_id'=>999999,'stage_key'=>'initial_review','decision'=>'approve','comment'=>'invalid']); throw new RuntimeException('Orphan history inserted'); }
catch (PDOException $e) { /* FK correctly refuses an orphan */ }
$logger = new \App\Core\Logger(__DIR__.'/../storage/logs');
require_once __DIR__.'/../database/seeders/RoleSeeder.php';
require_once __DIR__.'/../database/seeders/SiteContentSeeder.php';
$roleSeeder = new \Database\Seeders\RoleSeeder($db, $logger);
$roleSeeder->run();
(new \Database\Seeders\SiteContentSeeder($db, $logger))->run();
$alias = $db->int("SELECT COUNT(*) FROM roles r JOIN role_permissions rp ON rp.role_id=r.id JOIN permissions p ON p.id=rp.permission_id WHERE r.name='initial_review' AND p.name='donations.view'");
if ($alias!==1 || $db->int("SELECT COUNT(*) FROM roles WHERE name='initial_reviewer'")!==1 || !in_array('initial_review', \App\Services\AuthService::STAFF_ROLES, true)) throw new RuntimeException('New first-review role alias missing or old role removed');
if (!in_array('initial_review', (require __DIR__.'/../config/donation_review.php')['stages']['initial_review']['roles'], true)) throw new RuntimeException('New reviewer role cannot approve initial stage');
putenv('DB_HOST=' . (getenv('TEST_DB_HOST') ?: '127.0.0.1'));
putenv('DB_DATABASE=' . (getenv('TEST_DB_NAME') ?: 'almarah_test'));
putenv('DB_USERNAME=' . (getenv('TEST_DB_USER') ?: 'alm'));
putenv('DB_PASSWORD=' . (getenv('TEST_DB_PASS') ?: 'almpass'));
$app=require __DIR__.'/../bootstrap/app.php';
$review=$app->make(\App\Services\DonationReviewService::class);
$gift=$review->submit(['source'=>'bank_transfer','donor_name'=>'QA role donor','donor_email'=>'qa-role@example.org','donor_phone'=>'+923001234567','amount'=>'100.00','received_at'=>gmdate('Y-m-d'),'bank_reference'=>'QA-ROLE-1']);
$review->decide($gift['id'],1,['initial_review'],'approve','Evidence inspected for QA.');
if ($db->selectOne('SELECT verification_status FROM donations WHERE id=:id',['id'=>$gift['id']])['verification_status']!=='initial_review') throw new RuntimeException('Alias reviewer could not perform first stage');
echo "Part 7 pre-V2 donation upgrade retained historical values; legacy classification, index, FK and both first-review roles performed as expected.\n";
