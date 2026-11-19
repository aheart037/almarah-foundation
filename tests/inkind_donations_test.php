<?php

declare(strict_types=1);

/**
 * In-kind (goods) donations: validation, saving, finance verification workflow, certificate versions + email,
 * recipient-name certificates, money isolation, public form and admin access control.
 * Destructive: needs a disposable DB whose name contains "test".
 *   TEST_DB_NAME=almarah_test php tests/inkind_donations_test.php
 */
require __DIR__ . '/bootstrap.php';

use App\Core\Database;
use App\Core\Request;
use App\Core\Router;
use App\Core\Session;
use App\Exceptions\HttpException;
use App\Mail\MailQueue;
use App\Mail\TransportInterface;
use App\Services\InKindCertificateService;
use App\Services\InKindDonationService;
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
// The shipped config.php is a folder install (site_url .../donate), which strips /donate from request paths; tests use root URLs.
\App\Core\BasePath::reset();
\App\Core\BasePath::boot('https://example.org');
$n = 0;
function ok(bool $c, string $label): void { global $n; if (!$c) throw new RuntimeException('FAIL ' . $label); $n++; }
function throws(callable $f, string $needle = ''): bool { try { $f(); } catch (Throwable $e) { return $needle === '' || stripos($e->getMessage(), $needle) !== false; } return false; }

final class IkFakeTransport implements TransportInterface
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
$app->bind(TransportInterface::class, static fn () => new IkFakeTransport());

$pdo = $database->pdo();
$pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
foreach (['inkind_certificates', 'inkind_events', 'inkind_donation_items', 'inkind_donations', 'email_jobs'] as $t) { $pdo->exec("TRUNCATE TABLE `$t`"); }
$pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
@array_map('unlink', glob(__DIR__ . '/../storage/private/certificates/ALM-IK-*.pdf') ?: []);

// staff: 10 = finance (view+verify), 11 = view only
$database->run("INSERT IGNORE INTO roles (id, name, label) VALUES (10, 'finance_manager', 'Finance'), (11, 'donation_manager', 'Donation manager')");
foreach ([[21, 'ikrec@example.org'], [22, 'ikfin@example.org'], [23, 'ikplain@example.org']] as [$id, $mail]) {
    $database->run("INSERT IGNORE INTO users (id, first_name, last_name, email, password_hash, status) VALUES ($id, 'U$id', 'T', '$mail', 'x', 'active')");
}
$database->run("INSERT IGNORE INTO permissions (name, group_name, label) VALUES ('donations.view', 'donations', 'View donations')");
$perm = static fn (string $p): int => (int) $database->scalar('SELECT id FROM permissions WHERE name = :n', ['n' => $p]);
foreach (['donations.view', 'donations.verify'] as $p) { $database->run('INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (10, ' . $perm($p) . ')'); }
$database->run('INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (11, ' . $perm('donations.view') . ')');
$database->run('INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (22, 10), (21, 11)');

$svc = $app->make(InKindDonationService::class);
$certs = $app->make(InKindCertificateService::class);
$moneyBefore = (int) $database->scalar('SELECT COUNT(*) FROM donations');

// ---- 1. tables ---------------------------------------------------------------------------------
foreach (['inkind_donations', 'inkind_donation_items', 'inkind_events', 'inkind_certificates'] as $t) {
    ok((int) $database->scalar("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = '$t'") === 1, "table $t exists");
}

// ---- 2. validation -----------------------------------------------------------------------------
$base = ['donor_name' => 'Ali Raza', 'donor_email' => 'ali@example.org', 'donor_phone' => '0300111', 'handover_method' => 'drop_off',
         'item_category' => ['footwear'], 'item_name' => ['Winter shoes'], 'item_qty' => ['50'], 'item_unit' => ['pairs'], 'item_details' => ['Sizes 3-8']];
