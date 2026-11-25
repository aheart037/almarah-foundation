<?php

declare(strict_types=1);

/**
 * Deletion of junk donations / in-kind donations (App\Services\RecordDeletionService).
 *
 * Self-contained: runs against an in-memory SQLite database that mirrors the relevant tables and,
 * importantly, the same ON DELETE RESTRICT / CASCADE foreign keys as the MySQL migrations.
 * No MySQL, no .env, nothing on disk is touched except one throw-away file under storage/private/.
 *
 *   php tests/record_deletion_test.php
 */

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Core\Database;
use App\Core\Logger;
use App\Services\AuditService;
use App\Services\RecordDeletionService;
use App\Services\UploadService;

$failures = 0;
$checks = 0;
$ok = static function (bool $cond, string $label) use (&$failures, &$checks): void {
    $checks++;
    echo ($cond ? '  PASS  ' : '  FAIL  ') . $label . "\n";
    if (!$cond) {
        $failures++;
    }
};

$db = new Database(['driver' => 'sqlite', 'database' => ':memory:']);
$pdo = $db->pdo();
foreach ([
    'CREATE TABLE donors (id INTEGER PRIMARY KEY, name TEXT, email TEXT)',
    'CREATE TABLE donations (id INTEGER PRIMARY KEY, public_reference TEXT, donor_id INTEGER, gateway_id INTEGER, amount_minor INTEGER,
        currency TEXT DEFAULT "PKR", status TEXT, verification_status TEXT DEFAULT "legacy", receipt_number TEXT, source TEXT, bank_reference TEXT,
        payment_proof_path TEXT, created_at TEXT)',
    'CREATE TABLE payment_transactions (id INTEGER PRIMARY KEY, donation_id INTEGER NOT NULL REFERENCES donations(id) ON DELETE RESTRICT)',
    'CREATE TABLE payment_callbacks (id INTEGER PRIMARY KEY, payment_transaction_id INTEGER REFERENCES payment_transactions(id) ON DELETE RESTRICT)',
    'CREATE TABLE payment_refunds (id INTEGER PRIMARY KEY, donation_id INTEGER NOT NULL REFERENCES donations(id) ON DELETE RESTRICT)',
    'CREATE TABLE financial_adjustments (id INTEGER PRIMARY KEY, donation_id INTEGER NOT NULL REFERENCES donations(id))',
    'CREATE TABLE reconciliation_items (id INTEGER PRIMARY KEY, donation_id INTEGER REFERENCES donations(id) ON DELETE RESTRICT)',
    'CREATE TABLE donation_review_events (id INTEGER PRIMARY KEY, donation_id INTEGER NOT NULL REFERENCES donations(id))',
    'CREATE TABLE donation_certificates (id INTEGER PRIMARY KEY, donation_id INTEGER NOT NULL REFERENCES donations(id) ON DELETE RESTRICT, status TEXT)',
    'CREATE TABLE inkind_donations (id INTEGER PRIMARY KEY, public_reference TEXT, donor_name TEXT, donor_email TEXT, status TEXT, photos_json TEXT, created_at TEXT)',
    'CREATE TABLE inkind_donation_items (id INTEGER PRIMARY KEY, inkind_id INTEGER NOT NULL REFERENCES inkind_donations(id) ON DELETE CASCADE)',
    'CREATE TABLE inkind_events (id INTEGER PRIMARY KEY, inkind_id INTEGER NOT NULL REFERENCES inkind_donations(id) ON DELETE CASCADE)',
    'CREATE TABLE inkind_certificates (id INTEGER PRIMARY KEY, inkind_id INTEGER NOT NULL REFERENCES inkind_donations(id) ON DELETE CASCADE, status TEXT)',
    'CREATE TABLE deleted_records (id INTEGER PRIMARY KEY AUTOINCREMENT, record_type TEXT, record_id INTEGER, reference TEXT, summary TEXT, snapshot TEXT, reason TEXT NOT NULL, deleted_by INTEGER, deleted_at TEXT DEFAULT CURRENT_TIMESTAMP)',
    'CREATE TABLE audit_logs (id INTEGER PRIMARY KEY, user_id INTEGER, action TEXT, entity_type TEXT, entity_id TEXT, safe_metadata_json TEXT,
        ip_address TEXT, user_agent TEXT, created_at TEXT DEFAULT CURRENT_TIMESTAMP)',
] as $ddl) {
    $pdo->exec($ddl);
}

$tmpLogs = sys_get_temp_dir() . '/rdel-logs-' . getmypid();
@mkdir($tmpLogs, 0777, true);
$logger = new Logger($tmpLogs);
$uploads = new UploadService($logger, dirname(__DIR__) . '/uploads');
$svc = new RecordDeletionService($db, $uploads, new AuditService($db, $logger));

