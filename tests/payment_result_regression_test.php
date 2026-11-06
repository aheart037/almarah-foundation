<?php
declare(strict_types=1);

// Standalone regression harness: no credentials, database, mail or bank calls.
namespace App\Core {
    class Config { public static function get(string $key, mixed $default = null): mixed { return $default; } }
    class Logger { public array $events = []; public function payment(string $message, array $context = []): void { $this->events[] = $message; } }
}
namespace App\Repositories {
    class DonationRepository {
        public array $row;
        public function updateStatus(int $id, string $status, ?string $at = null): void { $this->row['status'] = $status; }
        public function find(int $id): array { return $this->row; }
        public function setReceiptStatus(int $id, string $status): void { $this->row['receipt_email_status'] = $status; }
    }
    class PaymentRepository {
        public array $row = [];
        public function updateTransaction(int $id, array $data): void { $this->row = array_merge($this->row, $data); }
    }
    class FundraiserRepository { public function find(int $id): ?array { return null; } }
}
namespace App\Mail {
    class Mailer {
        public int $attempts = 0;
        public function donationReceipt(...$args): void { $this->attempts++; throw new \RuntimeException('Simulated mail failure'); }
        public function donationFailed(...$args): void { throw new \RuntimeException('Simulated failure email error'); }
    }
}
namespace App\Services { class AuditService { public function log(...$args): void {} } }
namespace {
    require __DIR__ . '/../app/Payments/PaymentStatusMapper.php';
    require __DIR__ . '/../app/Payments/Dtos/PaymentVerificationResult.php';
    require __DIR__ . '/../app/Services/PaymentStatusService.php';
    function check(bool $ok, string $message): void { if (!$ok) throw new RuntimeException($message); echo "PASS: $message\n"; }
    function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
    function base_url(string $s = ''): string { return '/' . $s; }
    function money(int $n, string $c): string { return $c . ' ' . number_format($n / 100, 2); }
    function dt(string $s, string $f): string { return date($f, strtotime($s)); }
    function status_badge_class(string $s): string { return 'badge'; }
    function can_fundraiser_capability(string $s): bool { return false; }

    $d = new App\Repositories\DonationRepository();
    $d->row = ['id'=>1, 'status'=>'processing', 'public_reference'=>'ALM-TEST', 'amount_minor'=>500000,
        'currency'=>'PKR', 'donor_email'=>'donor@example.test', 'gateway_id'=>1, 'gateway_code'=>'etisalat',
        'verification_status'=>'legacy', 'created_at'=>'2026-11-07 10:00:00', 'purpose_name'=>'Education'];
    $p = new App\Repositories\PaymentRepository();
    $mailer = new App\Mail\Mailer();
    $logger = new App\Core\Logger();
    $service = new App\Services\PaymentStatusService($d, $p, new App\Repositories\FundraiserRepository(),
        new App\Payments\PaymentStatusMapper(), $mailer, new App\Services\AuditService(), $logger);
    $tx = ['id'=>2, 'donation_id'=>1, 'gateway_code'=>'etisalat', 'provider_transaction_id'=>'BANK-TEST', 'provider_order_id'=>'0001'];
    $result = new App\Payments\Dtos\PaymentVerificationResult('etisalat', 'completed', true, providerCode: '0');
    $applied = $service->applyVerifiedResult($tx, $d->row, $result);
    check($applied['status']==='completed' && $d->row['status']==='completed' && $p->row['status']==='completed', 'Mail failure does not undo confirmed payment');
    check(in_array('Payment saved; notification failed', $logger->events, true), 'Notification failure is logged');
    $service->applyVerifiedResult($tx, $d->row, $result);
    check($mailer->attempts===1, 'Duplicate completion does not repeat receipt');
    $d->row['status']='processing';
    $applied=$service->applyVerifiedResult($tx, $d->row, new App\Payments\Dtos\PaymentVerificationResult('etisalat','processing',false));
    check($applied['status']==='processing' && $d->row['status']==='processing', 'Unverified bank response stays processing');
    $applied=$service->applyVerifiedResult($tx, $d->row, new App\Payments\Dtos\PaymentVerificationResult('etisalat','failed',true));
    check($applied['status']==='failed' && $p->row['status']==='failed', 'Verified decline updates donation and transaction');

    function renderResult(array $donation, array $transactions, string $outcome): string {
        $prDonation=$donation; $prTransactions=$transactions; $prOutcome=$outcome;
        $prReference=$donation['public_reference']; $prRetryUrl='/donate'; $prStatusUrl='/donation/ALM-TEST';
        ob_start(); require __DIR__ . '/../resources/views/partials/payment-result.php'; return ob_get_clean();
    }
    $d->row['status']='completed';
    $confirmed=$tx+['status'=>'completed','finalized_at'=>'2026-11-07 10:01:00'];
    $html=renderResult($d->row, [$confirmed], 'completed');
    check(str_contains($html,'Payment Successful') && !str_contains($html,'Verification not established'), 'Finalized gateway payment shows success before staff review');
    check(str_contains($html,'awaiting staff approval') && !str_contains($html,'data-print'), 'Staff approval remains required for official receipt');
    check(str_contains(renderResult($d->row, [], 'completed'),'Verification not established'), 'Historical completed row alone cannot claim bank confirmation');
    $wrong=$confirmed; $wrong['donation_id']=99;
    check(str_contains(renderResult($d->row, [$wrong], 'completed'),'Verification not established'), 'Another donation transaction cannot establish success');
    $d->row['status']='processing';
    check(str_contains(renderResult($d->row, [], 'processing'),'data-pr-poll'), 'Processing payment offers automatic status check');
    $d->row['gateway_id']=null;
    check(!str_contains(renderResult($d->row, [], 'processing'),'data-pr-poll'), 'Offline review does not poll the bank');
    if (isset($argv[1])) file_put_contents($argv[1], '<!doctype html><html lang="en"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="../assets/css/site.css"><link rel="stylesheet" href="../assets/css/payment-result.css"><body>'.$html.'</body></html>');
}
