<?php

declare(strict_types=1);

/**
 * Donation management Part 1: validation, finance verification, ledger totals, access control.
 * Destructive: needs a disposable DB whose name contains "test".
 *   TEST_DB_NAME=almarah_test php tests/donation_management_part1_test.php
 */
require __DIR__ . '/bootstrap.php';

use App\Core\Csrf;
use App\Core\Database;
use App\Core\Request;
use App\Core\Router;
use App\Core\Session;
use App\Exceptions\HttpException;
use App\Services\DonationLedgerService;
use App\Services\DonationReviewService;
use App\Services\UploadService;
use Tests\Reports\Fixture;

$name = (string) getenv('TEST_DB_NAME');
if ($name === '' || !str_contains($name, 'test')) { throw new RuntimeException('Set disposable TEST_DB_NAME containing test.'); }
putenv('DB_HOST=' . (getenv('TEST_DB_HOST') ?: '127.0.0.1'));
putenv('DB_DATABASE=' . $name);
putenv('DB_USERNAME=' . (getenv('TEST_DB_USER') ?: 'alm'));
putenv('DB_PASSWORD=' . (getenv('TEST_DB_PASS') ?: 'almpass'));
$db = Fixture::connect();
Fixture::migrate($db);
Fixture::seed($db);
$app = require __DIR__ . '/../bootstrap/app.php';
$database = $app->make(Database::class);
$review = $app->make(DonationReviewService::class);
$ledger = $app->make(DonationLedgerService::class);
$uploads = $app->make(UploadService::class);
$app->make(Session::class)->start();
$router = $app->make(Router::class);
$n = 0;
function ok(bool $c, string $label): void { global $n; if (!$c) throw new RuntimeException('FAIL ' . $label); $n++; }
function throws(callable $fn, string $needle = ''): bool { try { $fn(); } catch (InvalidArgumentException | RuntimeException $e) { return $needle === '' || stripos($e->getMessage(), $needle) !== false; } return false; }

// --- fixtures ---------------------------------------------------------------
// Start from an empty donation ledger (disposable DB): drop the shared fixture's donations.
$database->pdo()->exec('SET FOREIGN_KEY_CHECKS = 0');
foreach (['payment_refunds', 'payment_callbacks', 'payment_transactions', 'financial_adjustments', 'reconciliation_items', 'donation_review_events', 'donations'] as $t) {
    $database->pdo()->exec("TRUNCATE TABLE `$t`");
}
$database->pdo()->exec('SET FOREIGN_KEY_CHECKS = 1');
$database->insert('roles', ['id' => 10, 'name' => 'finance_manager', 'label' => 'Finance']);
foreach ([[11, 'staff@example.org'], [12, 'finance@example.org'], [13, 'finance2@example.org']] as [$id, $mail]) {
    $database->insert('users', ['id' => $id, 'first_name' => 'U' . $id, 'last_name' => 'T', 'email' => $mail, 'password_hash' => 'x', 'status' => 'active']);
}
$database->insert('bank_accounts', ['id' => 1, 'bank_name' => 'Meezan', 'account_title' => 'Almarah', 'account_number' => '0001', 'currency' => 'PKR', 'status' => 'published']);
$database->insert('bank_accounts', ['id' => 2, 'bank_name' => 'Barclays', 'account_title' => 'Almarah', 'account_number' => '0002', 'currency' => 'USD', 'status' => 'published']);
$database->insert('bank_accounts', ['id' => 3, 'bank_name' => 'Hidden', 'account_title' => 'Almarah', 'account_number' => '0003', 'currency' => 'PKR', 'status' => 'hidden']);
$database->insert('donors', ['name' => 'Dora Donor', 'email' => 'dora@example.org', 'phone' => '0300']);
$donorId = (int) $database->scalar("SELECT id FROM donors WHERE email='dora@example.org'");
function mk(Database $d, array $o): int {
    static $i = 0; $i++;
    $d->insert('donations', $o + ['public_reference' => 'ALM-T-' . str_pad((string) $i, 4, '0', STR_PAD_LEFT), 'donor_id' => $GLOBALS['donorId'],
        'amount_minor' => 100000, 'currency' => 'PKR', 'gateway_id' => null, 'status' => 'pending', 'source' => 'bank_transfer', 'verification_status' => 'submitted', 'bank_account_id' => 1]);
    return (int) $d->scalar('SELECT MAX(id) FROM donations');
}
$offline = mk($database, ['recorded_by' => 11, 'amount_minor' => 500000, 'bank_reference' => 'FT-1', 'payment_proof_path' => 'private/payment-proofs/x.png']);
$online = mk($database, ['amount_minor' => 200000, 'bank_reference' => 'FT-1']);                 // same bank ref as $offline -> duplicate warning
$cash = mk($database, ['recorded_by' => 11, 'source' => 'cash', 'amount_minor' => 300000, 'deposited_at' => gmdate('Y-m-d')]);
$usd = mk($database, ['recorded_by' => 11, 'currency' => 'USD', 'bank_account_id' => 2, 'amount_minor' => 10000]);
$legacy = mk($database, ['recorded_by' => 11, 'verification_status' => 'legacy', 'status' => 'completed', 'amount_minor' => 999999]);
$gwDone = mk($database, ['gateway_id' => 1, 'bank_account_id' => null, 'verification_status' => 'legacy', 'status' => 'completed', 'amount_minor' => 700000]);
$gwOpen = mk($database, ['gateway_id' => 1, 'bank_account_id' => null, 'verification_status' => 'legacy', 'status' => 'pending', 'amount_minor' => 800000]);

