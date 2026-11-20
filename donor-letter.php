<?php

declare(strict_types=1);

/**
 * Local-only form to produce the donor pack (receipt + thank-you/tax-exemption letter + approvals).
 * Run:  php -S localhost:8080 router.php   then open  http://localhost:8080/donor-letter.php
 * It refuses any request that does not come from this computer, so it is inert on the live website.
 */

if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
    http_response_code(404);
    exit('Not found');
}

require __DIR__ . '/app/Services/DonorLetterPdfService.php';

$h = static fn (string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
$error = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $p = array_map(static fn ($v) => is_string($v) ? trim($v) : '', $_POST);
    try {
        $pdf = (new App\Services\DonorLetterPdfService())->render([
            'kind' => $p['kind'] ?? 'donation',
            'donor_name' => $p['donor_name'] ?? '',
            'date' => $p['date'] ?? '',
            'donation_no' => $p['donation_no'] ?? '',
            'salutation' => $p['salutation'] ?? '',
            'contact' => $p['contact'] ?? '',
            'reference' => $p['reference'] ?? '',
            'amount' => $p['amount'] ?? '',
            'txn_no' => $p['txn_no'] ?? '',
            'purpose' => $p['purpose'] ?? '',
            'mode' => $p['mode'] ?? '',
            'inkind_items' => $p['inkind_items'] ?? '',
        ]);
        $safe = preg_replace('/[^A-Za-z0-9]+/', '-', $p['donor_name'] ?? 'donor') ?: 'donor';
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="Almarah-' . $safe . '.pdf"');
        echo $pdf;
        exit;
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}
?><!doctype html>
<html lang="en"><head><meta charset="utf-8"><title>Donor letter</title>
<style>body{font:15px system-ui,sans-serif;max-width:620px;margin:30px auto;padding:0 16px;color:#222}
label{display:block;margin:12px 0 4px;font-weight:600}input,select{width:100%;padding:8px;box-sizing:border-box;border:1px solid #bbb;border-radius:6px}
button{margin-top:18px;padding:10px 20px;background:#96193e;color:#fff;border:0;border-radius:6px;font-size:15px;cursor:pointer}
.err{background:#fde8e8;padding:10px;border-radius:6px}small{color:#666}</style></head><body>
<h2>Almarah Foundation - donor receipt &amp; letter</h2>
<?php if ($error !== '') { echo '<p class="err">' . $h($error) . '</p>'; } ?>
<form method="post" target="_blank">
<label>Type</label><select name="kind"><option value="donation">Donation (cash / online / cheque)</option><option value="inkind">Donation in kind</option></select>
<label>Donor name *</label><input name="donor_name" required maxlength="80">
<p><small>Everything below is optional. Empty fields are simply left off the receipt.</small></p>
<label>Date</label><input name="date" value="<?= $h(date('d-m-Y')) ?>">
<label>Donation No.</label><input name="donation_no">
<label>Word after the name in the letter (optional, e.g. Bhai)</label><input name="salutation" value="">
<label>Contact No.</label><input name="contact">
<label>Reference by (person who referred the donor)</label><input name="reference">
<label>Amount (PKR, numbers only - words are filled in automatically)</label><input name="amount" inputmode="numeric">
<label>Slip / Cheque / Transaction No. (donation type only)</label><input name="txn_no">
<label>Donation purpose</label><input name="purpose">
<label>Payment mode (donation type only)</label><select name="mode"><option value="">- not shown -</option><option value="cash">Cash</option><option value="online">Online Transfer</option><option value="cheque">Cheque</option></select>
<label>Items received (in-kind type only)</label><input name="inkind_items" placeholder="e.g. 40 hoodies (clothes for kids)">
<button>Create PDF</button></form></body></html>
