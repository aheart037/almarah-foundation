<?php

declare(strict_types=1);

/** Destructive migration-runner rehearsal, ONLY against an explicit *_test database. */
require __DIR__.'/bootstrap.php';
use Tests\Reports\Fixture;
use App\Database\Migrator;
use App\Core\Logger;

$name=(string)getenv('TEST_DB_NAME');
if ($name==='' || !str_contains($name,'test')) throw new RuntimeException('Set an explicit disposable TEST_DB_NAME containing test.');
$db=Fixture::connect();
$pdo=$db->pdo();
$pdo->exec('SET FOREIGN_KEY_CHECKS=0');
foreach ($db->select("SELECT table_name AS t FROM information_schema.tables WHERE table_schema=DATABASE() AND table_type='BASE TABLE'") as $row) $pdo->exec('DROP TABLE IF EXISTS `'.$row['t'].'`');
foreach ($db->select("SELECT table_name AS t FROM information_schema.views WHERE table_schema=DATABASE()") as $row) $pdo->exec('DROP VIEW IF EXISTS `'.$row['t'].'`');
$pdo->exec('SET FOREIGN_KEY_CHECKS=1');
$migrator=new Migrator($db,__DIR__.'/../database/migrations',new Logger(__DIR__.'/../storage/logs'));
$applied=$migrator->run();
if (count($applied)!==count($migrator->files()) || $migrator->pending()!==[] || $migrator->run()!==[]) throw new RuntimeException('Migration runner did not apply all files exactly once.');
if ($db->int("SELECT COUNT(*) FROM information_schema.key_column_usage WHERE table_schema=DATABASE() AND table_name='financial_adjustments' AND constraint_name='fk_fadj_transaction'")!==1) throw new RuntimeException('Financial adjustment FK missing.');
echo count($applied)." migrations applied once and re-run cleanly on a disposable database.\n";