ok(throws(fn () => $svc->submit(array_merge($base, ['donor_email' => 'nope'])), 'valid email'), 'bad email refused');
ok(throws(fn () => $svc->submit(array_merge($base, ['donor_name' => ''])), 'required'), 'name required');
ok(throws(fn () => $svc->submit(array_merge($base, ['item_name' => [''], 'item_qty' => ['']])), 'at least one item'), 'no items refused');
ok(throws(fn () => $svc->submit(array_merge($base, ['item_qty' => ['0']])), 'quantity'), 'zero quantity refused');
ok(throws(fn () => $svc->submit(array_merge($base, ['item_qty' => ['12.5']])), 'quantity'), 'fractional quantity refused');
ok(throws(fn () => $svc->submit(array_merge($base, ['item_name' => ['Shoes'], 'item_qty' => ['']])), 'quantity'), 'missing quantity refused');
ok(throws(fn () => $svc->submit(array_merge($base, ['handover_method' => 'pickup'])), 'collect'), 'pickup needs an address');
ok(throws(fn () => $svc->submit(array_merge($base, ['handover_method' => 'teleport'])), 'how the goods'), 'bad hand-over refused');
ok(throws(fn () => $svc->submit(array_merge($base, ['handover_date' => '2001-01-01'])), 'date'), 'old hand-over date refused');
ok(throws(fn () => $svc->submit(array_merge($base, ['acknowledgment_self' => '0'])), 'Recipient'), 'recipient name required');
ok(throws(fn () => $svc->submit(array_merge($base, ['referral_enabled' => '1'])), 'Referrer'), 'referrer name required');
ok(throws(fn () => $svc->submit(array_merge($base, ['item_name' => array_fill(0, 16, 'x'), 'item_qty' => array_fill(0, 16, '1')])), 'up to'), 'too many items refused');
ok((int) $database->scalar('SELECT COUNT(*) FROM inkind_donations') === 0, 'failed submissions save nothing');

// ---- 3. successful submission ------------------------------------------------------------------
$multi = array_merge($base, [
    'item_category' => ['footwear', 'clothing', 'food', 'weird'], 'item_name' => ['Winter shoes', 'Jackets', 'Food packets', 'Misc'],
    'item_qty' => ['50', '200', '120', ' '], 'item_unit' => ['pairs', '', 'packets', ''], 'item_details' => ['Sizes 3-8', '', 'Ready to eat', ''],
    'item_name_blank' => 'x', 'description' => 'All new', 'donor_city' => 'Lahore', 'handover_date' => gmdate('Y-m-d'),
]);
$multi['item_name'][3] = ''; $multi['item_qty'][3] = ''; // blank row is skipped
$r = $svc->submit($multi);
ok(str_starts_with($r['reference'], 'IKD-' . gmdate('Y') . '-'), 'reference format');
$row = $svc->find($r['id']);
ok($row['status'] === 'pending' && $row['donor_email'] === 'ali@example.org' && $row['donor_city'] === 'Lahore', 'saved as pending with donor details');
$items = $svc->items($r['id']);
ok(count($items) === 3 && (int) $items[0]['quantity'] === 50 && $items[0]['unit'] === 'pairs' && $items[1]['category'] === 'clothing', 'item lines saved in order');
ok($svc->events($r['id'])[0]['action'] === 'submitted', 'submission event recorded');
ok((int) $database->scalar("SELECT COUNT(*) FROM donors WHERE email = 'ali@example.org'") === 1, 'donor record created');
ok((int) $database->scalar('SELECT COUNT(*) FROM donations') === $moneyBefore, 'goods never create a money donation');
$r2 = $svc->submit(array_merge($base, ['item_category' => ['bogus'], 'handover_method' => 'pickup', 'pickup_address' => '12 Canal Road',
    'acknowledgment_self' => '0', 'recipient_name' => 'Ayesha Recipient', 'recipient_email' => 'ayesha@example.org', 'referral_enabled' => '1', 'referrer_name' => 'Bilal', 'referrer_phone' => '0300']));
ok($svc->items($r2['id'])[0]['category'] === 'other', 'unknown category becomes other');
ok(InKindDonationService::certificateName($svc->find($r2['id'])) === 'Ayesha Recipient' && InKindDonationService::certificateName($row) === 'Ali Raza', 'certificate name = recipient when given on behalf');
ok($svc->find($r2['id'])['referrer_name'] === 'Bilal', 'referrer saved');
ok($svc->search(['q' => 'Jackets'], 1)['total'] === 1 && $svc->search(['status' => 'pending'], 1)['total'] === 2 && $svc->search(['status' => 'verified'], 1)['total'] === 0, 'search by item/status');
ok($svc->statusCounts()['pending'] === 2, 'status counts');

