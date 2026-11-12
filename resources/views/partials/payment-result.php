<?php
/**
 * Payment result page body (v19) - shared by both gateways.
 *
 * Used by public/donate/response.php (the page the bank sends the donor back
 * to: Meezan and UBL/Etisalat) and public/donate/status.php (the receipt page
 * reached through /donation/{reference}). One component, so the two banks and
 * both entry points always look and read the same.
 *
 * The outcome is never taken from the browser. Callers pass what the server
 * verified; this file only presents it.
 *
 * Expected variables:
 * @var string      $prOutcome       completed | processing | failed | refunded
 * @var string      $prReason        cancelled | expired | failed | unknown (failed only)
 * @var array|null  $prDonation      donation row (null = not found)
 * @var string      $prReference     public donation reference
 * @var string      $prRetryUrl
 * @var string      $prStatusUrl
 * @var array       $prTransactions  payment records for the donation (optional)
 * @var string|null $prNotice        optional note shown above the receipt
 * @var string|null $prTitle         heading override (used with outcome "notfound")
 * @var string|null $prLead          lead text override (used with outcome "notfound")
 */

use App\Core\Config;

$prOutcome      = (string) ($prOutcome ?? 'failed');
$prReason       = (string) ($prReason ?? '');
$prDonation     = is_array($prDonation ?? null) ? $prDonation : null;
$prReference    = (string) ($prReference ?? ($prDonation['public_reference'] ?? ''));
$prRetryUrl     = (string) ($prRetryUrl ?? base_url('donate'));
$prStatusUrl    = (string) ($prStatusUrl ?? base_url('donate'));
$prTransactions = is_array($prTransactions ?? null) ? $prTransactions : [];
$prNotice       = isset($prNotice) ? (string) $prNotice : '';
$org            = (array) Config::get('app.org', []);
$bank           = (array) ($org['bank'] ?? []);
$orgEmail       = (string) ($org['email'] ?? '');
$orgPhone       = (string) ($org['phone'] ?? '');

// ---- facts about the donation --------------------------------------------
$currency   = (string) ($prDonation['currency'] ?? 'PKR');
$amountText = $prDonation !== null ? money((int) $prDonation['amount_minor'], $currency) : '';
preg_match('/^(\D*?)\s*([\d,.]+.*)$/u', $amountText, $amountParts);
$amountCur  = trim((string) ($amountParts[1] ?? ''));
$amountNum  = (string) ($amountParts[2] ?? $amountText);
// Bank settlement and staff approval of an official receipt are separate.
// Only server-stored, finalized transactions can establish gateway success.
$bankConfirmed = false;
foreach ($prTransactions as $tx) {
    if ($prDonation !== null && !empty($prDonation['gateway_id'])
        && (int) ($tx['donation_id'] ?? 0) === (int) $prDonation['id']
        && (string) ($tx['status'] ?? '') === 'completed'
        && !empty($tx['finalized_at'])) {
        $bankConfirmed = true;
        break;
    }
}
$staffVerified = (string) ($prDonation['verification_status'] ?? '') === 'verified';
$paymentConfirmed = $prOutcome === 'completed' && ($bankConfirmed || $staffVerified);
$legacyUnverified = $prOutcome === 'completed' && !$paymentConfirmed;
$offlinePending = $prOutcome === 'processing' && $prDonation !== null && empty($prDonation['gateway_id']);
$officialReceipt = $prOutcome === 'completed' && $staffVerified;
$receiptSent = $officialReceipt && ($prDonation['receipt_email_status'] ?? '') === 'sent';
$countable  = $paymentConfirmed && preg_match('/^\d[\d,]*$/', $amountNum) === 1;
$target     = (string) ($prDonation['fundraiser_title'] ?? '');
$targetText = $target !== '' ? $target : (string) ($prDonation['purpose_name'] ?? 'Almarah Foundation general fund');
$anonymous  = !empty($prDonation['anonymous']);
// This public status page is opened with a reference, not a logged-in identity.
// Do not disclose the donor's full name to someone who guesses that reference.
$rawDonorName = trim((string) ($prDonation['donor_name'] ?? ''));
$donorName  = $anonymous ? 'Anonymous' : ($rawDonorName !== '' ? mb_substr($rawDonorName, 0, 1) . '•••' : '');
$slug       = (string) ($prDonation['fundraiser_slug'] ?? '');
$fundUrl    = base_url('donate'); // Main-site checkout is /donate, not /fundraisers.

