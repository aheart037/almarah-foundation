<?php

declare(strict_types=1);

/**
 * Editing of offline donations and in-kind donations (App\Services\RecordEditService).
 *
 * Self-contained: runs against an in-memory SQLite database that mirrors the relevant tables.
 *
 *   php tests/record_edit_test.php
 */

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Core\Database;
use App\Core\Logger;
use App\Repositories\DonorRepository;
use App\Services\AuditService;
use App\Services\RecordEditService;
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
    'CREATE TABLE donors (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, name TEXT, email TEXT, phone TEXT)',
    'CREATE TABLE donation_purposes (id INTEGER PRIMARY KEY, name TEXT)',
    'CREATE TABLE bank_accounts (id INTEGER PRIMARY KEY, currency TEXT, status TEXT)',
    'CREATE TABLE donations (id INTEGER PRIMARY KEY, public_reference TEXT, donor_id INTEGER, gateway_id INTEGER, recorded_by INTEGER,
        amount_minor INTEGER, currency TEXT, status TEXT, verification_status TEXT, verified_by INTEGER, verified_at TEXT, receipt_number TEXT,
        source TEXT, payment_method TEXT, bank_account_id INTEGER, bank_reference TEXT, transaction_at TEXT, deposited_at TEXT, purpose_id INTEGER,
        donation_type TEXT, anonymous INTEGER DEFAULT 0, donor_message TEXT, payment_proof_path TEXT, acknowledgment_self INTEGER DEFAULT 1,
        recipient_name TEXT, recipient_phone TEXT, recipient_email TEXT, recipient_address TEXT, acknowledgment_instructions TEXT,
        referral_enabled INTEGER DEFAULT 0, referrer_name TEXT, referrer_phone TEXT, referral_notes TEXT, created_at TEXT)',
    'CREATE TABLE payment_refunds (id INTEGER PRIMARY KEY, donation_id INTEGER)',
    'CREATE TABLE financial_adjustments (id INTEGER PRIMARY KEY, donation_id INTEGER)',
    'CREATE TABLE reconciliation_items (id INTEGER PRIMARY KEY, donation_id INTEGER)',
    'CREATE TABLE donation_certificates (id INTEGER PRIMARY KEY, donation_id INTEGER, status TEXT)',
    'CREATE TABLE donation_review_events (id INTEGER PRIMARY KEY AUTOINCREMENT, donation_id INTEGER, actor_user_id INTEGER, stage_key TEXT, decision TEXT, comment TEXT)',
    'CREATE TABLE inkind_donations (id INTEGER PRIMARY KEY, public_reference TEXT, donor_id INTEGER, donor_name TEXT, donor_email TEXT, donor_phone TEXT,
        donor_city TEXT, donor_address TEXT, handover_method TEXT, handover_date TEXT, pickup_address TEXT, description TEXT, acknowledgment_self INTEGER,
        recipient_name TEXT, recipient_phone TEXT, recipient_email TEXT, recipient_address TEXT, acknowledgment_instructions TEXT,
        referral_enabled INTEGER, referrer_name TEXT, referrer_phone TEXT, referral_notes TEXT, status TEXT)',
    'CREATE TABLE inkind_donation_items (id INTEGER PRIMARY KEY AUTOINCREMENT, inkind_id INTEGER, category TEXT, item_name TEXT, quantity INTEGER, unit TEXT, details TEXT, sort_order INTEGER)',
    'CREATE TABLE inkind_events (id INTEGER PRIMARY KEY AUTOINCREMENT, inkind_id INTEGER, action TEXT, actor_user_id INTEGER, note TEXT)',
    'CREATE TABLE inkind_certificates (id INTEGER PRIMARY KEY, inkind_id INTEGER, status TEXT)',
    'CREATE TABLE audit_logs (id INTEGER PRIMARY KEY, user_id INTEGER, action TEXT, entity_type TEXT, entity_id TEXT, safe_metadata_json TEXT,
        ip_address TEXT, user_agent TEXT, created_at TEXT DEFAULT CURRENT_TIMESTAMP)',
] as $ddl) {
    $pdo->exec($ddl);
}
$db->insert('bank_accounts', ['id' => 1, 'currency' => 'PKR', 'status' => 'published']);
$db->insert('bank_accounts', ['id' => 2, 'currency' => 'USD', 'status' => 'published']);
$db->insert('donation_purposes', ['id' => 3, 'name' => 'Education']);
$db->insert('donors', ['name' => 'Ali Khan', 'email' => 'ali@example.org', 'phone' => '+923001112222']);