// ---- 4. workflow -------------------------------------------------------------------------------
ok(throws(fn () => $certs->generate($r['id'], 22), 'verified'), 'no certificate before verification');
ok(throws(fn () => $svc->decide($r['id'], 22, 'reject', ''), 'reason'), 'rejection needs a reason');
ok(throws(fn () => $svc->decide($r['id'], 22, 'reopen', ''), 'no longer'), 'cannot reopen a pending donation');
$svc->decide($r2['id'], 22, 'reject', 'Items not received');
ok($svc->find($r2['id'])['status'] === 'rejected' && $svc->find($r2['id'])['review_note'] === 'Items not received', 'rejected with reason');
ok(throws(fn () => $svc->decide($r2['id'], 22, 'approve', ''), 'no longer'), 'rejected cannot be approved directly');
$svc->decide($r2['id'], 22, 'reopen', 'Donor called back');
ok($svc->find($r2['id'])['status'] === 'pending', 'reopened to pending');
$svc->decide($r['id'], 22, 'approve', 'Counted by warehouse');
ok($svc->find($r['id'])['status'] === 'verified' && (int) $svc->find($r['id'])['reviewed_by'] === 22, 'verified by finance');
ok(throws(fn () => $svc->decide($r['id'], 22, 'approve', ''), 'no longer'), 'double approval refused');
ok(throws(fn () => $svc->decide($r['id'], 22, 'reject', 'too late please'), 'no longer'), 'verified cannot be rejected');
ok(count($svc->events($r['id'])) === 2, 'events: submitted + verified');

// ---- 5. certificate + email --------------------------------------------------------------------
$g = $certs->generate($r['id'], 22);
ok($g['ok'] && $g['row']['status'] === 'generated' && (int) $g['row']['version'] === 1, 'certificate generated v1');
ok($g['row']['certificate_number'] === sprintf('ALM-IK-%s-%06d', gmdate('Y'), $r['id']), 'certificate number format');
$abs = $certs->absolutePath($g['row']);
ok($abs !== null && str_starts_with((string) file_get_contents($abs), '%PDF'), 'PDF file stored');
if (trim((string) shell_exec('command -v pdftotext')) !== '') {
    $txt = (string) shell_exec('pdftotext -layout ' . escapeshellarg($abs) . ' -');
    ok(str_contains($txt, 'Certificate of Appreciation') && str_contains($txt, 'Ali Raza') && str_contains($txt, '50 pairs of Winter shoes')
        && str_contains($txt, '200') && str_contains($txt, 'Jackets') && str_contains($txt, 'Sophia Warraich') && str_contains($txt, 'Chairperson'), 'certificate shows name, items and chairperson');
    ok(!str_contains($txt, 'Tax Exemption') && !str_contains($txt, 'PKR'), 'no tax/amount wording on goods certificate');
    ok((int) preg_replace('/\D/', '', (string) shell_exec('pdfinfo ' . escapeshellarg($abs) . ' | grep Pages')) === 1, 'one page');
}
ok($certs->generate($r['id'], 22)['row']['id'] === $g['row']['id'], 'generate is idempotent');
$v2 = $certs->generate($r['id'], 22, true);
ok((int) $v2['row']['version'] === 2 && count($certs->history($r['id'])) === 2 && $certs->absolutePath($g['row']) !== null, 'new version added, v1 kept');

$mq = $app->make(MailQueue::class);
IkFakeTransport::$sent = [];
$st = $certs->sendEmail((int) $v2['row']['id'], false);
ok($st === 'queued', 'email queued');
$job = $database->selectOne("SELECT * FROM email_jobs WHERE event_key = 'inkind.certificate'");
ok($job && $job['recipient_email'] === 'ali@example.org', 'queued to the donor');
$mq->processJobNow((int) $job['id']);
$cur = $certs->syncEmailStatus($certs->find((int) $v2['row']['id']));
ok($cur['email_status'] === 'sent' && count(IkFakeTransport::$sent) === 1, 'email sent');
$mail = IkFakeTransport::$sent[0];
ok(count($mail['attachments']) === 1 && str_starts_with($mail['attachments'][0]['bytes'], '%PDF') && str_ends_with($mail['attachments'][0]['name'], '.pdf'), 'certificate attached to the email');
ok(str_contains($mail['html'], 'Winter shoes') && str_contains($mail['subject'], $r['reference']), 'email mentions items and reference');
ok($certs->sendEmail((int) $v2['row']['id'], false) === 'sent' && count(IkFakeTransport::$sent) === 1, 'a sent email is not sent twice');
IkFakeTransport::$fail = true;
ok($certs->sendEmail((int) $v2['row']['id'], true) !== 'sent', 'outage reported on resend');
IkFakeTransport::$fail = false;
$certs->sendEmail((int) $v2['row']['id'], true);
ok($certs->syncEmailStatus($certs->find((int) $v2['row']['id']))['email_status'] === 'sent', 'staff resend works after outage');
ok((int) $database->scalar("SELECT COUNT(*) FROM email_jobs WHERE event_key = 'inkind.certificate'") === 1, 'still a single mail job per certificate');