// --- 1. server-side validation (runs before any file is touched) -------------------------------
$base = ['donor_name' => 'Dora', 'donor_email' => 'dora@example.org', 'donor_phone' => '0300', 'amount' => '1000', 'received_at' => gmdate('Y-m-d'), 'currency' => 'PKR', 'source' => 'bank_transfer', 'bank_account_id' => '1'];
ok(throws(fn () => $review->submit(array_diff_key($base, ['bank_account_id' => 1])), 'bank account'), 'bank account is required');
ok(throws(fn () => $review->submit(['bank_account_id' => '3'] + $base), 'bank account'), 'hidden bank account rejected');
ok(throws(fn () => $review->submit(['bank_account_id' => '2'] + $base), 'does not accept'), 'bank/currency mismatch rejected');
ok(throws(fn () => $review->submit(['source' => 'cash'] + $base), 'staff'), 'public donors cannot submit cash');
ok(throws(fn () => $review->submit(['source' => 'cash'] + $base, null, 11), 'deposit date'), 'cash needs deposit date');
ok(throws(fn () => $review->submit(['source' => 'cash', 'deposited_at' => gmdate('Y-m-d')] + $base, null, 11), 'slip number'), 'cash needs deposit slip number');
ok(throws(fn () => $review->submit(['source' => 'cash', 'deposited_at' => gmdate('Y-m-d'), 'bank_reference' => 'DS-1'] + $base, null, 11), 'Upload the bank'), 'valid cash still needs a slip file');
ok(throws(fn () => $review->submit($base, null), 'Upload the bank'), 'slip is mandatory for online donations');
ok(throws(fn () => $review->submit(['amount' => '0'] + $base), 'amount'), 'amount validated');
ok($database->int('SELECT COUNT(*) FROM donations') === 7, 'rejected submissions create no rows');

// --- 2. slip upload hardening ---------------------------------------------------------------
$tmp = tempnam(sys_get_temp_dir(), 'slip'); file_put_contents($tmp, str_repeat('A', 10));
ok(throws(fn () => $uploads->storePaymentProof(['name' => 'a.png', 'tmp_name' => $tmp, 'size' => 3 * 1024 * 1024, 'error' => UPLOAD_ERR_OK]), '2 MB'), 'slip over 2 MB rejected');
ok(throws(fn () => $uploads->storePaymentProof(['name' => 'a.png', 'tmp_name' => $tmp, 'size' => 10, 'error' => UPLOAD_ERR_OK])), 'non-uploaded file rejected');
ok(throws(fn () => $uploads->storePaymentProof(['name' => 'a.pdf', 'tmp_name' => $tmp, 'size' => 10, 'error' => UPLOAD_ERR_OK])), 'PDF slip rejected');
unlink($tmp);

// --- 3. finance verification ----------------------------------------------------------------
$fin = ['finance_manager'];
ok(throws(fn () => $review->decide($offline, 12, $fin, 'reject', ''), 'reason'), 'rejection reason is mandatory');
ok(throws(fn () => $review->decide($offline, 12, $fin, 'reject', 'abc'), 'reason'), 'rejection reason needs 5+ chars');
ok(throws(fn () => $review->decide($offline, 12, ['admin'], 'approve', ''), 'role'), 'non-finance role cannot verify');
ok(throws(fn () => $review->decide($offline, 11, $fin, 'approve', ''), 'own donation'), 'recorder cannot verify own entry');
ok(throws(fn () => $review->decide($gwDone, 12, $fin, 'approve', ''), 'Gateway'), 'gateway donations are not reviewed manually');
ok(count($review->possibleDuplicates($database->selectOne('SELECT * FROM donations WHERE id=:i', ['i' => $online]))) === 1, 'duplicate reference warns reviewer');
ok($review->decide($offline, 12, $fin, 'approve', 'Matches statement') === true, 'finance verifies');
$d = $database->selectOne('SELECT * FROM donations WHERE id=:i', ['i' => $offline]);
ok($d['verification_status'] === 'verified' && $d['status'] === 'completed' && (int) $d['verified_by'] === 12 && $d['verified_at'] !== null, 'verified fields set');
ok($d['certificate_status'] === 'not_issued', 'no certificate issued in Part 1');
ok(throws(fn () => $review->decide($offline, 13, $fin, 'approve', ''), 'not awaiting'), 'duplicate verification blocked');
ok(throws(fn () => $review->decide($offline, 13, $fin, 'reject', 'changed my mind'), 'not awaiting'), 'cannot reject after verification');
ok($database->int("SELECT COUNT(*) FROM audit_logs WHERE entity_type='donation' AND entity_id=:i AND action='donation.review.approve'", ['i' => (string) $offline]) === 1, 'audit log written');
ok(count($review->history($offline)) === 1 && $review->history($offline)[0]['actor_email'] === 'finance@example.org', 'verification history kept');