$tmpLogs = sys_get_temp_dir() . '/redit-logs-' . getmypid();
@mkdir($tmpLogs, 0777, true);
$logger = new Logger($tmpLogs);
$svc = new RecordEditService($db, new DonorRepository($db), new UploadService($logger, dirname(__DIR__) . '/uploads'), new AuditService($db, $logger));

$count = static fn (string $t, string $w = '1=1'): int => $db->int("SELECT COUNT(*) FROM {$t} WHERE {$w}");
$row = static fn (int $id): array => $db->selectOne('SELECT * FROM donations WHERE id = :id', ['id' => $id]);
$mk = static fn (array $o = []): int => $db->insert('donations', $o + [
    'public_reference' => 'ALM-' . bin2hex(random_bytes(3)), 'donor_id' => 1, 'gateway_id' => null, 'recorded_by' => 5,
    'amount_minor' => 500000, 'currency' => 'PKR', 'status' => 'pending', 'verification_status' => 'submitted', 'source' => 'bank_transfer',
    'payment_method' => 'bank_transfer', 'bank_account_id' => 1, 'bank_reference' => 'FT100', 'transaction_at' => '2026-11-01 12:00:00',
    'donation_type' => 'one_time', 'acknowledgment_self' => 1, 'created_at' => '2026-11-01 12:00:00',
]);
$form = static fn (array $o = []): array => $o + [
    'amount' => '5000.00', 'currency' => 'PKR', 'source' => 'bank_transfer', 'received_at' => '2026-11-01', 'bank_reference' => 'FT100',
    'bank_account_id' => '1', 'purpose_id' => '', 'donation_type' => 'one_time', 'donor_name' => 'Ali Khan', 'donor_email' => 'ali@example.org',
    'donor_phone' => '+923001112222', 'note' => '', 'acknowledgment_self' => '1', 'referral_enabled' => '0',
    'reason' => 'Corrected after checking the deposit slip',
];
$fails = static function (callable $f): ?string {
    try {
        $f();
        return null;
    } catch (InvalidArgumentException $e) {
        return $e->getMessage();
    }
};

echo "Offline donations - who can be edited\n";
$ok(RecordEditService::donationEditability(['gateway_id' => 2, 'recorded_by' => 5, 'status' => 'completed'])['mode'] === 'blocked', 'online (gateway) payment is blocked');
$ok(RecordEditService::donationEditability(['gateway_id' => null, 'recorded_by' => null, 'status' => 'pending'])['mode'] === 'blocked', 'donor-reported transfer is blocked');
foreach (['failed', 'cancelled', 'abandoned', 'processing', 'refunded'] as $st) {
    $ok(RecordEditService::donationEditability(['recorded_by' => 5, 'status' => $st])['mode'] === 'blocked', "status {$st} is blocked");
}
$ok(RecordEditService::donationEditability(['recorded_by' => 5, 'status' => 'pending', 'verification_status' => 'submitted'])['mode'] === 'full', 'pending + submitted gets full edit');
$ok(RecordEditService::donationEditability(['recorded_by' => 5, 'status' => 'pending', 'verification_status' => 'rejected'])['mode'] === 'full', 'rejected gets full edit');
$ok(RecordEditService::donationEditability(['recorded_by' => 5, 'status' => 'completed', 'verification_status' => 'verified'])['mode'] === 'contact', 'verified + completed is contact-only');
$ok(RecordEditService::donationEditability(['recorded_by' => 5, 'status' => 'pending', 'verification_status' => 'legacy'])['mode'] === 'contact', 'legacy record is contact-only');
$ok(RecordEditService::donationEditability(['recorded_by' => 5, 'status' => 'pending', 'verification_status' => 'submitted', 'receipt_number' => 'R-1'])['mode'] === 'contact', 'receipted is contact-only');
foreach (['payment_refunds' => 'refund', 'financial_adjustments' => 'adjustment', 'reconciliation_items' => 'reconciliation'] as $table => $what) {
    $id = $mk();
    $db->insert($table, ['donation_id' => $id]);
    $ok($svc->donationAccess($row($id))['mode'] === 'contact', "a {$what} record downgrades to contact-only");
}
$id = $mk();
$db->insert('donation_certificates', ['donation_id' => $id, 'status' => 'generated']);
$ok($svc->donationAccess($row($id))['mode'] === 'contact', 'an issued certificate downgrades to contact-only');
$id = $mk();
$db->insert('donation_certificates', ['donation_id' => $id, 'status' => 'failed']);
$ok($svc->donationAccess($row($id))['mode'] === 'full', 'a failed certificate does not');