// recipient-name certificate
$svc->decide($r2['id'], 22, 'approve', '');
$g2 = $certs->generate($r2['id'], 22);
ok($g2['row']['certificate_name'] === 'Ayesha Recipient', 'certificate issued in the recipient name');
if (isset($txt)) { ok(str_contains((string) shell_exec('pdftotext -layout ' . escapeshellarg((string) $certs->absolutePath($g2['row'])) . ' -'), 'Ayesha Recipient'), 'recipient name printed'); }
ok((int) $database->scalar('SELECT COUNT(*) FROM donations') === $moneyBefore, 'money donations untouched by the whole workflow');

// ---- 6. HTTP: public + admin access ------------------------------------------------------------
$router = $app->make(Router::class);
function ikhit(Router $r, $app, string $path, string $method = 'GET', array $data = []): int {
    try { return $r->dispatch(new Request($method, $path, $method === 'GET' ? $data : [], $method === 'POST' ? $data : [], [], []), $app)->status(); }
    catch (HttpException $e) { return $e->statusCode(); }
}
$session = $app->make(Session::class);
$login = function (?int $uid) use ($app, $session): void {
    $uid === null ? $session->forget('user_id') : $session->set('user_id', $uid);
    $auth = $app->make(\App\Services\AuthService::class);
    foreach (['resolved' => false, 'cachedUser' => null, 'cachedPermissions' => null] as $prop => $val) {
        $rp = new ReflectionProperty($auth, $prop); $rp->setAccessible(true); $rp->setValue($auth, $val);
    }
};
$login(null);
ok(ikhit($router, $app, '/donate/goods') === 200, 'public goods form renders');
ok(ikhit($router, $app, '/donate') === 200, 'donate page still renders');
ok(ikhit($router, $app, '/donate/transfer') === 200, 'bank transfer page unaffected');
ok(in_array(ikhit($router, $app, '/donate/goods', 'POST', ['donor_name' => 'x']), [403, 419], true), 'public POST needs CSRF');
foreach (['/admin/inkind', '/admin/inkind/' . $r['id']] as $p) { ok(in_array(ikhit($router, $app, $p), [301, 302, 401, 403], true), "guest blocked: $p"); }
$login(23);
ok(ikhit($router, $app, '/admin/inkind') === 403, 'non-staff cannot list');
$login(21); // view only
ok(ikhit($router, $app, '/admin/inkind') === 200 && ikhit($router, $app, '/admin/inkind/' . $r['id']) === 200, 'viewer can open list and detail');
ok(in_array(ikhit($router, $app, '/admin/inkind/' . $r['id'] . '/decide', 'POST', ['decision' => 'approve']), [403, 419], true), 'viewer cannot verify');
ok(in_array(ikhit($router, $app, '/admin/inkind/certificates/' . $v2['row']['id'] . '/resend', 'POST'), [403, 419], true), 'viewer cannot resend');
ok(ikhit($router, $app, '/admin/inkind/certificates/' . $v2['row']['id'] . '/download') === 200, 'viewer can download certificate');
$login(22); // finance
ok(ikhit($router, $app, '/admin/inkind?status=pending&q=Ali') === 200 && ikhit($router, $app, '/admin/inkind/' . $r2['id']) === 200, 'finance pages render');
ok(in_array(ikhit($router, $app, '/admin/inkind/' . $r['id'] . '/decide', 'POST', ['decision' => 'approve']), [403, 419], true), 'verify POST without CSRF is refused');
ok(ikhit($router, $app, '/admin/inkind/999999') === 302, 'unknown donation redirects back to the list');
ok(ikhit($router, $app, '/admin/inkind/certificates/999999/download') === 404, 'unknown certificate -> 404');
ok(ikhit($router, $app, '/admin/inkind/' . $r['id'] . '/photo/0') === 404, 'no photo -> 404');
$login(null);

echo "OK: $n in-kind donation checks passed\n";