ok($review->decide($online, 12, $fin, 'reject', 'Slip amount does not match') === false, 'finance rejects with reason');
$r = $database->selectOne('SELECT * FROM donations WHERE id=:i', ['i' => $online]);
ok($r['verification_status'] === 'rejected' && $r['status'] === 'pending' && $r['verified_at'] === null, 'rejected stays out of completed');
ok($review->lastRejection($r['public_reference']) === 'Slip amount does not match', 'donor sees rejection reason');
ok(throws(fn () => $review->resubmit($r['public_reference'], 'wrong@example.org', $base + ['bank_reference' => 'x'], null), 'could not find'), 'resubmit needs matching email');
ok(throws(fn () => $review->resubmit('ALM-T-0001', 'dora@example.org', $base, null), 'could not find'), 'only rejected donor reports can be resubmitted');
ok(throws(fn () => $review->resubmit($r['public_reference'], 'dora@example.org', $base, null), 'Upload the bank'), 'resubmission needs a new slip');
ok($database->selectOne('SELECT verification_status FROM donations WHERE id=:i', ['i' => $online])['verification_status'] === 'rejected', 'failed resubmit leaves donation unchanged');
ok($review->decide($legacy, 12, $fin, 'approve', '') === true, 'legacy offline entries can be reviewed');
ok($review->decide($usd, 13, $fin, 'approve', 'USD ok') === true, 'second finance user verifies another donation');

// --- 4. ledger ------------------------------------------------------------------------------
$all = $ledger->search([], 1);
ok($all['total'] === 7, 'ledger lists every record (nothing hidden)');
ok(($all['confirmed']['PKR'] ?? 0) === 500000 + 999999 + 700000, 'confirmed PKR = verified offline + gateway completed only');
ok(($all['confirmed']['USD'] ?? 0) === 10000, 'confirmed totals are per currency');
ok(($all['pending']['PKR'] ?? 0) === 300000, 'pending (cash) shown separately, not confirmed');
$by = array_column($all['rows'], 'ledger_status', 'id');
ok($by[$online] === 'rejected' && $by[$cash] === 'pending_verification' && $by[$gwOpen] === 'gateway_unconfirmed' && $by[$gwDone] === 'verified', 'ledger statuses');
ok($ledger->search(['channel' => 'offline'], 1)['total'] === 4 && $ledger->search(['channel' => 'online'], 1)['total'] === 3, 'online/offline filter');
ok($ledger->search(['bank_account_id' => 2], 1)['total'] === 1, 'bank filter');
ok($ledger->search(['status' => 'pending_verification'], 1)['total'] === 1, 'status filter');
ok($ledger->search(['q' => 'ALM-T-0001'], 1)['total'] === 1 && $ledger->search(['q' => "' OR 1=1 --"], 1)['total'] === 0, 'search is parameterised');
ok($database->int('SELECT COUNT(*) FROM donation_review_events') >= 4, 'review events persisted');

// --- 5. access control / CSRF / secure slip route -------------------------------------------
function hit(Router $r, $app, string $path, string $method = 'GET', array $data = []): int {
    try { return $r->dispatch(new Request($method, $path, $method === 'GET' ? $data : [], $method === 'POST' ? $data : [], [], []), $app)->status(); }
    catch (HttpException $e) { return $e->statusCode(); }
}
foreach (['/admin/ledger', '/admin/donations/' . $offline . '/proof', '/admin/finance/offline'] as $p) {
    $s = hit($router, $app, $p);
    ok(in_array($s, [301, 302, 401, 403], true), "guests blocked from $p (got $s)");
}
$s = hit($router, $app, '/admin/donations/' . $cash . '/review', 'POST', ['decision' => 'approve']);
ok(in_array($s, [301, 302, 401, 403, 419], true), "guest cannot verify (got $s)");
ok(hit($router, $app, '/donate/transfer') === 200, 'public transfer form renders');

// --- 6. migration is idempotent and permissions exist ---------------------------------------
$sql = (string) file_get_contents(__DIR__ . '/../database/migrations/0019_donation_management_part1.sql');
$database->pdo()->exec($sql); $database->pdo()->exec($sql);
ok($database->int("SELECT COUNT(*) FROM permissions WHERE name IN ('donations.record','donations.verify')") === 2, 'migration 0019 re-runs safely; permissions present');

echo "$n donation-management Part 1 checks passed.\n";
