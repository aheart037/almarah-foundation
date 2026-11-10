<?php

declare(strict_types=1);

/**
 * Donation management Part 2: certificates, PDF, email delivery/retry, duplicate prevention, access control.
 * Destructive: needs a disposable DB whose name contains "test".
 *   TEST_DB_NAME=almarah_test php tests/donation_certificates_part2_test.php
 */
require __DIR__ . '/bootstrap.php';

use App\Core\Database;
use App\Core\Request;
use App\Core\Router;
use App\Core\Session;
use App\Exceptions\HttpException;
use App\Mail\MailQueue;
use App\Mail\TransportInterface;
use App\Services\DonationCertificateService;
use App\Services\DonationReviewService;
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
$app->make(Session::class)->start();
$n = 0;
function ok(bool $c, string $label): void { global $n; if (!$c) throw new RuntimeException('FAIL ' . $label); $n++; }

/** Capturing transport; $fail switches simulated SMTP outage. */
final class FakeTransport implements TransportInterface
{
    public static bool $fail = false;
    public static array $sent = [];
    public function send(string $toEmail, string $toName, string $subject, string $htmlBody, string $textBody, array $attachments = []): bool
    {
        if (self::$fail) { throw new RuntimeException('SMTP down'); }
        self::$sent[] = ['to' => $toEmail, 'subject' => $subject, 'html' => $htmlBody, 'attachments' => array_map(static fn ($a) => ['name' => $a['name'], 'bytes' => (string) file_get_contents($a['path'])], $attachments)];
        return true;
    }
    public function testConnection(): bool { return true; }
    public function describe(): string { return 'fake'; }
}
$app->bind(TransportInterface::class, static fn () => new FakeTransport());

$pdo = $database->pdo();
$pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
foreach (['donation_certificates', 'email_jobs', 'donation_review_events', 'donations'] as $t) { $pdo->exec("TRUNCATE TABLE `$t`"); }
$pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
@array_map('unlink', glob(__DIR__ . '/../storage/private/certificates/*.pdf') ?: []);

$database->insert('roles', ['id' => 10, 'name' => 'finance_manager', 'label' => 'Finance']);
$database->insert('roles', ['id' => 11, 'name' => 'donation_manager', 'label' => 'Donation manager']);
foreach ([[21, 'rec@example.org'], [22, 'fin@example.org'], [23, 'plain@example.org']] as [$id, $mail]) {
    $database->insert('users', ['id' => $id, 'first_name' => 'U' . $id, 'last_name' => 'T', 'email' => $mail, 'password_hash' => 'x', 'status' => 'active']);
}
$database->run("INSERT IGNORE INTO permissions (name, group_name, label) VALUES ('donations.view', 'donations', 'View donations')");
$perm = static fn (string $p): int => (int) $database->scalar('SELECT id FROM permissions WHERE name = :n', ['n' => $p]);
foreach (['donations.view', 'donations.verify'] as $p) { $database->insert('role_permissions', ['role_id' => 10, 'permission_id' => $perm($p)]); }
$database->insert('role_permissions', ['role_id' => 11, 'permission_id' => $perm('donations.view')]);
$database->insert('user_roles', ['user_id' => 22, 'role_id' => 10]);
$database->insert('user_roles', ['user_id' => 21, 'role_id' => 11]);
$database->insert('bank_accounts', ['id' => 1, 'bank_name' => 'Meezan', 'account_title' => 'Almarah', 'account_number' => '0001', 'currency' => 'PKR', 'status' => 'published']);
$database->insert('donors', ['name' => 'Dora Donor', 'email' => 'dora@example.org', 'phone' => '0300']);
$database->insert('donors', ['name' => 'Bad Mail', 'email' => 'not-an-email', 'phone' => '0301']);
$d1 = (int) $database->scalar("SELECT id FROM donors WHERE email='dora@example.org'");
$d2 = (int) $database->scalar("SELECT id FROM donors WHERE email='not-an-email'");
$today = gmdate('Y-m-d');
function mk(Database $d, array $o): int {
    static $i = 0; $i++;
    $d->insert('donations', $o + ['public_reference' => 'ALM-C-' . str_pad((string) $i, 4, '0', STR_PAD_LEFT), 'donor_id' => $GLOBALS['d1'], 'amount_minor' => 250000, 'currency' => 'PKR',
        'gateway_id' => null, 'status' => 'pending', 'source' => 'bank_transfer', 'verification_status' => 'submitted', 'bank_account_id' => 1, 'recorded_by' => 21,
        'transaction_at' => $GLOBALS['today'] . ' 12:00:00']);
    return (int) $d->scalar('SELECT MAX(id) FROM donations');
}
$settingsKey = static fn (string $k, string $v) => $GLOBALS['app']->make(\App\Services\SettingsService::class)->set('certificate.' . $k, $v);
$settingsKey('approval_valid_from', '2020-01-01'); // test dates are "today"; real window is seeded by the migration

