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

echo "$n donation certificate Part 2 checks passed.\n";
