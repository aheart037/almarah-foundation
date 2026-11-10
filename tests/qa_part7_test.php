<?php

declare(strict_types=1);

/** Destructive QA: runs only on a disposable DB with "test" in its name. */
require __DIR__ . '/bootstrap.php';
use Tests\Reports\Fixture;
use App\Core\Request;
use App\Core\Router;
use App\Core\Session;
use App\Core\Csrf;
use App\Core\Response;
use App\Core\Database;
use App\Services\AuthService;
use App\Services\ReportService;
use App\Mail\MailQueue;
use App\Mail\TransportInterface;

putenv('DB_HOST=' . (getenv('TEST_DB_HOST') ?: '127.0.0.1'));
putenv('DB_DATABASE=' . (getenv('TEST_DB_NAME') ?: 'almarah_test'));
putenv('DB_USERNAME=' . (getenv('TEST_DB_USER') ?: 'alm'));
putenv('DB_PASSWORD=' . (getenv('TEST_DB_PASS') ?: 'almpass'));
putenv('APP_CURRENCY=PKR');
$db = Fixture::connect(); Fixture::migrate($db); Fixture::seed($db);
$app = require __DIR__ . '/../bootstrap/app.php';
$session = $app->make(Session::class); $session->start();
$csrf = $app->make(Csrf::class); $token = $csrf->token();
$router = $app->make(Router::class); $auth = $app->make(AuthService::class);
$checks = 0;
function qa(bool $condition, string $label): void { global $checks; if (!$condition) throw new RuntimeException('FAIL: ' . $label); $checks++; }
function callQa(Router $router, $app, string $method, string $path, array $input = []): Response {
    $_SERVER['REQUEST_URI'] = $path;
    return $router->dispatch(new Request($method, $path, $method === 'GET' ? $input : [], $method === 'POST' ? $input : [], [], []), $app);
}
function cardQa(string $html, string $title): string {
    $doc = new DOMDocument(); @$doc->loadHTML($html); $xpath = new DOMXPath($doc);
    foreach ($xpath->query("//*[contains(concat(' ',normalize-space(@class),' '),' stat-card ')]") as $item) {
        $key = $xpath->query(".//*[contains(concat(' ',normalize-space(@class),' '),' k ')]", $item)->item(0);
        if (trim($key?->textContent ?? '') === $title) return trim($xpath->query(".//*[contains(concat(' ',normalize-space(@class),' '),' v ')]", $item)->item(0)?->textContent ?? '');
    }
    throw new RuntimeException('Missing card ' . $title);
}
function summaryQa(array $report, string $label): int { foreach ($report['summary'] as $card) if ($card['label'] === $label) return (int)$card['value']; throw new RuntimeException('Missing summary ' . $label); }
function exportPathQa(Response $response): string { return (new ReflectionProperty(Response::class, 'filePath'))->getValue($response); }
foreach (['dashboard.view','reports.view','donations.view','logs.view','finance.manage'] as $name) {
    $id = $db->insert('permissions', ['name'=>$name,'group_name'=>explode('.',$name)[0],'label'=>$name]);
    $db->insert('role_permissions', ['role_id'=>1,'permission_id'=>$id]);
}
$db->insert('donations', ['public_reference'=>'QA-LEGACY','donor_id'=>1,'amount_minor'=>7000000,'currency'=>'PKR','status'=>'completed','verification_status'=>'legacy','completed_at'=>'2026-10-18 12:00:00']);
$db->insert('donations', ['public_reference'=>'QA-USD','donor_id'=>1,'amount_minor'=>8000000,'currency'=>'USD','status'=>'completed','verification_status'=>'verified','completed_at'=>'2026-10-18 12:00:00']);
$auth->login($db->selectOne('SELECT * FROM users WHERE id=1'), '127.0.0.1');
$filters = ['date_from'=>'2000-01-01','date_to'=>'2100-12-31','currency'=>'PKR'];
$report = $app->make(ReportService::class);
$donations = $report->run('donations', $filters);
$financial = $report->run('financial', $filters);
$confirmed = summaryQa($donations, 'Confirmed amount');
$gross = summaryQa($financial, 'Gross received');
$refunds = summaryQa($financial, 'Refunded');
$net = summaryQa($financial, 'Net received');
$dashboard = callQa($router, $app, 'GET', '/admin')->body();
qa(cardQa($dashboard, 'Verified donations') === money_short($confirmed, 'PKR'), 'dashboard confirmed matches report confirmed; excludes legacy, USD and refunded status');
qa($gross - $refunds === $net && $gross > $confirmed, 'report gross includes verified refunded gift; refunds deducted once from net');
qa(summaryQa($report->run('financial', array_merge($filters, ['currency'=>'USD'])), 'Gross received') !== $gross, 'USD and PKR amounts separated');
$reportHtml = callQa($router, $app, 'GET', '/admin/reports/financial', $filters)->body();
qa(str_contains($reportHtml, money($gross)) && str_contains($reportHtml, money($net)), 'report page shows the same gross/net amounts');
$pdf = callQa($router, $app, 'GET', '/admin/reports/financial/pdf', $filters);
qa($pdf->status() === 200 && str_starts_with($pdf->body(), '%PDF-') && str_contains($pdf->headers()['Cache-Control'], 'no-store'), 'protected PDF and private headers');
$tmp = tempnam(sys_get_temp_dir(), 'qa-pdf-'); file_put_contents($tmp, $pdf->body());
$pdfText = shell_exec('pdftotext -layout ' . escapeshellarg($tmp) . ' -'); unlink($tmp);
qa(is_string($pdfText) && str_contains($pdfText, money($gross)) && str_contains($pdfText, money($net)) && str_contains($pdfText, money($refunds)), 'independent PDF text extraction confirms matching financial figures');
$csv = callQa($router, $app, 'GET', '/admin/reports/financial/csv', $filters); $csvPath = exportPathQa($csv);
qa(is_file($csvPath) && str_contains(file_get_contents($csvPath), money($gross)) && str_contains(file_get_contents($csvPath), money($net)) && str_contains(file_get_contents($csvPath), money($refunds)), 'CSV summary matches page and PDF'); unlink($csvPath);
$xlsx = callQa($router, $app, 'GET', '/admin/reports/financial/xlsx', $filters); $xlsxPath = exportPathQa($xlsx);
$zip = new ZipArchive(); qa($zip->open($xlsxPath) === true, 'Excel export opens'); $sheet = $zip->getFromName('xl/worksheets/sheet1.xml');
qa(is_string($sheet) && str_contains($sheet, money($gross)) && str_contains($sheet, money($net)) && str_contains($sheet, '<v>13300.00</v>') && str_contains($sheet, '<v>10300.00</v>'), 'Excel summary and numeric period cells match page/PDF/CSV'); $zip->close(); unlink($xlsxPath);
qa($db->int("SELECT COUNT(*) FROM audit_logs WHERE action='report.exported'") >= 3, 'all financial export downloads audited');
// Financial exports must not be served if the audit trail cannot be written.
$db->pdo()->exec('RENAME TABLE audit_logs TO audit_logs_qa_offline');
try {
    try { callQa($router, $app, 'GET', '/admin/reports/financial/pdf', $filters); throw new RuntimeException('Unaudited PDF was served'); }
    catch (PDOException $e) { qa(true, 'PDF fails closed when audit storage unavailable'); }
    try { callQa($router, $app, 'GET', '/admin/reports/financial/csv', $filters); throw new RuntimeException('Unaudited CSV was served'); }
    catch (PDOException $e) { qa(true, 'CSV fails closed when audit storage unavailable'); }
} finally { $db->pdo()->exec('RENAME TABLE audit_logs_qa_offline TO audit_logs'); }
qa(!str_contains($reportHtml, 'QA-LEGACY') && $confirmed < 7000000, 'legacy entry does not pollute report totals');
qa($report->run('donations', array_merge($filters, ['q'=>"%' OR 1=1 --"]))['total'] === 0, 'parameterized report search resists SQL injection');
$malicious = '<script>alert(7)</script>';
$db->insert('donors', ['name'=>$malicious,'email'=>'xss-qa@example.org']);
qa(!str_contains(callQa($router, $app, 'GET', '/admin/donors')->body(), $malicious), 'admin donor name escaped, no raw script in HTML');
$uploads = $app->make(\App\Services\UploadService::class);
qa($uploads->privatePath('private/donation_proofs/../../.env') === null, 'private download rejects traversal');
$privateDir = __DIR__ . '/../storage/private/donation_proofs'; if (!is_dir($privateDir)) mkdir($privateDir, 0770, true);
$secret = tempnam(sys_get_temp_dir(), 'qa-secret-'); file_put_contents($secret, 'secret'); $link = $privateDir . '/qa-escape.pdf'; symlink($secret, $link);
try { qa($uploads->privatePath('private/donation_proofs/qa-escape.pdf') === null, 'private file resolver rejects symlinks outside storage'); }
finally { unlink($link); unlink($secret); }
$proofFile = $privateDir . '/qa-protected.pdf'; file_put_contents($proofFile, '%PDF-1.4\nQA only');
$db->update('donations',['payment_proof_path'=>'private/donation_proofs/qa-protected.pdf'],'id=:id',['id'=>1]);
try {
    $protected = callQa($router,$app,'GET','/admin/donations/1/proof');
    qa($protected->status()===200 && realpath(exportPathQa($protected))===realpath($proofFile) && str_contains($protected->headers()['Cache-Control'],'no-store'), 'authorized proof download streams private no-store document');
    qa($db->int("SELECT COUNT(*) FROM audit_logs WHERE action='donation.proof.viewed'")===1, 'private proof access recorded in audit log');
    $db->pdo()->exec('RENAME TABLE audit_logs TO audit_logs_qa_offline');
    try {
        try { callQa($router,$app,'GET','/admin/donations/1/proof'); throw new RuntimeException('Unaudited proof served'); }
        catch (PDOException $e) { qa(true, 'proof download fails closed without audit storage'); }
    } finally { $db->pdo()->exec('RENAME TABLE audit_logs_qa_offline TO audit_logs'); }
} finally { $db->update('donations',['payment_proof_path'=>null],'id=:id',['id'=>1]); unlink($proofFile); }