echo "Offline donations - full edit\n";
$id = $mk();
$r = $svc->updateDonation($id, $form(['amount' => '7500.50', 'bank_reference' => 'FT200', 'purpose_id' => '3', 'note' => 'fixed typo']), null, 9);
$d = $row($id);
$ok($r['changed'] !== [] && $r['review_restarted'] === false, 'changes are saved and reported');
$ok((int) $d['amount_minor'] === 750050 && $d['bank_reference'] === 'FT200' && (int) $d['purpose_id'] === 3 && $d['donor_message'] === 'fixed typo', '  amount, reference, purpose and note updated');
$ok($d['transaction_at'] === '2026-11-01 12:00:00', '  unchanged date keeps its stored time');
$a = $db->selectOne("SELECT * FROM audit_logs WHERE action = 'donation.edited'");
$ok($a !== null && (int) $a['user_id'] === 9 && (int) $a['entity_id'] === $id, '  audit record names the editor');
$ok(str_contains((string) $a['safe_metadata_json'], '750050') && str_contains((string) $a['safe_metadata_json'], '500000'), '  audit record holds old and new amount');
$ok($count('donation_review_events', "donation_id = {$id} AND decision = 'edited'") === 1, '  an "edited" review event was added');

$r = $svc->updateDonation($id, $form(['amount' => '7500.50', 'bank_reference' => 'FT200', 'purpose_id' => '3', 'note' => 'fixed typo']), null, 9);
$ok($r['changed'] === [] && $count('audit_logs', "action = 'donation.edited'") === 1, 'saving with no change writes nothing');

$id = $mk(['verification_status' => 'initial_review', 'verified_by' => 3]);
$r = $svc->updateDonation($id, $form(['amount' => '6000']), null, 9);
$d = $row($id);
$ok($r['review_restarted'] === true && $d['verification_status'] === 'submitted' && $d['verified_by'] === null, 'editing a part-reviewed donation restarts review');
$ok($count('donation_review_events', "donation_id = {$id} AND decision = 'reset'") === 1, '  with a "reset" event (so earlier approvals no longer block reviewers)');

$id = $mk(['verification_status' => 'rejected']);
$svc->updateDonation($id, $form(['amount' => '5100']), null, 9);
$ok($row($id)['verification_status'] === 'submitted', 'editing a rejected donation sends it back for review');