// --- 1. migration idempotent, seeded foundation details come from the published FBR order -------------------
$sql = (string) file_get_contents(__DIR__ . '/../database/migrations/0020_donation_certificates.sql');
$pdo->exec($sql); $pdo->exec($sql);
ok($database->scalar("SELECT setting_value FROM site_settings WHERE setting_key='certificate.ntn'") === '4910219', 'NTN seeded from foundation FBR order');
ok($database->scalar("SELECT COUNT(*) FROM site_settings WHERE setting_key LIKE 'certificate.%'") == 8, 'migration re-runs without duplicating settings');

// --- 2. eligibility ----------------------------------------------------------------------------------------
$fin = ['finance_manager'];
$review = $app->make(DonationReviewService::class);
$svc = fn (): DonationCertificateService => $app->make(DonationCertificateService::class);
$pending = mk($database, []);
$rejected = mk($database, []);
$review->decide($rejected, 22, $fin, 'reject', 'Slip unreadable, please resend');
$gwOpen = mk($database, ['gateway_id' => 1, 'verification_status' => 'legacy']);
$refunded = mk($database, ['gateway_id' => 1, 'verification_status' => 'legacy', 'status' => 'refunded']);
foreach (['pending' => $pending, 'rejected' => $rejected, 'gateway unconfirmed' => $gwOpen, 'refunded' => $refunded] as $label => $id) {
    $r = $svc()->issue($id);
    ok($r['ok'] === false && $svc()->find($id) === null, "no certificate for $label donation");
}
ok($database->int('SELECT COUNT(*) FROM donation_certificates') === 0, 'ineligible donations create no certificate rows');

// --- 3. verified offline donation -> certificate + email ----------------------------------------------------
$off = mk($database, ['amount_minor' => 1234500]);
ok($review->decide($off, 22, $fin, 'approve', 'Matches statement') === true, 'finance verifies offline donation');
$res = $svc()->issue($off, 22);
$c = $svc()->find($off);
ok($res['ok'] && $c['status'] === 'generated' && preg_match('/^ALM-TC-\d{4}-\d{6}$/', (string) $c['certificate_number']) === 1, 'certificate generated with number');
$path = $svc()->absolutePath($c);
$bin = (string) file_get_contents((string) $path);
ok($path !== null && str_starts_with($bin, '%PDF') && hash('sha256', $bin) === $c['file_sha256'], 'PDF stored privately with matching hash');
ok($database->scalar('SELECT certificate_status FROM donations WHERE id=:i', ['i' => $off]) === 'issued', 'donation certificate_status updated');
if (trim((string) shell_exec('command -v pdftotext')) !== '') {
    $txt = (string) shell_exec('pdftotext -layout ' . escapeshellarg((string) $path) . ' -');
    foreach (['Dora Donor', 'PKR 12,345.00', 'ALM-C-0005', (string) $c['certificate_number'], '4910219', 'section 2(36)', gmdate('j F Y')] as $needle) {
        ok(str_contains($txt, $needle), "PDF contains $needle");
    }
}
ok($c['email_status'] === 'queued' && $database->int('SELECT COUNT(*) FROM email_jobs') === 1, 'email queued (not sent inline)');
$queue = $app->make(MailQueue::class);
$queue->work(10);
$c = $svc()->syncEmailStatus($svc()->find($off));
$mail = FakeTransport::$sent[0] ?? null;
ok($c['email_status'] === 'sent' && $c['email_sent_at'] !== null && count(FakeTransport::$sent) === 1, 'email delivered once, status recorded');
ok($mail && $mail['to'] === 'dora@example.org' && $mail['attachments'][0]['name'] === $c['certificate_number'] . '.pdf' && $mail['attachments'][0]['bytes'] === $bin, 'PDF attached to the email');
ok(str_contains($mail['html'], $c['certificate_number']), 'email shows certificate number');