// Receipts go to the donor's own address; show it masked, because anyone
// holding the reference can open this page.
$maskEmail = static function (string $email): string {
    if (!str_contains($email, '@')) {
        return 'your email address';
    }
    [$local, $domain] = explode('@', $email, 2);

    return mb_substr($local, 0, 1) . str_repeat('•', max(2, min(6, mb_strlen($local) - 1))) . '@' . $domain;
};
$emailMasked = $maskEmail((string) ($prDonation['donor_email'] ?? ''));

// Which bank was used (both gateways are presented the same way).
$gatewayCode = strtolower((string) ($prDonation['gateway_code'] ?? ''));
$gatewayName = (string) ($prDonation['gateway_name'] ?? '');
if ($gatewayName === '') {
    $gatewayName = $offlinePending ? 'Transfer reported' : ($gatewayCode === 'meezan' ? 'Meezan Bank' : 'Card payment');
}
$gatewayIcon = $gatewayCode === 'meezan'
    ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10l9-6 9 6"/><path d="M5 10v8M9.5 10v8M14.5 10v8M19 10v8"/><path d="M3 20h18"/></svg>'
    : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="2.5" y="5" width="19" height="14" rx="2.5"/><path d="M2.5 10h19M6 15h4"/></svg>';

// ---- how each outcome is presented ---------------------------------------
$failText = match ($prReason) {
    'cancelled' => 'The payment was cancelled before it finished, so no money has been taken.',
    'expired'   => 'This payment session expired before the bank could confirm it. If your bank shows a debit, contact us with your donation reference and we will trace it.',
    default     => 'The bank could not complete this payment, so your donation has not been counted. If your bank shows a debit, contact us with your donation reference and we will trace it.',
};
// Only display a bank error saved by verification, never a URL error string.
if ($prOutcome === 'failed') {
    foreach ($prTransactions as $tx) {
        if (in_array((string) ($tx['status'] ?? ''), ['failed', 'cancelled', 'abandoned'], true)) {
            $bankError = trim((string) ($tx['provider_response_description'] ?? ''));
            if ($bankError !== '') {
                $failText .= ' Bank message: ' . $bankError;
            }
            break;
        }
    }
}
$view = match ($prOutcome) {
    'notfound'   => ['tone' => 'bad',  'kicker' => 'Not found', 'title' => (string) ($prTitle ?? 'We could not find that donation'),
                     'lead' => (string) ($prLead ?? 'Check the link in your receipt email, or contact our team with the reference from your bank statement.')],
    'completed'  => $legacyUnverified
        ? ['tone' => 'wait', 'kicker' => 'Historical record', 'title' => 'Verification not established',
           'lead' => 'This historical donation is marked completed, but this record does not establish independent verification. Contact our team before relying on it as a verified payment.']
        : ['tone' => 'ok', 'kicker' => 'Payment Successful', 'title' => 'Thank you for your donation!',
           'lead' => 'Your payment was successful, and your donation has been confirmed. JazakAllah Khair for your support.' . ($staffVerified ? '' : ' A thank-you email is on its way, and your tax exemption certificate will be emailed to you once our finance team has verified your donation.')],
    'processing' => $offlinePending
        ? ['tone' => 'wait', 'kicker' => 'Under review', 'title' => 'Transfer report received',
           'lead' => 'Your report is pending staff verification. The funds have not been confirmed and this is not a receipt. Keep your reference safe.']
        : ['tone' => 'wait', 'kicker' => 'Almost there', 'title' => 'Confirming your payment',
           'lead' => 'The bank has not given us a final answer yet, so your donation is not counted until it does. This usually settles within a few minutes. Please do not pay again.'],
    'refunded'   => ['tone' => 'info', 'kicker' => 'Refunded', 'title' => 'This donation was refunded',
                     'lead' => 'We have refunded this donation to your original payment method. It can take a few working days to show on your statement.'],
    default      => ['tone' => 'bad',  'kicker' => 'Payment Failed', 'title' => 'That payment did not go through',
                     'lead' => $failText],
};