echo "Offline donations - validation\n";
$id = $mk();
$before = $row($id);
$same = static fn (int $id) => $db->selectOne('SELECT * FROM donations WHERE id = :id', ['id' => $id]) == $before;
$bad = [
    'amount with letters'      => $form(['amount' => '12abc']),
    'amount too small'         => $form(['amount' => '0.50']),
    'future date'              => $form(['received_at' => gmdate('Y-m-d', time() + 86400 * 3)]),
    'unknown currency'         => $form(['currency' => 'XYZ']),
    'bank currency mismatch'   => $form(['bank_account_id' => '2']),
    'unknown bank'             => $form(['bank_account_id' => '99']),
    'unknown purpose'          => $form(['purpose_id' => '77']),
    'bad payment method'       => $form(['source' => 'bitcoin']),
    'missing phone'            => $form(['donor_phone' => '']),
    'bad email'                => $form(['donor_email' => 'nope']),
    'cash without deposit'     => $form(['source' => 'cash']),
    'recipient name missing'   => $form(['acknowledgment_self' => '0', 'recipient_name' => '']),
];
foreach ($bad as $label => $in) {
    $ok($fails(fn () => $svc->updateDonation($id, $in, null, 9)) !== null, "rejected: {$label}");
}
$ok($same($id), 'a rejected edit changes nothing');
$ok($fails(fn () => $svc->updateDonation($id, $form(['source' => 'cash', 'deposited_at' => '2026-11-02', 'bank_reference' => 'SLIP-9']), null, 9)) === null, 'cash with deposit date and slip number is accepted');
$ok($fails(fn () => $svc->updateDonation(99999, $form(), null, 9)) !== null, 'unknown donation is refused');

echo "Offline donations - anonymous and donor link\n";
$id = $mk();
$svc->updateDonation($id, $form(['anonymous' => '1', 'donor_name' => '', 'donor_email' => '', 'donor_phone' => '']), null, 9);
$d = $row($id);
$ok($d['donor_id'] === null && (int) $d['anonymous'] === 1, 'anonymous gift with no email has no donor record');
$svc->updateDonation($id, $form(['donor_name' => 'Sara Noor', 'donor_email' => 'sara@example.org', 'donor_phone' => '+923331234567']), null, 9);
$d = $row($id);
$donor = $db->selectOne('SELECT * FROM donors WHERE id = :id', ['id' => $d['donor_id']]);
$ok($donor !== null && $donor['email'] === 'sara@example.org' && (int) $d['anonymous'] === 0, 'a donor record is created and linked when an email is added');
$ok($count('donors') === 2 && $db->selectOne('SELECT name FROM donors WHERE id = 1')['name'] === 'Ali Khan', 'the other donor is untouched');

echo "Offline donations - contact-only (verified)\n";
$id = $mk(['status' => 'completed', 'verification_status' => 'verified', 'amount_minor' => 100000]);
$r = $svc->updateDonation($id, $form(['amount' => '999999', 'bank_reference' => 'HACK', 'currency' => 'USD', 'donor_phone' => '+923009998888', 'note' => 'new phone']), null, 9);
$d = $row($id);
$ok($r['mode'] === 'contact' && (int) $d['amount_minor'] === 100000 && $d['bank_reference'] === 'FT100' && $d['currency'] === 'PKR', 'money fields posted to a verified donation are ignored');
$ok($d['donor_message'] === 'new phone' && $db->selectOne('SELECT phone FROM donors WHERE id = 1')['phone'] === '+923009998888', 'phone and note are corrected');
$ok($d['verification_status'] === 'verified' && $count('donation_review_events', "donation_id = {$id} AND decision = 'reset'") === 0, 'verification is left as it was (no review reset)');
$ok($fails(fn () => $svc->updateDonation($id, $form(['donor_email' => '']), null, 9)) !== null, 'email cannot be removed from a verified donation');
$ok($fails(fn () => $svc->updateDonation($id, $form(['donor_email' => 'bad']), null, 9)) !== null, 'invalid email is refused');

echo "Offline donations - blocked records stay untouched\n";
$gw = $mk(['gateway_id' => 4, 'recorded_by' => null, 'status' => 'completed']);
$ok($fails(fn () => $svc->updateDonation($gw, $form(['amount' => '1']), null, 9)) !== null && (int) $row($gw)['amount_minor'] === 500000, 'online payment refuses edit');
$rf = $mk(['status' => 'refunded']);
$ok($fails(fn () => $svc->updateDonation($rf, $form(), null, 9)) !== null, 'refunded donation refuses edit');