// --- 4. duplicate prevention --------------------------------------------------------------------------------
$again = $svc()->issue($off);
$svc()->generate($off); $svc()->sendEmail((int) $c['id'], true);
$queue->work(10);
ok($database->int('SELECT COUNT(*) FROM donation_certificates WHERE donation_id=:i', ['i' => $off]) === 1, 'one certificate row per donation');
ok($again['certificate']['certificate_number'] === $c['certificate_number'] && count(glob(__DIR__ . '/../storage/private/certificates/*.pdf')) === 1, 'number and file unchanged on repeat');
ok(count(FakeTransport::$sent) === 1 && $database->int('SELECT COUNT(*) FROM email_jobs') === 1, 'an emailed certificate is never re-sent');
ok(throws2(fn () => $database->insert('donation_certificates', ['donation_id' => $off, 'status' => 'pending'])), 'database refuses a second certificate row');
function throws2(callable $f): bool { try { $f(); } catch (Throwable) { return true; } return false; }

// --- 5. email failure never affects verification; retry works -----------------------------------------------
FakeTransport::$fail = true;
$o2 = mk($database, []);
$review->decide($o2, 22, $fin, 'approve', 'ok');
$svc()->issue($o2, 22);
for ($i = 0; $i < 4; $i++) { $database->run("UPDATE email_jobs SET available_at = UTC_TIMESTAMP() WHERE status='queued'"); $queue->work(10); }
$c2 = $svc()->syncEmailStatus($svc()->find($o2));
$dn = $database->selectOne('SELECT status, verification_status FROM donations WHERE id=:i', ['i' => $o2]);
ok($c2['status'] === 'generated' && $c2['email_status'] === 'failed' && str_contains((string) $c2['email_last_error'], 'SMTP down'), 'SMTP outage recorded as failed with reason');
ok($dn['status'] === 'completed' && $dn['verification_status'] === 'verified', 'verification unaffected by email failure');
$stillDown = $svc()->sendEmail((int) $c2['id'], true);
ok(in_array($stillDown, ['failed', 'queued'], true) && str_contains((string) $svc()->find($o2)['email_last_error'], 'SMTP down'), 'retry while SMTP is down is not reported as sent');
FakeTransport::$fail = false;
ok($svc()->sendEmail((int) $c2['id'], true) === 'sent' && count(FakeTransport::$sent) === 2, 'retry after recovery delivers');
ok($svc()->sendEmail((int) $c2['id'], true) === 'sent' && count(FakeTransport::$sent) === 2 && $database->int('SELECT COUNT(*) FROM email_jobs') === 2, 'second retry does not duplicate the email');

// --- 6. generation failure is stored and recoverable --------------------------------------------------------
$o3 = mk($database, []);
$review->decide($o3, 22, $fin, 'approve', 'ok');
$settingsKey('ntn', '');
$r3 = $svc()->issue($o3, 22);
$c3 = $svc()->find($o3);
ok($r3['ok'] === false && $c3['status'] === 'failed' && str_contains((string) $c3['generation_error'], 'NTN') && $c3['file_path'] === null, 'missing registration detail: failed, nothing invented');
ok($database->scalar('SELECT status FROM donations WHERE id=:i', ['i' => $o3]) === 'completed', 'donation still verified after generation failure');
$settingsKey('ntn', '4910219');
ok($svc()->issue($o3, 22)['ok'] && $svc()->find($o3)['certificate_number'] === $c3['certificate_number'], 'retry generates with the same certificate number');
$settingsKey('approval_valid_until', '2000-01-01');
$o4 = mk($database, []);
$review->decide($o4, 22, $fin, 'approve', 'ok');
ok($svc()->issue($o4)['ok'] === false && str_contains((string) $svc()->find($o4)['generation_error'], 'approval period'), 'donation outside approval period is not certified');
$settingsKey('approval_valid_until', '2099-01-01');