foreach (['/admin/reports/financial/pdf','/admin/reports/financial/csv','/admin/reports/financial/xlsx','/admin/donations/export','/admin/donations/1/proof'] as $url) {
    $auth->logout(); $auth->login($db->selectOne('SELECT * FROM users WHERE id=3'), '127.0.0.1');
    try { callQa($router, $app, 'GET', $url); throw new RuntimeException('Unprivileged download ' . $url); }
    catch (\App\Exceptions\HttpException $e) { qa($e->statusCode() === 403, 'unprivileged financial download denied: ' . $url); }
    $auth->logout(); $auth->login($db->selectOne('SELECT * FROM users WHERE id=1'), '127.0.0.1');
}
try { callQa($router,$app,'POST','/admin/email-queue/1/retry'); throw new RuntimeException('Missing CSRF accepted'); }
catch (\App\Exceptions\HttpException $e) { qa($e->statusCode() === 419, 'resend blocked without CSRF token'); }
// Safe capture transport; no external SMTP connection or real recipient is used.
$transport = new class implements TransportInterface {
    public array $messages = []; public bool $accept = true;
    public function send(string $toEmail,string $toName,string $subject,string $htmlBody,string $textBody): bool { $this->messages[] = [$toEmail,$subject,$htmlBody]; return $this->accept; }
    public function testConnection(): bool { return true; }
    public function describe(): string { return 'local capture only'; }
};
$queue = new MailQueue($db, $transport, $app->make(\App\Services\EmailTemplateService::class), $app->make(\App\Core\Logger::class), ['queue'=>['enabled'=>true,'max_retries'=>1]]);
$contact = $queue->enqueueRaw('contact-qa@example.invalid','Contact QA','Contact notification','<p>New inquiry</p>','New inquiry','forms.notification');
qa($contact !== null && $queue->processJob($contact) && count($transport->messages)===1, 'local capture delivers queued contact notification');
qa(!$queue->processJob($contact) && count($transport->messages)===1, 'already sent mail is not duplicated');
$volunteer = $queue->enqueueRaw('volunteer-qa@example.invalid','Volunteer QA','Volunteer acknowledgment','<p>Thank you</p>','Thank you','forms.volunteer_ack');
qa($volunteer !== null && $queue->processJob($volunteer), 'local capture delivers volunteer acknowledgment');
$failed = $queue->enqueueRaw('fail-qa@example.invalid','Fail QA','Failure QA','<p>Fail</p>','Fail'); $transport->accept = false;
qa(!$queue->processJob($failed) && $db->selectOne('SELECT status,last_error FROM email_jobs WHERE id=:id',['id'=>$failed])['status']==='failed', 'transport rejection logged as failed, never sent');
$transport->accept = true;
$stuck = $queue->enqueueRaw('stuck-qa@example.invalid','Stuck QA','Stuck QA','<p>Stuck</p>','Stuck');
$db->update('email_jobs',['status'=>'sending'],'id=:id',['id'=>$stuck]); $countBefore = count($transport->messages);
qa(!$queue->processJob($stuck) && count($transport->messages)===$countBefore, 'already-claimed sending job cannot be delivered twice');
$token = $csrf->token();
$retryUrl = '/admin/email-queue/' . $failed . '/retry';
$retry = callQa($router,$app,'POST',$retryUrl,['_token'=>$token]);
qa($retry->status()===302 && $db->selectOne('SELECT status FROM email_jobs WHERE id=:id',['id'=>$failed])['status']==='queued', 'authorized admin requeues only failed job');
callQa($router,$app,'POST','/admin/email-queue/'.$stuck.'/retry',['_token'=>$token]);
qa($db->selectOne('SELECT status FROM email_jobs WHERE id=:id',['id'=>$stuck])['status']==='sending', 'admin cannot requeue active in-flight email');
$db->insert('roles',['name'=>'content_manager','label'=>'Content manager']); $roleId = $db->int("SELECT id FROM roles WHERE name='content_manager'");
$db->insert('user_roles',['user_id'=>3,'role_id'=>$roleId]);
$db->insert('role_permissions',['role_id'=>$roleId,'permission_id'=>$db->int("SELECT id FROM permissions WHERE name='logs.view'")]);
$auth->logout(); $auth->login($db->selectOne('SELECT * FROM users WHERE id=3'), '127.0.0.1');
$token = $csrf->token();
try { callQa($router,$app,'POST',$retryUrl,['_token'=>$token]); throw new RuntimeException('Logs reader retried email'); }
catch (\App\Exceptions\HttpException $e) { qa($e->statusCode()===403,'logs.view alone cannot retry email'); }
// Existing records/relationships and migration-built indexes are checked on the actual disposable DB.
qa($db->int('SELECT COUNT(*) FROM donors WHERE id=1')===1 && $db->int('SELECT COUNT(*) FROM donations WHERE donor_id=1')>0, 'migrations and V2 inserts preserve fixture donors and donation FKs');
$indexes = $db->select("SELECT index_name FROM information_schema.statistics WHERE table_schema=DATABASE() AND table_name='donations'");
qa(in_array('donation_review_status_idx',array_column($indexes,'index_name'),true), 'verification lookup index exists after migrations');
qa($db->int("SELECT COUNT(*) FROM information_schema.table_constraints WHERE table_schema=DATABASE() AND table_name='donations' AND constraint_type='FOREIGN KEY'")>0, 'donation foreign keys exist');
// The older donation-ledger CSV must not silently drop records after row 5,000.
$auth->logout(); $auth->login($db->selectOne('SELECT * FROM users WHERE id=1'), '127.0.0.1');
$db->transaction(function () use ($db): void {
    $insert = $db->pdo()->prepare("INSERT INTO donations (public_reference,donor_id,amount_minor,currency,status,verification_status) VALUES (?,1,100,'PKR','pending','submitted')");
    for ($i=0; $i<5010; $i++) $insert->execute(['QA-BULK-' . str_pad((string)$i, 5, '0', STR_PAD_LEFT)]);
});
$ledger = callQa($router, $app, 'GET', '/admin/donations/export', ['q'=>'QA-BULK-']);
$ledgerPath = exportPathQa($ledger); $ledgerCsv = file_get_contents($ledgerPath);
qa($ledger->status()===200 && is_string($ledgerCsv) && substr_count($ledgerCsv,'QA-BULK-')===5010 && str_contains($ledgerCsv,'QA-BULK-05009'), 'older ledger CSV streams all 5,010 matches, preserving last row and filters');
qa($db->int("SELECT COUNT(*) FROM audit_logs WHERE action='donation.ledger.exported'")===1, 'older ledger CSV is now audited');
unlink($ledgerPath);
echo "$checks Part 7 security/consistency assertions passed.\n";