echo "In-kind donations\n";
$ik = $db->insert('inkind_donations', ['public_reference' => 'IKD-1', 'donor_id' => 1, 'donor_name' => 'Ali Khan', 'donor_email' => 'ali@example.org', 'donor_phone' => '+923009998888',
    'handover_method' => 'drop_off', 'acknowledgment_self' => 1, 'referral_enabled' => 0, 'status' => 'pending']);
$db->insert('inkind_donation_items', ['inkind_id' => $ik, 'category' => 'clothing', 'item_name' => 'Jackets', 'quantity' => 10, 'sort_order' => 0]);
$ikForm = static fn (array $o = []): array => $o + [
    'donor_name' => 'Ali Khan', 'donor_email' => 'ali@example.org', 'donor_phone' => '+923009998888', 'handover_method' => 'drop_off',
    'acknowledgment_self' => '1', 'referral_enabled' => '0', 'reason' => 'Donor called with the right quantities',
    'item_category' => ['clothing'], 'item_name' => ['Jackets'], 'item_qty' => ['10'], 'item_unit' => [''], 'item_details' => [''],
];
$r = $svc->updateInKind($ik, $ikForm(['item_name' => ['Jackets', 'Shoes', ''], 'item_category' => ['clothing', 'footwear', 'other'], 'item_qty' => ['12', '30', ''], 'item_unit' => ['pcs', 'pairs', ''], 'item_details' => ['', 'size 8', ''], 'description' => 'Packed']), 7);
$items = $db->select('SELECT * FROM inkind_donation_items WHERE inkind_id = :i ORDER BY sort_order', ['i' => $ik]);
$ok(in_array('items', $r['changed'], true) && count($items) === 2 && (int) $items[0]['quantity'] === 12 && $items[1]['item_name'] === 'Shoes' && $items[1]['category'] === 'footwear', 'items are replaced (blank rows ignored)');
$ok($db->selectOne('SELECT description FROM inkind_donations WHERE id = :i', ['i' => $ik])['description'] === 'Packed', 'description updated');
$ok($count('inkind_events', "inkind_id = {$ik} AND action = 'edited'") === 1 && $count('audit_logs', "action = 'inkind.edited'") === 1, 'history event and audit record written');
$ok($db->selectOne('SELECT status FROM inkind_donations WHERE id = :i', ['i' => $ik])['status'] === 'pending', 'status is not changed by an edit');
$r = $svc->updateInKind($ik, $ikForm(['item_name' => ['Jackets', 'Shoes'], 'item_category' => ['clothing', 'footwear'], 'item_qty' => ['12', '30'], 'item_unit' => ['pcs', 'pairs'], 'item_details' => ['', 'size 8'], 'description' => 'Packed']), 7);
$ok($r['changed'] === [] && $count('inkind_events', "inkind_id = {$ik} AND action = 'edited'") === 1, 'no change writes nothing');
foreach ([
    'no items'        => $ikForm(['item_name' => [''], 'item_qty' => ['']]),
    'zero quantity'   => $ikForm(['item_qty' => ['0']]),
    'text quantity'   => $ikForm(['item_qty' => ['ten']]),
    'too many items'  => $ikForm(['item_name' => array_fill(0, 16, 'x'), 'item_qty' => array_fill(0, 16, '1'), 'item_category' => array_fill(0, 16, 'other')]),
    'bad email'       => $ikForm(['donor_email' => 'x']),
    'pickup no addr'  => $ikForm(['handover_method' => 'pickup']),
    'bad date'        => $ikForm(['handover_date' => '2026-13-45']),
    'no recipient'    => $ikForm(['acknowledgment_self' => '0']),
] as $label => $in) {
    $ok($fails(fn () => $svc->updateInKind($ik, $in, 7)) !== null, "rejected: {$label}");
}
$ok($count('inkind_donation_items', "inkind_id = {$ik}") === 2, 'rejected edits leave the items alone');
$db->update('inkind_donations', ['status' => 'verified'], 'id = :i', ['i' => $ik]);
$db->insert('inkind_certificates', ['inkind_id' => $ik, 'status' => 'generated']);
$r = $svc->updateInKind($ik, $ikForm(['donor_name' => 'Ali Raza Khan', 'item_name' => ['Jackets', 'Shoes'], 'item_category' => ['clothing', 'footwear'], 'item_qty' => ['12', '30'], 'item_unit' => ['pcs', 'pairs'], 'item_details' => ['', 'size 8'], 'description' => 'Packed']), 7);
$ok($r['certificate_stale'] === true && $db->selectOne('SELECT status FROM inkind_donations WHERE id = :i', ['i' => $ik])['status'] === 'verified', 'a verified donation can be edited and flags the issued certificate as stale');
$ok($fails(fn () => $svc->updateInKind(9999, $ikForm(), 7)) !== null, 'unknown in-kind donation is refused');


