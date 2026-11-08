<main class="container" style="max-width:800px;margin:35px auto;padding:24px">
  <h1>Correct a rejected transfer</h1>
  <?php if (error_for('form') !== ''): ?><p role="alert"><?= error_for('form') ?></p><?php endif; ?>
  <?php if (!empty($reason)): ?><p><strong>Reason given by our finance team:</strong> <?= e((string) $reason) ?></p><?php endif; ?>
  <form method="post" enctype="multipart/form-data" action="<?= e(base_url('donate/transfer/resubmit')) ?>">
    <?= csrf_field() ?>
    <p><label>Reference number * <input class="input" name="reference" maxlength="40" required value="<?= old('reference', $ref ?? '') ?>"></label></p>
    <p><label>Email used for the donation * <input class="input" type="email" name="donor_email" maxlength="191" required value="<?= old('donor_email') ?>"></label></p>
    <p><label>Currency * <select class="select-field" name="currency" required><?php foreach ($currencies as $code): ?><option value="<?= e($code) ?>"<?= old('currency', 'PKR') === $code ? ' selected' : '' ?>><?= e($code) ?></option><?php endforeach; ?></select></label></p>
    <p><label>Amount * <input class="input" type="number" step="0.01" min="1" name="amount" required value="<?= old('amount') ?>"></label></p>
    <p><label>Transaction date * <input class="input" type="date" name="received_at" max="<?= gmdate('Y-m-d') ?>" required value="<?= old('received_at', gmdate('Y-m-d')) ?>"></label></p>
    <p><label>Payment method * <select class="select-field" name="source"><option value="bank_transfer">Bank transfer</option><option value="online_transfer">Online transfer</option><option value="other">Other</option></select></label></p>
    <p><label>Bank account you paid into * <select class="select-field" name="bank_account_id" required><option value="">Select…</option>
      <?php foreach ($banks as $b): ?><option value="<?= (int) $b['id'] ?>"<?= (int) old('bank_account_id') === (int) $b['id'] ? ' selected' : '' ?>><?= e((string) $b['bank_name'] . ' – ' . (string) ($b['currency'] ?? 'PKR')) ?></option><?php endforeach; ?>
    </select></label></p>
    <p><label>Transaction reference <input class="input" name="bank_reference" maxlength="120" value="<?= old('bank_reference') ?>"></label></p>
    <p><label>New bank transfer / deposit slip * (PNG or JPG, maximum 2 MB) <input type="file" name="payment_proof" accept=".jpg,.jpeg,.png" required></label></p>
    <p><label>Notes <textarea class="input" name="note" maxlength="255"><?= old('note') ?></textarea></label></p>
    <button class="btn btn-primary" type="submit">Resubmit for verification</button>
  </form>
</main>
