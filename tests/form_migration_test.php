<?php

declare(strict_types=1);

/** Destructive pre-V2 data preservation check; use only a disposable *test* database. */
require __DIR__ . '/bootstrap.php';
use Tests\Reports\Fixture;

$db = Fixture::connect();
$pdo = $db->pdo();
$pdo->exec('SET FOREIGN_KEY_CHECKS=0');
foreach ($db->select("SELECT table_name AS t FROM information_schema.tables WHERE table_schema=DATABASE() AND table_type='BASE TABLE'") as $r) $pdo->exec('DROP TABLE IF EXISTS `' . $r['t'] . '`');
foreach ($db->select("SELECT table_name AS t FROM information_schema.views WHERE table_schema=DATABASE()") as $r) $pdo->exec('DROP VIEW IF EXISTS `' . $r['t'] . '`');
$pdo->exec('SET FOREIGN_KEY_CHECKS=1');
$files=glob(__DIR__ . '/../database/migrations/*.sql'); sort($files);
foreach ($files as $file) {
    if (basename($file)==='0013_contact_volunteer_workflow.sql') continue;
    $pdo->exec((string)file_get_contents($file));
}
$contact=$db->insert('contact_messages',['name'=>'Historical','email'=>'historical@example.org','message'=>'A previous message','status'=>'replied','admin_notes'=>'Past reply']);
$volunteer=$db->insert('volunteers',['first_name'=>'Earlier','last_name'=>'Applicant','email'=>'earlier@example.org','phone'=>'123456789','status'=>'approved','skills'=>'Teaching']);
$pdo->exec((string)file_get_contents(__DIR__ . '/../database/migrations/0013_contact_volunteer_workflow.sql'));
$c=$db->selectOne('SELECT * FROM contact_messages WHERE id=:id',['id'=>$contact]);
$v=$db->selectOne('SELECT * FROM volunteers WHERE id=:id',['id'=>$volunteer]);
if ($c['status']!=='responded' || $c['admin_notes']!=='Past reply' || $v['status']!=='approved' || $v['skills']!=='Teaching') throw new RuntimeException('Historical data changed unexpectedly.');
if ($db->int("SELECT COUNT(*) FROM form_case_events WHERE contact_id=$contact AND to_status='replied'")!==1 || $db->int("SELECT COUNT(*) FROM form_case_events WHERE volunteer_id=$volunteer AND to_status='approved'")!==1) throw new RuntimeException('Historical states not captured.');
echo "Legacy contact/volunteer migration preserved records and prior statuses.\n";
