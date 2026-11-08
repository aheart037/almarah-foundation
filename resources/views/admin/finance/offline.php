<?php
/**
 * Record a donation received outside the website.
 * @var array $purposes @var array $sources @var array $banks
 */
?>
<div class="dash-head">
  <div>
    <h1>Record an offline donation</h1>
    <p class="sub">Bank credits, cash, cheques and Zelle transfers. Saved as <strong>pending</strong> until independently reviewed and verified.</p>
  </div>
  <a class="btn btn-light" href="<?= e(base_url('admin/finance')) ?>">Back to finance desk</a>
</div>

<form method="post" action="<?= e(base_url('admin/finance/offline')) ?>" class="panel" novalidate enctype="multipart/form-data">
  <?= csrf_field() ?>
  <?php if (error_for('form') !== ''): ?><div class="alert alert-error"><?= error_for('form') ?></div><?php endif; ?>

  <div class="form-row">
    <div class="field">
      <label for="amount">Amount<span class="req">*</span></label>
      <input class="input" type="number" step="0.01" min="1" id="amount" name="amount" required value="<?= old('amount') ?>" placeholder="5000.00">
    </div>
    <div class="field">
      <label for="currency">Currency</label>
      <select class="select-field" id="currency" name="currency">
        <?php foreach (['PKR', 'USD', 'GBP', 'EUR', 'AED', 'SAR', 'CAD', 'AUD'] as $c): ?>
          <option value="<?= $c ?>" <?= old('currency', 'PKR') === $c ? 'selected' : '' ?>><?= $c ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <div class="form-row">
    <div class="field">
      <label for="source">Received via<span class="req">*</span></label>
      <select class="select-field" id="source" name="source">
        <?php foreach ($sources as $k => $label): ?>
          <option value="<?= e($k) ?>" <?= old('source', 'bank_transfer') === $k ? 'selected' : '' ?>><?= e($label) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field">
      <label for="received_at">Date received</label>
      <input class="input" type="date" id="received_at" name="received_at" required value="<?= old('received_at', gmdate('Y-m-d')) ?>" max="<?= e(gmdate('Y-m-d')) ?>">
    </div>
  </div>

  <div class="form-row">
    <div class="field">
      <label for="bank_reference">Bank / transaction reference</label>
      <input class="input" id="bank_reference" name="bank_reference" maxlength="120" value="<?= old('bank_reference') ?>" placeholder="e.g. Meezan 02160113593207 · FT24ABC123">
      <small class="muted">For cash, enter the bank deposit slip number.</small>
    </div>
    <div class="field">
      <label for="bank_account_id">Bank account<span class="req">*</span></label>
      <select class="select-field" id="bank_account_id" name="bank_account_id" required>
        <option value="">Select…</option>
        <?php foreach ($banks as $b): ?><option value="<?= (int) $b['id'] ?>" <?= (int) old('bank_account_id') === (int) $b['id'] ? 'selected' : '' ?>><?= e((string) $b['bank_name'] . ' – ' . (string) $b['currency'] . ' – …' . substr((string) $b['account_number'], -4)) ?></option><?php endforeach; ?>
      </select>
      <small class="muted">Cash: the account it was deposited into. Currency must match.</small>
    </div>
    <div class="field">
      <label for="deposited_at">Cash deposit date</label>
      <input class="input" type="date" id="deposited_at" name="deposited_at" max="<?= gmdate('Y-m-d') ?>" value="<?= e(old('deposited_at')) ?>">
      <small class="muted">Required when received via Cash (collected and deposited in the same entry).</small>
    </div>
    <div class="field">
      <label for="purpose_id">Purpose</label>
      <select class="select-field" id="purpose_id" name="purpose_id">
        <option value="">General Fund</option>
        <?php foreach ($purposes as $p): ?>
          <option value="<?= (int) $p['id'] ?>" <?= (int) old('purpose_id', '0') === (int) $p['id'] ? 'selected' : '' ?>><?= e((string) $p['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <div class="form-row">
    <div class="field">
      <label for="donation_type">Type</label>
      <select class="select-field" id="donation_type" name="donation_type">
        <option value="one_time" <?= old('donation_type', 'one_time') === 'one_time' ? 'selected' : '' ?>>One-time</option>
        <option value="monthly" <?= old('donation_type') === 'monthly' ? 'selected' : '' ?>>Monthly pledge instalment</option>
      </select>
    </div>
    <div class="field">
      <label class="checkbox-row" style="margin-top:28px"><input type="checkbox" name="anonymous" value="1" <?= old('anonymous') === '1' ? 'checked' : '' ?>> Anonymous / donor unknown</label>
    </div>
  </div>

  <h3 style="margin:18px 0 8px">Donor</h3>
  <div class="form-row">
    <div class="field">
      <label for="donor_name">Name</label>
      <input class="input" id="donor_name" name="donor_name" maxlength="150" value="<?= old('donor_name') ?>">
    </div>
    <div class="field">
      <label for="donor_email">Email</label>
      <input class="input" type="email" id="donor_email" name="donor_email" maxlength="191" value="<?= old('donor_email') ?>">
    </div>
  </div>
  <div class="form-row">
    <div class="field">
      <label for="donor_phone">Phone</label>
      <input class="input" id="donor_phone" name="donor_phone" maxlength="40" value="<?= old('donor_phone') ?>">
    </div>
    <div class="field">
      <label for="note">Internal note</label>
      <input class="input" id="note" name="note" maxlength="255" value="<?= old('note') ?>" placeholder="e.g. Zakat, received at office">
    </div>
  </div>

  <div class="field"><label>Bank / deposit slip<span class="req">*</span> (PNG or JPG, 2 MB maximum)
    <input type="file" name="payment_proof" accept=".jpg,.jpeg,.png" required></label></div>
  <?php require __DIR__ . '/../../partials/donation-extra-fields.php'; ?>
  <p>Receipt email is available only after final verification.</p>

  <div class="sr-actions" style="margin-top:18px">
    <button class="btn btn-brand" type="submit">Save donation</button>
    <a class="btn btn-light" href="<?= e(base_url('admin/finance')) ?>">Cancel</a>
  </div>
</form>