$icons = [
    'ok'   => '<svg viewBox="0 0 52 52" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><circle class="pr-ring" cx="26" cy="26" r="22"/><path class="pr-tick" d="M15 27l8 8 14-16"/></svg>',
    'wait' => '<svg viewBox="0 0 52 52" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><circle class="pr-ring" cx="26" cy="26" r="22"/><path class="pr-hand" d="M26 13v13"/><path class="pr-hand pr-hand-s" d="M26 26l9 5"/></svg>',
    'bad'  => '<svg viewBox="0 0 52 52" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><circle class="pr-ring" cx="26" cy="26" r="22"/><path class="pr-tick" d="M18 18l16 16M34 18L18 34"/></svg>',
    'info' => '<svg viewBox="0 0 52 52" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><circle class="pr-ring" cx="26" cy="26" r="22"/><path class="pr-tick" d="M26 24v13M26 16h.01"/></svg>',
];

// Progress steps: done | now | todo | fail
$step2 = $legacyUnverified ? 'now' : match ($prOutcome) { 'completed', 'refunded' => 'done', 'processing' => 'now', default => 'fail' };
$step3 = $legacyUnverified ? 'todo' : match ($prOutcome) { 'completed' => $receiptSent ? 'done' : 'now', 'refunded' => 'done', default => 'todo' };
$steps = [
    [$offlinePending ? 'now' : 'done', $offlinePending ? 'Transfer reported' : 'Payment sent', $offlinePending ? 'Waiting for staff review' : 'Payment method: ' . $gatewayName],
    [$step2, $offlinePending ? 'Staff verification' : 'Bank confirmation', $legacyUnverified ? 'Not independently verified' : ($prOutcome === 'completed' ? 'Verified by the bank' : ($prOutcome === 'processing' ? ($offlinePending ? 'Waiting for staff' : 'Waiting for the bank') : 'Not confirmed'))],
    [$step3,  'Receipt and impact', $receiptSent ? 'Receipt emailed' : ($legacyUnverified ? 'Verification required' : ($prOutcome === 'completed' ? 'Certificate after verification' : 'After confirmation'))],
];