echo "Reasons for edits\n";
$id = $mk();
$b = $row($id);
foreach (['' => 'empty', 'abc' => 'too short', str_repeat('x', 501) => 'too long'] as $rs => $why) {
    $ok($fails(fn () => $svc->updateDonation($id, $form(['amount' => '8123', 'reason' => $rs]), null, 9)) !== null && $row($id) == $b, "donation edit refused: reason {$why}, nothing saved");
}
$ok($fails(fn () => $svc->updateDonation($id, $form(['amount' => '8123']), null, 9)) === null, 'accepted with a reason');
$ev = $db->selectOne("SELECT comment FROM donation_review_events WHERE donation_id = {$id} ORDER BY id DESC");
$ok(str_contains((string) $ev['comment'], 'Reason: Corrected after checking the deposit slip'), 'the reason is in the review history');
$ok(str_contains((string) $db->selectOne("SELECT safe_metadata_json FROM audit_logs WHERE action = 'donation.edited' ORDER BY id DESC")['safe_metadata_json'], 'deposit slip'), 'the reason is in the audit record');
$ok($fails(fn () => $svc->updateDonation($id, $form(['amount' => '8123', 'reason' => '']), null, 9)) === null, 'a save with no change needs no reason');
$vid = $mk(['status' => 'completed', 'verification_status' => 'verified']);
$ok($fails(fn () => $svc->updateDonation($vid, $form(['donor_phone' => '+923000000001', 'reason' => '']), null, 9)) !== null, 'contact-only edit also needs a reason');
$ok($fails(fn () => $svc->updateDonation($vid, $form(['donor_phone' => '+923000000001', 'reason' => 'Donor changed number']), null, 9)) === null
    && str_contains((string) $db->selectOne("SELECT comment FROM donation_review_events WHERE donation_id = {$vid} ORDER BY id DESC")['comment'], 'Donor changed number'), 'contact-only edit logs its reason in the history');
$ik3 = $db->insert('inkind_donations', ['public_reference' => 'IKD-3', 'donor_name' => 'X', 'donor_email' => 'x@example.org', 'donor_phone' => '1', 'handover_method' => 'drop_off', 'acknowledgment_self' => 1, 'referral_enabled' => 0, 'status' => 'pending']);
$ok($fails(fn () => $svc->updateInKind($ik3, $ikForm(['reason' => '']), 7)) !== null, 'in-kind edit refused without a reason');
$svc->updateInKind($ik3, $ikForm(['reason' => 'Fixing the donor name']), 7);
$ok(str_contains((string) $db->selectOne("SELECT note FROM inkind_events WHERE inkind_id = {$ik3} AND action = 'edited'")['note'], 'Fixing the donor name'), 'in-kind edit reason is in the history');

echo "\n{$checks} checks, {$failures} failure(s)\n";
exit($failures === 0 ? 0 : 1);
