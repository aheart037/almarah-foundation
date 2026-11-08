<main class="container" style="max-width:800px;margin:35px auto;padding:24px">
  <h1>Report a donation or transfer</h1>
  <p>Submit your payment details for review. A screenshot or bank reference alone is not proof of receipt; we will confirm the funds before issuing an official receipt. To pay securely by card, use <a href="<?= e(base_url('donate')) ?>">Donate Now</a>.</p>
  <?php if ($msg = flash('success')): ?><p role="status"><?= e($msg) ?></p><?php endif; ?>
  <?php if (error_for('form') !== ''): ?><p role="alert"><?= error_for('form') ?></p><?php endif; ?>
  <form method="post" enctype="multipart/form-data" action="<?= e(base_url('donate/transfer')) ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="transfer_attempt" value="<?= e($transferNonce) ?>">
    <label style="display:none">Website <input name="website" autocomplete="off"></label>
    <p><label>Full name * <input class="input" name="donor_name" maxlength="150" required value="<?= old('donor_name') ?>"></label></p>
    <p><label>Email * <input class="input" type="email" name="donor_email" maxlength="191" required value="<?= old('donor_email') ?>"></label></p>
    <p><label>Mobile/WhatsApp * <input class="input" name="donor_phone" maxlength="40" required value="<?= old('donor_phone') ?>"></label></p>
    <p><label for="transferCurrency">Currency *</label> <select class="select-field" id="transferCurrency" name="currency" required>
      <?php foreach ($currencies as $code): ?><option value="<?= e($code) ?>"<?= old('currency', $selectedCurrency) === $code ? ' selected' : '' ?>><?= e(($currencyOptions[$code]['flag'] ?? '') . ' ' . ($currencyOptions[$code]['name'] ?? $code) . ' – ' . $code . ' (' . ($currencyOptions[$code]['symbol'] ?? $code) . ')') ?></option><?php endforeach; ?>
    </select></p>
    <p><label>Amount in selected currency * <input class="input" type="number" inputmode="decimal" step="0.01" min="1" max="5000000" name="amount" required value="<?= old('amount') ?>"></label></p>
    <p><label>Transaction date * <input class="input" type="date" name="received_at" max="<?= gmdate('Y-m-d') ?>" required value="<?= old('received_at', gmdate('Y-m-d')) ?>"></label></p>
    <p><label>Payment method * <select class="select-field" name="source"><option value="bank_transfer">Bank transfer</option><option value="online_transfer">Online transfer</option><option value="other">Other</option></select></label></p>
    <p><label>Transaction reference (if provided) <input class="input" name="bank_reference" maxlength="120" value="<?= old('bank_reference') ?>"></label></p>
    <p><label>Bank account you paid into * <select class="select-field" name="bank_account_id" required><option value="">Select…</option>
      <?php foreach ($banks as $b): ?><option value="<?= (int) $b['id'] ?>"<?= (int) old('bank_account_id') === (int) $b['id'] ? ' selected' : '' ?>><?= e((string) $b['bank_name'] . ' – ' . (string) ($b['currency'] ?? 'PKR') . ' – …' . substr((string) ($b['account_number'] ?? $b['iban'] ?? ''), -4)) ?></option><?php endforeach; ?>
    </select></label></p>
    <p><label>Bank transfer / deposit slip * (PNG or JPG, maximum 2 MB) <input type="file" name="payment_proof" accept=".jpg,.jpeg,.png" required></label></p>
    <p><label>Additional notes <textarea class="input" name="note" maxlength="255"><?= old('note') ?></textarea></label></p>
    <?php require __DIR__ . '/../../partials/donation-extra-fields.php'; ?>
    <button class="btn btn-primary" type="submit">Submit for review</button>
    <p><small>Reporting a transfer does not confirm receipt. Staff verify the funds before recording a completed donation.</small></p>
  </form>
  <p><small>Was your report rejected? <a href="<?= e(base_url('donate/transfer/resubmit')) ?>">Correct and resubmit it</a>.</small></p>
</main>

<script>document.querySelector('form[action$="donate/transfer"]').addEventListener('submit',function(){if(!this.reportValidity())return;var button=this.querySelector('button[type="submit"]');button.disabled=true;button.textContent='Sending report…';});</script>