// --- 7. donor without a usable email; gateway donation ------------------------------------------------------
$o5 = mk($database, ['donor_id' => $d2]);
$review->decide($o5, 22, $fin, 'approve', 'ok');
$svc()->issue($o5);
ok($svc()->find($o5)['status'] === 'generated' && $svc()->find($o5)['email_status'] === 'skipped', 'invalid donor email: certificate kept, email skipped');
$gw = mk($database, ['gateway_id' => 1, 'verification_status' => 'legacy', 'status' => 'completed', 'recorded_by' => null, 'bank_account_id' => null]);
ok($svc()->issue($gw)['ok'] === true, 'gateway-confirmed donation is certifiable');

// --- 8. access control (logged-in staff) ------------------------------------------------------------------
$router = $app->make(Router::class);
function hit(Router $r, $app, string $path, string $method = 'GET', array $data = []): int {
    try { return $r->dispatch(new Request($method, $path, $method === 'GET' ? $data : [], $method === 'POST' ? $data : [], [], []), $app)->status(); }
    catch (HttpException $e) { return $e->statusCode(); }
}
$session = $app->make(Session::class);
/** Switch the logged-in user; AuthService caches the user per instance, so reset it via reflection. */
$login = function (?int $uid) use ($app, $session): void {
    $uid === null ? $session->forget('user_id') : $session->set('user_id', $uid);
    $auth = $app->make(\App\Services\AuthService::class);
    foreach (['resolved' => false, 'cachedUser' => null, 'cachedPermissions' => null] as $prop => $val) {
        $rp = new ReflectionProperty($auth, $prop); $rp->setAccessible(true); $rp->setValue($auth, $val);
    }
};
$login(null);
foreach ([['GET', "/admin/donations/$off/certificate"], ['POST', "/admin/donations/$off/certificate/email"], ['POST', "/admin/donations/$off/certificate/generate"]] as [$m, $p]) {
    ok(in_array(hit($router, $app, $p, $m), [301, 302, 401, 403, 419], true), "guest blocked: $m $p");
}
$login(23); // active user with no staff role
ok(hit($router, $app, "/admin/donations/$off/certificate") === 403, 'non-staff cannot download certificate');
$login(21); // donation_manager: donations.view, not donations.verify
ok(hit($router, $app, "/admin/donations/$off/certificate") === 200, 'staff with donations.view can download');
$csrfPost = static fn (string $p): int => hit($GLOBALS['router'], $GLOBALS['app'], $p, 'POST');
ok(in_array($csrfPost("/admin/donations/$off/certificate/email"), [403, 419], true), 'staff without donations.verify cannot retry email');
ok(in_array($csrfPost("/admin/donations/$off/certificate/generate"), [403, 419], true), 'staff without donations.verify cannot generate');
$login(22);
ok(in_array($csrfPost("/admin/donations/$off/certificate/email"), [403, 419], true), 'finance POST without CSRF token is refused');
ok(hit($router, $app, "/admin/donations/$pending/certificate") === 404, 'no certificate -> 404');
ok(hit($router, $app, "/admin/donations/$off") === 200, 'donation page renders with certificate panel');
$login(null);

// --- 9. existing pages unaffected --------------------------------------------------------------------------
foreach (['/', '/donate/transfer'] as $p) { ok(hit($router, $app, $p) === 200, "public page $p still renders"); }