$why = 'Test entry created while training staff';
$old = gmdate('Y-m-d H:i:s', time() - 7200);
$fresh = gmdate('Y-m-d H:i:s', time() - 60);
$mk = static function (array $o) use ($db, $old): int {
    return $db->insert('donations', $o + [
        'public_reference' => 'ALM-' . bin2hex(random_bytes(3)), 'donor_id' => 1, 'gateway_id' => null, 'amount_minor' => 500000,
        'status' => 'pending', 'verification_status' => 'submitted', 'created_at' => $old,
    ]);
};
$count = static fn (string $t, string $w = '1=1'): int => $db->int("SELECT COUNT(*) FROM {$t} WHERE {$w}");
$db->insert('donors', ['id' => 1, 'name' => 'Test Donor', 'email' => 'test@example.com']);

echo "Donations\n";

// 1. A junk offline donation with a bank slip, review history, a gateway attempt and callbacks.
$proofDir = dirname(__DIR__) . '/storage/private/test-proofs';
@mkdir($proofDir, 0777, true);
$proofFile = $proofDir . '/slip-' . getmypid() . '.png';
file_put_contents($proofFile, 'x');
$rel = 'private/test-proofs/' . basename($proofFile);
$junk = $mk(['payment_proof_path' => $rel]);
$db->insert('donation_review_events', ['donation_id' => $junk]);
$db->insert('donation_certificates', ['donation_id' => $junk, 'status' => 'failed']);
$tx = $db->insert('payment_transactions', ['donation_id' => $junk]);
$db->insert('payment_callbacks', ['payment_transaction_id' => $tx]);
$r = $svc->deleteDonation($junk, 7, $why);
$ok($r['ok'] === true, 'pending junk donation is deleted');
$ok($count('donations', "id = {$junk}") === 0, '  donation row is gone');
$ok($count('payment_transactions') === 0 && $count('payment_callbacks') === 0, '  its payment attempts and callbacks are gone');
$ok($count('donation_review_events') === 0 && $count('donation_certificates') === 0, '  its review history and failed certificate row are gone');
$ok(!is_file($proofFile), '  the uploaded bank slip file was removed');
$audit = $db->selectOne("SELECT * FROM audit_logs WHERE action = 'donation.deleted'");
$ok($audit !== null && (int) $audit['user_id'] === 7 && (int) $audit['entity_id'] === $junk, '  an audit record names who deleted it');
$ok($audit !== null && str_contains((string) $audit['safe_metadata_json'], 'Test Donor'), '  the audit record keeps a snapshot of what was removed');

// 2. Money records are refused, and nothing changes.
$protected = [
    'completed'                 => $mk(['status' => 'completed']),
    'refunded'                  => $mk(['status' => 'refunded']),
    'processing'                => $mk(['status' => 'processing']),
    'verified (status failed)'  => $mk(['status' => 'failed', 'verification_status' => 'verified']),
    'has receipt number'        => $mk(['status' => 'pending', 'receipt_number' => 'R-100']),
    'online pending < 1 hour'   => $mk(['status' => 'pending', 'gateway_id' => 2, 'created_at' => $fresh]),
];
$withRefund = $mk(['status' => 'failed']);
$tx2 = $db->insert('payment_transactions', ['donation_id' => $withRefund]);
$db->insert('payment_refunds', ['donation_id' => $withRefund]);
$protected['has refund record'] = $withRefund;
$withAdj = $mk(['status' => 'cancelled']);
$db->insert('financial_adjustments', ['donation_id' => $withAdj]);
$protected['has financial adjustment'] = $withAdj;
$withRecon = $mk(['status' => 'failed']);
$db->insert('reconciliation_items', ['donation_id' => $withRecon]);
$protected['in a reconciliation run'] = $withRecon;
$withCert = $mk(['status' => 'pending']);
$db->insert('donation_certificates', ['donation_id' => $withCert, 'status' => 'generated']);
$protected['certificate issued'] = $withCert;
foreach ($protected as $label => $id) {
    $r = $svc->deleteDonation($id, 7, $why);
    $ok($r['ok'] === false && $count('donations', "id = {$id}") === 1, "refused: {$label}");
}
$ok($count('payment_transactions', "donation_id = {$withRefund}") === 1, '  refused delete left the payment attempt untouched (rolled back / never started)');

// 3. Allowed states.
foreach (['failed', 'cancelled', 'abandoned', 'pending'] as $st) {
    $id = $mk(['status' => $st]);
    $ok($svc->deleteDonation($id, 7, $why)['ok'] === true, "allowed: {$st} offline entry");
}
$id = $mk(['status' => 'pending', 'gateway_id' => 2, 'created_at' => $old]);
$ok($svc->deleteDonation($id, 7, $why)['ok'] === true, 'allowed: online pending older than an hour (abandoned checkout)');
$ok($svc->deleteDonation(999999, 7, $why)['ok'] === false, 'unknown id is reported, not an error');

// 4. Bulk.
$a = $mk(['status' => 'failed']);
$b = $mk(['status' => 'completed']);
$c = $mk(['status' => 'abandoned']);
$res = $svc->bulk([$a, $b, $c, $a, 'x', -4], 7, fn (int $id, int $actor): array => $svc->deleteDonation($id, $actor, $why));
$ok($res['deleted'] === 2 && count($res['skipped']) === 1, 'bulk: deletes the junk ones, skips the completed one, ignores bad/duplicate ids');
$ok($count('donations', "id = {$b}") === 1, 'bulk: completed donation survived');