$shareText = 'I just donated to ' . $targetText . ' with Almarah Foundation. Please join me: ' . $fundUrl;
$waUrl = 'https://wa.me/?text=' . rawurlencode($shareText);
?>
<section class="section pr-page" data-pr="<?= e($prOutcome) ?>" data-pr-ref="<?= e($prReference) ?>"
         <?php if ($prOutcome === 'processing' && !$offlinePending && $prReference !== ''): ?>data-pr-poll="<?= e($prStatusUrl) ?>"<?php endif; ?>>
  <div class="wrap wrap-narrow">

    <!-- HERO -->
    <div class="pr-hero pr-<?= e($view['tone']) ?>">
      <div class="pr-band" aria-hidden="true"><i></i><i></i><i></i></div>
      <div class="pr-icon"><?= $icons[$view['tone']] ?></div>
      <p class="pr-kicker"><?= e($view['kicker']) ?></p>
      <h1><?= e($view['title']) ?></h1>
      <?php if ($paymentConfirmed): ?>
        <p class="pr-arabic" lang="ar" dir="rtl">جزاك الله خيرًا</p>
      <?php endif; ?>

      <?php if ($prDonation !== null && $amountText !== ''): ?>
        <div class="pr-amount" aria-label="Amount <?= e($amountText) ?>">
          <?php if ($amountCur !== ''): ?><span class="pr-cur"><?= e($amountCur) ?></span><?php endif; ?>
          <span class="pr-num"<?= $countable ? ' data-count="' . e((string) (int) str_replace(',', '', $amountNum)) . '"' : '' ?>><?= e($amountNum) ?></span>
        </div>
        <p class="pr-to"><?= $paymentConfirmed ? 'given to' : 'for' ?> <b><?= e($targetText) ?></b></p>
      <?php endif; ?>

      <p class="pr-lead"><?= e($view['lead']) ?></p>

      <div class="pr-chips"<?= $prOutcome === 'notfound' ? ' hidden' : '' ?>>
        <?php if ($prDonation !== null): ?><span class="pr-chip"><?= $gatewayIcon ?><?= e($gatewayName) ?></span><?php endif; ?>
        <span class="pr-chip"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg><?= $legacyUnverified ? 'Historical record – unverified' : ($offlinePending ? 'Transfer report – unverified' : 'Secure payment') ?></span>
        <?php if ($prOutcome === 'failed'): ?><span class="pr-chip pr-chip-good"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>No donation counted</span><?php endif; ?>
      </div>

      <?php if ($prOutcome === 'processing' && !$offlinePending && $prReference !== ''): ?>
        <p class="pr-poll" id="prPoll" aria-live="polite">Checking again in <b id="prCountdown">20</b> seconds&hellip;</p>
      <?php endif; ?>
    </div>

    <!-- STEPS -->
    <?php if ($prOutcome !== 'notfound'): ?>
    <ol class="pr-steps" aria-label="Payment progress">
      <?php foreach ($steps as $i => [$state, $label, $sub]): ?>
        <li class="is-<?= e($state) ?>" style="--i:<?= (int) $i ?>">
          <span class="pr-dot"><?php if ($state === 'done'): ?><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><?php elseif ($state === 'fail'): ?><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg><?php else: ?><?= (int) $i + 1 ?><?php endif; ?></span>
          <span class="pr-step-t"><b><?= e($label) ?></b><small><?= e($sub) ?></small></span>
        </li>
      <?php endforeach; ?>
    </ol>
    <?php endif; ?>

    <?php if ($prNotice !== ''): ?>
      <div class="alert alert-info"><p><?= e($prNotice) ?></p></div>
    <?php endif; ?>

    <?php if ($prDonation === null): ?>
      <div class="pr-card pr-center">
        <div class="pr-actions">
          <a class="btn btn-brand" href="<?= e(base_url('donate')) ?>">Return to donation page</a>
          <a class="btn btn-outline-brand" href="<?= e(base_url('support')) ?>">Contact support</a>
        </div>
      </div>
    <?php else: ?>

    <div class="pr-grid">
      <!-- RECEIPT -->
      <div class="pr-card pr-receipt">
        <div class="pr-receipt-head">
          <h2><?= $officialReceipt ? 'Donation receipt' : 'Donation details' ?></h2>
          <span class="<?= e(status_badge_class((string) $prDonation['status'])) ?>"><?= e(str_replace('_', ' ', (string) $prDonation['status'])) ?></span>
        </div>
        <dl class="pr-rows">
          <div><dt>Reference</dt><dd class="mono"><?= e($prReference) ?>
            <button class="pr-copy" type="button" data-copy="<?= e($prReference) ?>" aria-label="Copy reference">Copy</button></dd></div>
          <div><dt><?= $legacyUnverified ? 'Recorded amount' : ($prOutcome === 'completed' ? 'Amount paid' : 'Amount') ?></dt><dd><b><?= e($amountText) ?></b></dd></div>
          <div><dt>Giving to</dt><dd><?= e($targetText) ?></dd></div>
          <?php if ($donorName !== ''): ?><div><dt>Donor</dt><dd><?= e($donorName) ?></dd></div><?php endif; ?>
          <div><dt>Payment method</dt><dd><?= e($gatewayName) ?></dd></div>
          <?php // Provider transaction identifiers remain visible only to authorized staff. ?>
          <div><dt>Date</dt><dd><?= e(dt((string) $prDonation['created_at'], 'j F Y, H:i')) ?> UTC</dd></div>
          <?php if (!empty($prDonation['completed_at'])): ?><div><dt><?= $legacyUnverified ? 'Recorded completed' : 'Confirmed' ?></dt><dd><?= e(dt((string) $prDonation['completed_at'], 'j F Y, H:i')) ?> UTC</dd></div><?php endif; ?>
          <?php // Donor messages are private and must not appear on reference-only public pages. ?>
        </dl>
        <?php if ($prOutcome === 'completed'): ?>
          <?php if ($legacyUnverified): ?>
            <p class="pr-note">No verified receipt is available for this historical record. Please contact <a href="mailto:<?= e($orgEmail) ?>"><?= e($orgEmail) ?></a> for help.</p>
          <?php elseif (!$officialReceipt): ?>
            <p class="pr-note">Your payment is confirmed. Your official receipt and tax exemption certificate are awaiting staff approval: our finance team is verifying your donation, and we will email your certificate as soon as that is done. Keep this reference for your records.</p>
          <?php else: ?>
            <p class="pr-note"><?= $receiptSent ? 'An authorized receipt has been emailed to' : 'Your authorized receipt is being prepared for' ?> <b><?= e($emailMasked) ?></b>.</p>
          <?php endif; ?>
        <?php elseif ($prOutcome === 'processing'): ?>
          <p class="pr-note"><?= $offlinePending ? 'Keep this reference safe while staff verify the transfer.' : 'Keep this reference safe. We will email you the moment the bank confirms.' ?></p>
        <?php endif; ?>
        <p class="pr-note pr-print-only">Almarah Foundation &middot; <?= e($orgEmail) ?> &middot; <?= e($orgPhone) ?></p>
        <div class="pr-actions pr-noprint">
          <?php if ($officialReceipt): ?>
            <button class="btn btn-brand btn-sm" type="button" data-print>Print or save receipt</button>
          <?php endif; ?>
          <a class="btn btn-light btn-sm" href="<?= e($prStatusUrl) ?>"><?= $prOutcome === 'processing' ? 'Check status now' : 'View live status' ?></a>
        </div>
      </div>

      <!-- SIDE -->
      <aside class="pr-side pr-noprint">
        <?php if ($paymentConfirmed): ?>
          <div class="pr-card">
            <h3>Multiply your gift</h3>
            <p class="muted">One message can bring in the next donor. Tell someone why you gave.</p>
            <div class="pr-share">
              <a class="pr-share-btn wa" href="<?= e($waUrl) ?>" target="_blank" rel="noopener noreferrer">WhatsApp</a>
              <button class="pr-share-btn" type="button" data-copy="<?= e($fundUrl) ?>">Copy link</button>
            </div>
          </div>
          <div class="pr-card">
            <h3>What next?</h3>
            <div class="pr-links">
                  <a href="<?= e(base_url('donate')) ?>">Donate again <span>&rarr;</span></a>
              <?php if (can_fundraiser_capability('manage_pages')): ?><a href="<?= e(base_url('register')) ?>">Start your own fundraiser <span>&rarr;</span></a><?php endif; ?>
            </div>
          </div>
        <?php elseif ($prOutcome === 'failed' || $prOutcome === 'refunded'): ?>
          <div class="pr-card">
            <h3><?= $prOutcome === 'failed' ? 'Try again' : 'Give again' ?></h3>
            <p class="muted">A new attempt takes less than a minute, with the same or a different method.</p>
            <a class="btn btn-brand btn-block" href="<?= e($prRetryUrl) ?>"><?= $prOutcome === 'failed' ? 'Try again' : 'Donate again' ?></a>
          </div>
        <?php else: ?>
          <div class="pr-card">
            <h3>What is happening?</h3>
            <p class="muted"><?= $legacyUnverified ? 'This historical record is not independently verified; please contact us for details.' : ($offlinePending ? 'Staff will confirm receipt of the transfer before any official acknowledgment is issued.' : 'Banks sometimes take a few minutes to confirm a card payment. This page checks for you automatically, and we email you when it clears.') ?></p>
          </div>
        <?php endif; ?>

        <div class="pr-card pr-help">
          <h3><?= $prOutcome === 'failed' ? 'Need help?' : 'Questions?' ?></h3>
          <p class="muted">Quote your reference <span class="mono"><?= e($prReference) ?></span>.</p>
          <?php if ($orgPhone !== ''): ?><a href="tel:<?= e(preg_replace('/[^\d+]/', '', $orgPhone) ?? '') ?>"><?= e($orgPhone) ?></a><?php endif; ?>
          <?php if ($orgEmail !== ''): ?><a href="mailto:<?= e($orgEmail) ?>"><?= e($orgEmail) ?></a><?php endif; ?>
        </div>
      </aside>
    </div>

    <?php if ($prOutcome === 'failed' && $currency === 'PKR' && $bank !== []): ?>
      <details class="pr-card pr-bank pr-noprint">
        <summary>Prefer a bank transfer? <span>No donation has been counted; if debited, contact us.</span></summary>
        <dl class="pr-rows">
          <div><dt>Bank</dt><dd><?= e((string) ($bank['name'] ?? '')) ?></dd></div>
          <div><dt>Account title</dt><dd><?= e((string) ($bank['title'] ?? '')) ?></dd></div>
          <div><dt>Account no.</dt><dd class="mono"><?= e((string) ($bank['account'] ?? '')) ?></dd></div>
          <div><dt>IBAN</dt><dd class="mono"><?= e((string) ($bank['iban'] ?? '')) ?>
            <button class="pr-copy" type="button" data-copy="<?= e((string) ($bank['iban'] ?? '')) ?>" aria-label="Copy IBAN">Copy</button></dd></div>
          <div><dt>Branch</dt><dd><?= e((string) ($bank['branch'] ?? '')) ?></dd></div>
          <div><dt>SWIFT</dt><dd class="mono"><?= e((string) ($bank['swift'] ?? '')) ?></dd></div>
        </dl>
      </details>
    <?php endif; ?>

    <?php endif; ?>
  </div>
</section>