// --- 10. donor bank-transfer page: two options, report form, bank list -----------------------------------------
$page = static fn (string $path): string => $GLOBALS['router']->dispatch(new Request('GET', parse_url($path, PHP_URL_PATH), (function () use ($path) { parse_str((string) parse_url($path, PHP_URL_QUERY), $q); return $q; })(), [], [], []), $GLOBALS['app'])->body();
$chooser = $page('/donate/transfer');
ok(str_contains($chooser, 'I already paid') && str_contains($chooser, 'I want to make a new transfer') && !str_contains($chooser, 'name="payment_proof"'), 'donor sees the two options first');
$report = $page('/donate/transfer?mode=report');
ok(str_contains($report, 'name="payment_proof"') && str_contains($report, 'accept="image/*"') && str_contains($report, 'name="bank_reference"') && str_contains($report, 'enctype="multipart/form-data"') && str_contains($report, 'Meezan'), 'report form: slip upload, transaction ID, bank list');
$new = $page('/donate/transfer?mode=new&currency=PKR');
ok(str_contains($new, 'Meezan') && str_contains($new, '0001') && str_contains($new, 'report my payment'), 'new-transfer option lists bank accounts and links to reporting');

$donate = $page('/donate');
ok(str_contains($donate, 'I already paid') && str_contains($donate, 'data-transfer-url') && str_contains($donate, 'mode=new'), 'Donate page offers both bank-transfer options');
ok(str_contains((string) file_get_contents(__DIR__ . '/../assets/js/donate.js'), 'donate/transfer'), 'bank transfer button leads to the transfer page');

// --- 12. bank ledgers --------------------------------------------------------------------------------------
$database->insert('bank_accounts', ['id' => 2, 'bank_name' => 'HBL', 'account_title' => 'Almarah', 'account_number' => '9999', 'currency' => 'PKR', 'status' => 'published']);
$database->insert('donors', ['name' => '=HYPERLINK("x")', 'email' => 'evil@example.org', 'phone' => '0302']);
$d3 = (int) $database->scalar("SELECT id FROM donors WHERE email='evil@example.org'");
$h1 = mk($database, ['bank_account_id' => 2, 'donor_id' => $d3, 'amount_minor' => 50000, 'source' => 'online_transfer', 'bank_reference' => 'TXN-HBL-777']);
$review->decide($h1, 22, $fin, 'approve', 'ok');
$h2 = mk($database, ['bank_account_id' => 2, 'amount_minor' => 70000]);
$review->decide($h2, 22, $fin, 'reject', 'Slip unreadable, please resend');
$h3 = mk($database, ['bank_account_id' => 2, 'amount_minor' => 90000]); // pending
$sum = $app->make(\App\Services\BankLedgerService::class)->summary([]);
$by = []; foreach ($sum as $r) { $by[$r['bank_id']] = $r; }
ok(isset($by[1], $by[2], $by[0]), 'summary lists both banks and the gateway ledger');
ok((int) $by[2]['confirmed_count'] === 1 && (int) $by[2]['confirmed_minor'] === 50000 && (int) $by[2]['pending_count'] === 1 && (int) $by[2]['rejected_count'] === 1, 'HBL: rejected and pending are not confirmed');
$expect1 = $database->int("SELECT COUNT(*) FROM donations WHERE bank_account_id = 1 AND status='completed' AND verification_status='verified'");
ok((int) $by[1]['confirmed_count'] === $expect1 && $expect1 > 0, 'Meezan confirmed count matches the donations table');
ok(($by[2]['modes']['Online / mobile banking'] ?? 0) === 1, 'mode breakdown shown');
$login(22);
$p = $page('/admin/ledger/banks');
ok(str_contains($p, 'HBL') && str_contains($p, 'Meezan') && str_contains($p, 'Online payment gateway'), 'bank ledgers page renders all ledgers');
$p = $page('/admin/ledger/banks/2');
ok(str_contains($p, 'TXN-HBL-777') && str_contains($p, 'ALM-C-') , 'bank ledger shows transaction ID and website reference');
ok(hit($router, $app, '/admin/ledger/banks/999') === 404, 'unknown bank -> 404');
ok(hit($router, $app, '/admin/ledger/banks/2/export') === 403, 'CSV needs reports.view');
$database->run("INSERT IGNORE INTO permissions (name, group_name, label) VALUES ('reports.view', 'reports', 'View reports')");
$database->insert('role_permissions', ['role_id' => 10, 'permission_id' => $perm('reports.view')]);
$login(22);
$csv = $GLOBALS['router']->dispatch(new Request('GET', '/admin/ledger/banks/2/export', [], [], [], []), $app);
ok($csv->status() === 200 && str_contains($csv->body(), 'TXN-HBL-777') && str_contains($csv->body(), "\"'=HYPERLINK") && str_contains($csv->body(), 'Website reference'), 'CSV export works and neutralises spreadsheet formulas');
$login(23);
ok(hit($router, $app, '/admin/ledger/banks') === 403, 'non-staff cannot open bank ledgers');
$login(null);
ok(in_array(hit($router, $app, '/admin/ledger/banks'), [301, 302, 401, 403], true), 'guest cannot open bank ledgers');