echo "In-kind donations\n";
$photoFile = $proofDir . '/photo-' . getmypid() . '.jpg';
file_put_contents($photoFile, 'x');
$ik = static function (string $status, ?string $photos = null) use ($db, $old): int {
    $id = $db->insert('inkind_donations', ['public_reference' => 'IKD-' . bin2hex(random_bytes(3)), 'donor_name' => 'Goods Donor', 'donor_email' => 'g@example.com', 'status' => $status, 'photos_json' => $photos, 'created_at' => $old]);
    $db->insert('inkind_donation_items', ['inkind_id' => $id]);
    $db->insert('inkind_events', ['inkind_id' => $id]);
    return $id;
};
$p = $ik('pending', json_encode(['private/test-proofs/' . basename($photoFile)]));
$ok($svc->deleteInKind($p, 7, $why)['ok'] === true, 'pending in-kind donation deleted');
$ok($count('inkind_donation_items', "inkind_id = {$p}") === 0 && $count('inkind_events', "inkind_id = {$p}") === 0, '  items and history removed');
$ok(!is_file($photoFile), '  photo file removed');
$ok($svc->deleteInKind($ik('rejected'), 7, $why)['ok'] === true, 'rejected in-kind donation deleted');
$v = $ik('verified');
$ok($svc->deleteInKind($v, 7, $why)['ok'] === false && $count('inkind_donations', "id = {$v}") === 1, 'verified in-kind donation refused');
$pc = $ik('pending');
$db->insert('inkind_certificates', ['inkind_id' => $pc, 'status' => 'generated']);
$ok($svc->deleteInKind($pc, 7, $why)['ok'] === false, 'in-kind donation with an issued certificate refused even if status was changed');
$ok($count('audit_logs', "action = 'inkind.deleted'") === 2, 'in-kind deletes are audited');

echo "Helpers\n";
$ok(RecordDeletionService::cleanIds(['3', 3, '0', '-1', 'abc', '7', [1]]) === [3, 7], 'cleanIds keeps only unique positive integers');
$ok(RecordDeletionService::cleanIds('nope') === [], 'cleanIds rejects non-arrays');
$ok(RecordDeletionService::quickDonationBlocker(['status' => 'failed', 'verification_status' => 'submitted']) === null, 'quick check allows a failed unverified donation');

@unlink($proofFile);
@unlink($photoFile);
@rmdir($proofDir);

echo "Reasons and the deleted-records log\n";
$log = $db->select('SELECT * FROM deleted_records ORDER BY id');
$ok(count($log) > 0 && $log[0]['record_type'] === 'donation' && $log[0]['reason'] === $why && (int) $log[0]['deleted_by'] === 7, 'a deleted donation is logged with its reason and who deleted it');
$ok($log[0]['reference'] !== null && str_contains((string) $log[0]['summary'], 'Test Donor'), '  the log keeps its reference and a short summary');
$ok($count('deleted_records', "record_type = 'inkind'") === 2, 'both deleted in-kind donations are logged');
$ok($count('deleted_records') === $count('audit_logs', "action IN ('donation.deleted','inkind.deleted')"), 'every deletion has exactly one log row and one audit record');
$ok(str_contains((string) $db->selectOne("SELECT safe_metadata_json FROM audit_logs WHERE action = 'donation.deleted'")['safe_metadata_json'], 'training'), 'the audit record carries the reason too');
$before = $count('donations');
$fresh1 = $mk(['status' => 'failed']);
foreach (['', '   ', 'abc', str_repeat('x', 501)] as $bad) {
    $r = $svc->deleteDonation($fresh1, 7, $bad);
    $ok($r['ok'] === false && $count('donations', "id = {$fresh1}") === 1, 'refused without a valid reason: "' . mb_substr($bad, 0, 8) . '" (' . mb_strlen($bad) . ' chars)');
}
$ik2 = $ik('pending');
$ok($svc->deleteInKind($ik2, 7, 'no')['ok'] === false && $count('inkind_donations', "id = {$ik2}") === 1, 'in-kind delete also needs a reason');
$ok($count('deleted_records') === $count('audit_logs', "action IN ('donation.deleted','inkind.deleted')"), 'refused deletions are not logged');
$svc->logDeleted('contact', 12, '#12', 'Contact messages entry #12', 'Spam', 7);
$last = $db->selectOne("SELECT * FROM deleted_records WHERE record_type = 'contact'");
$ok($last !== null && $last['summary'] === 'Contact messages entry #12' && $last['snapshot'] === null, 'form entries are logged by box and id only (no personal data)');
$ok(RecordDeletionService::cleanReason("  Duplicate entry  ") === 'Duplicate entry', 'cleanReason trims');

echo "\n{$checks} checks, {$failures} failure(s)\n";
exit($failures === 0 ? 0 : 1);
