<?php

declare(strict_types=1);

/** Isolated destructive integration suite: TEST_DB_NAME MUST contain 'test'. */
require __DIR__ . '/bootstrap.php';

use Tests\Reports\Fixture;
use App\Core\Config;
use App\Core\Database;
use App\Services\DonationReviewService;
use App\Services\DonationDocumentService;
use App\Services\FinanceService;

putenv('DB_HOST=' . (getenv('TEST_DB_HOST') ?: '127.0.0.1'));
putenv('DB_DATABASE=' . (getenv('TEST_DB_NAME') ?: 'almarah_test'));
putenv('DB_USERNAME=' . (getenv('TEST_DB_USER') ?: 'alm'));
putenv('DB_PASSWORD=' . (getenv('TEST_DB_PASS') ?: 'almpass'));
if (!str_contains((string) getenv('DB_DATABASE'), 'test')) throw new RuntimeException('Refusing production database.');
$app = require __DIR__ . '/../bootstrap/app.php';
$db = $app->make(Database::class);
Fixture::migrate($db);
Fixture::seed($db);
$svc = $app->make(DonationReviewService::class);
$finance = $app->make(FinanceService::class);
$docs = $app->make(DonationDocumentService::class);
$passed = 0;
function checkReview(bool $ok, string $message): void {
    global $passed;
    if (!$ok) throw new RuntimeException('FAIL: ' . $message);
    $passed++;
    echo "ok $message\n";
}
function shouldFail(callable $fn): bool {
    try { $fn(); return false; } catch (InvalidArgumentException $e) { return true; }
}
$input = [
    'source'=>'bank_transfer','donor_name'=>'A Donor','donor_email'=>'donor-two@example.org',
    'donor_phone'=>'+923001234567','amount'=>'101.25','received_at'=>'2026-10-20',
    'bank_reference'=>'REF-123','acknowledgment_self'=>'0','recipient_name'=>'A Recipient',
    'recipient_email'=>'recipient@example.org','referral_enabled'=>'1','referrer_name'=>'A Referrer',
];
$r = $svc->submit($input);
$id = $r['id'];
$d = $db->selectOne('SELECT * FROM donations WHERE id=:id', ['id'=>$id]);
checkReview($d['status'] === 'pending' && $d['verification_status'] === 'submitted' && $d['receipt_number'] === null, 'unverified submission has no official receipt or financial completion');
checkReview($d['recipient_name'] === 'A Recipient' && $d['referrer_name'] === 'A Referrer' && (int) $d['amount_minor'] === 10125, 'recipient, referrer and exact minor-unit amount persisted');
checkReview(shouldFail(fn () => $finance->assignReceiptNumber($id)), 'receipt blocked before verification');
$r2 = $svc->submit($input);
checkReview($r2['possible_duplicates'] >= 1 && $r2['id'] !== $id, 'duplicate reference warns but does not block legitimate record');
checkReview(shouldFail(fn () => $svc->decide($r2['id'], 1, ['initial_reviewer'], 'reject', '')), 'rejection requires reason');
$db->insert('users', ['id'=>7,'first_name'=>'Staff','last_name'=>'7','email'=>'staff7@example.org','password_hash'=>'x','status'=>'active']);
$svc->decide($r2['id'], 7, ['initial_reviewer'], 'reject', 'Incorrect bank details');
checkReview($db->selectOne('SELECT verification_status FROM donations WHERE id=:id', ['id'=>$r2['id']])['verification_status'] === 'rejected', 'rejection recorded as terminal review state');
checkReview(shouldFail(fn () => $svc->decide($r2['id'], 4, ['verification_officer'], 'approve', 'Cannot review')), 'cannot approve rejected donation');
checkReview(shouldFail(fn () => $finance->adjust($r2['id'], 'mark_completed', ['reason'=>'Bypass stage review'], 5)), 'legacy manual completion cannot bypass new review');
$staffSubmission = $svc->submit($input, null, 1);
checkReview(shouldFail(fn () => $svc->decide($staffSubmission['id'], 1, ['initial_reviewer'], 'approve', 'Self-review')), 'staff submitter cannot approve own donation');
checkReview(shouldFail(fn () => $svc->decide($id, 1, ['donor'], 'approve', 'Checked')), 'unauthorized role denied');
$actors = [[1,'initial_reviewer'],[4,'verification_officer'],[5,'finance_manager'],[6,'super_admin']];
foreach ($actors as [$actor,$role]) {
    if ($actor !== 1) {
        $db->insert('users', ['id'=>$actor,'first_name'=>'Staff','last_name'=>(string)$actor,'email'=>'staff'.$actor.'@example.org','password_hash'=>'x','status'=>'active']);
    }
    $final = $svc->decide($id, $actor, [$role], 'approve', 'Confirmed evidence and source.');
    checkReview($final === ($actor === 6), 'stage processed by distinct authorized staff ' . $actor);
    if ($actor === 1) checkReview(shouldFail(fn () => $svc->decide($id, 1, ['verification_officer'], 'approve', 'Repeated')), 'self/previous reviewer denied');
}
$d = $db->selectOne('SELECT * FROM donations WHERE id=:id', ['id'=>$id]);
checkReview($d['status'] === 'completed' && $d['verification_status'] === 'verified' && (int)$d['verified_by'] === 6, 'final verification completes offline donation');
checkReview(count($svc->history($id)) === 5, 'append-only verification history includes submission and four decisions');
$n = $finance->assignReceiptNumber($id);
checkReview($n === $finance->assignReceiptNumber($id) && str_starts_with($n, 'R-'), 'unique idempotent receipt number');
checkReview($db->selectOne('SELECT receipt_generated_at FROM donations WHERE id=:id', ['id'=>$id])['receipt_generated_at'] !== null, 'receipt generation time stored');
checkReview(str_starts_with($docs->render($id, 'receipt'), '%PDF-'), 'actual branded PDF receipt');
checkReview(str_starts_with($docs->render($id, 'letter'), '%PDF-'), 'actual thank-you PDF');
checkReview(str_starts_with($docs->render($id, 'certificate'), '%PDF-'), 'authorized certificate PDF');
checkReview(shouldFail(fn () => $docs->render($r2['id'], 'receipt')), 'rejected donation cannot create receipt PDF');
$svc->captureGatewayDetails($id, ['acknowledgment_self'=>'0', 'recipient_name'=>'A Recipient', 'referral_enabled'=>'1', 'referrer_name'=>'A Referrer'], 'meezan');
checkReview($db->selectOne('SELECT payment_method FROM donations WHERE id=:id', ['id'=>$id])['payment_method'] === 'meezan', 'gateway checkout stores additional acknowledgment/referral metadata without changing payment state');
checkReview(shouldFail(fn () => $svc->captureGatewayDetails($id, ['acknowledgment_self'=>'0'], 'meezan')), 'gateway recipient validation enforces conditional name');
$rows = $app->make(\App\Repositories\DonationRepository::class)->search(['referrer'=>'A Referrer','verification_status'=>'verified'],1,25);
checkReview($rows['total'] >= 1, 'admin referrer and verification filters');
// Exercise the real queue's attachment gate with a capturing SMTP substitute.
$transport = new class implements \App\Mail\TransportInterface {
    public array $attachments = [];
    public function send(string $toEmail, string $toName, string $subject, string $htmlBody, string $textBody): bool { throw new RuntimeException('Verified receipt must include PDF attachments.'); }
    public function sendWithAttachments(string $toEmail, string $toName, string $subject, string $htmlBody, string $textBody, array $attachments): bool { $this->attachments = $attachments; return true; }
    public function testConnection(): bool { return true; }
    public function describe(): string { return 'test'; }
};
$queue = new \App\Mail\MailQueue($db, $transport, $app->make(\App\Services\EmailTemplateService::class), $app->make(\App\Core\Logger::class), ['queue'=>['enabled'=>true,'max_retries'=>1]]);
$goodJob = $db->insert('email_jobs', ['event_key'=>'donation.receipt','recipient_email'=>'donor-two@example.org','subject'=>'Receipt','template_data_json'=>json_encode(['reference'=>$r['reference'], 'donor_name'=>'A Donor', 'amount'=>'PKR 101.25']), 'status'=>'queued']);
checkReview($queue->processJob($goodJob), 'verified receipt job delivered through existing email queue');
checkReview(count($transport->attachments) === 2 && str_starts_with($transport->attachments[0]['data'], '%PDF-'), 'receipt and certificate attached as actual PDFs');
checkReview($db->selectOne('SELECT receipt_email_status FROM donations WHERE id=:id', ['id'=>$id])['receipt_email_status'] === 'sent', 'email delivery status synchronized with successful queue job');
$badJob = $db->insert('email_jobs', ['event_key'=>'donation.receipt','recipient_email'=>'donor-two@example.org','subject'=>'Receipt','template_data_json'=>json_encode(['reference'=>$r2['reference']]),'status'=>'queued']);
checkReview(!$queue->processJob($badJob) && $db->selectOne('SELECT status FROM email_jobs WHERE id=:id', ['id'=>$badJob])['status'] === 'failed', 'unverified email refused and failure recorded');
$router = $app->make(\App\Core\Router::class);
$public = $router->dispatch(new \App\Core\Request('GET', '/donate/transfer', [], [], [], []), $app);
checkReview($public->status() === 200 && str_contains($public->body(), 'Payment proof'), 'public transfer form renders on new route');
$perm = $db->insert('permissions', ['name'=>'donations.view','group_name'=>'donations','label'=>'View donations']);
$db->insert('role_permissions', ['role_id'=>1, 'permission_id'=>$perm]);
$app->make(\App\Core\Session::class)->start();
$app->make(\App\Services\AuthService::class)->login($db->selectOne('SELECT * FROM users WHERE id=1'), '127.0.0.1');
$detail = $router->dispatch(new \App\Core\Request('GET', '/admin/donations/' . $id, [], [], [], []), $app);
checkReview($detail->status() === 200 && str_contains($detail->body(), 'Verification history') && str_contains($detail->body(), 'A Referrer'), 'existing admin detail renders enhanced review and referral sections');
$list = $router->dispatch(new \App\Core\Request('GET', '/admin/donations', [], [], [], []), $app);
checkReview($list->status() === 200 && str_contains($list->body(), 'Verification'), 'existing admin donation ledger renders enhanced filters');
echo "{$passed} donation review assertions passed.\n";