// --- 13. separate Offline donations menu + entries page ---------------------------------------------------------
$database->insert('roles', ['id' => 12, 'name' => 'record_only', 'label' => 'Record only']);
$database->insert('users', ['id' => 24, 'first_name' => 'Rec', 'last_name' => 'Only', 'email' => 'recordonly@example.org', 'password_hash' => 'x', 'status' => 'active']);
$database->insert('role_permissions', ['role_id' => 12, 'permission_id' => $perm('donations.record')]);
$database->insert('user_roles', ['user_id' => 24, 'role_id' => 12]);
$mine = mk($database, ['recorded_by' => 24, 'amount_minor' => 12300, 'bank_reference' => 'MINE-0001']);
$login(22);
$fp = $page('/admin/offline-donations');
ok(str_contains($fp, 'nav-group">Offline donations') && str_contains($fp, 'Verification queue') && str_contains($fp, 'All offline entries') && str_contains($fp, 'Bank ledgers'), 'finance sees the separate Offline donations menu, queue and bank ledgers');
ok(str_contains($fp, 'MINE-0001') && str_contains($fp, 'ALM-C-0005'), 'finance sees every staff entry');
$login(24);
$rp = $page('/admin/offline-donations');
ok(str_contains($rp, 'nav-group">Offline donations') && str_contains($rp, 'Record donation') && str_contains($rp, 'My entries') && !str_contains($rp, 'Verification queue') && !str_contains($rp, 'Bank ledgers'), 'record-only staff menu: record + my entries, no queue or ledgers');
ok(str_contains($rp, 'MINE-0001') && !str_contains($rp, 'ALM-C-0005'), 'record-only staff sees only their own entries');
ok(hit($router, $app, '/admin/ledger/banks') === 403 && hit($router, $app, '/admin/finance/offline') === 200, 'record-only staff cannot open ledgers but can record');
$login(23);
ok(hit($router, $app, '/admin/offline-donations') === 403, 'non-staff cannot open offline entries');
$login(null);
ok(in_array(hit($router, $app, '/admin/offline-donations'), [301, 302, 401, 403], true), 'guest cannot open offline entries');
ok(trim((string) file_get_contents(__DIR__ . '/../VERSION')) === 'V3.7 — Almarah Foundation V3.7', 'version is V3.7');

// --- 11. site installed in a folder named "donate" (https://host/donate/donate/transfer) --------------------------
\App\Core\BasePath::boot('https://softex.pk/donate');
ok(\App\Core\Response::locationFor('/donate/transfer?mode=done') === '/donate/transfer?mode=done', 'root-relative /donate/... is treated as already prefixed (the bug)');
ok(\App\Core\Response::locationFor('https://softex.pk/donate/donate/transfer?mode=done') === 'https://softex.pk/donate/donate/transfer?mode=done', 'absolute URL keeps the folder + route');
$src = (string) file_get_contents(__DIR__ . '/../app/Http/Controllers/Public/OfflineDonationController.php');
ok(!preg_match("#(redirect|flashFormStateTo)\\([^;]*'/donate/#", $src), 'transfer controller has no root-relative /donate/ redirects');
\App\Core\BasePath::reset();

echo "$n donation certificate Part 2 checks passed.\n";
