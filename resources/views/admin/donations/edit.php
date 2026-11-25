<?php
/**
 * Edit a staff-recorded offline donation.
 * @var array $donation @var array $access ['mode' => 'full'|'contact', 'reason' => ?string]
 * @var array $purposes @var array $sources @var array $banks
 */
$full = ($access['mode'] ?? 'full') === 'full';
$lock = $full ? '' : ' disabled';
$did = (int) $donation['id'];
$status = (string) ($donation['verification_status'] ?? '');
?>
<div class="dash-head">
  <div>
    <h1>Edit <?= e((string) $donation['public_reference']) ?></h1>
    <p class="sub">Offline donation recorded by staff. Every change is written to the audit log.</p>
  </div>
  <a class="btn btn-light" href="<?= e(base_url('admin/donations/' . $did)) ?>">Back to donation</a>
</div>

<?php if (!$full): ?>
  <div class="alert alert-warning"><?= e((string) $access['reason']) ?></div>
<?php elseif ($status !== 'submitted'): ?>
  <div class="alert alert-warning">This donation is <strong><?= e($status === 'rejected' ? 'rejected' : 'part-way through review') ?></strong>. Saving a change sends it back to the first review stage so the corrected details are checked again.</div>
<?php endif; ?>

<form method="post" action="<?= e(base_url('admin/donations/' . $did . '/edit')) ?>" class="panel" novalidate enctype="multipart/form-data">
  <?= csrf_field() ?>
  <?php if (error_for('form') !== ''): ?><div class="alert alert-error"><?= error_for('form') ?></div><?php endif; ?>

  <div class="form-row">
    <div class="field">
      <label for="amount">Amount<span class="req">*</span></label>
      <input class="input" type="number" step="0.01" min="1" id="amount" name="amount" required value="<?= old('amount') ?>"<?= $lock ?>>
    </div>
    <div class="field">
      <label for="currency">Currency</label>
      <select class="select-field" id="currency" name="currency"<?= $lock ?>>
        <?php foreach (\App\Services\RecordEditService::CURRENCIES as $c): ?>
          <option value="<?= $c ?>" <?= old('currency', 'PKR') === $c ? 'selected' : '' ?>><?= $c ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <div class="form-row">
    <div class="field">
      <label for="source">Received via<span class="req">*</span></label>
      <select class="select-field" id="source" name="source"<?= $lock ?>>
        <?php foreach ($sources as $k => $label): ?>
          <option value="<?= e($k) ?>" <?= old('source', 'bank_transfer') === $k ? 'selected' : '' ?>><?= e($label) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field">
      <label for="received_at">Date received</label>
      <input class="input" type="date" id="received_at" name="received_at" required value="<?= old('received_at') ?>" max="<?= e(gmdate('Y-m-d')) ?>"<?= $lock ?>>
    </div>
  </div>

  <div class="form-row">
    <div class="field">
      <label for="bank_reference">Bank / transaction reference</label>
      <input class="input" id="bank_reference" name="bank_reference" maxlength="120" value="<?= old('bank_reference') ?>"<?= $lock ?>>
      <small class="muted">For cash, enter the bank deposit slip number.</small>
    </div>
    <div class="field">
      <label for="bank_account_id">Bank account<span class="req">*</span></label>
      <select class="select-field" id="bank_account_id" name="bank_account_id" required<?= $lock ?>>
        <option value="">Select…</option>
        <?php foreach ($banks as $b): ?><option value="<?= (int) $b['id'] ?>" <?= (int) old('bank_account_id') === (int) $b['id'] ? 'selected' : '' ?>><?= e((string) $b['bank_name'] . ' – ' . (string) $b['currency'] . ' – …' . substr((string) $b['account_number'], -4)) ?></option><?php endforeach; ?>
      </select>
      <small class="muted">Currency must match the bank account.</small>
    </div>
    <div class="field">
      <label for="deposited_at">Cash deposit date</label>
      <input class="input" type="date" id="deposited_at" name="deposited_at" max="<?= gmdate('Y-m-d') ?>" value="<?= old('deposited_at') ?>"<?= $lock ?>>
      <small class="muted">Required when received via Cash.</small>
    </div>
    <div class="field">
      <label for="purpose_id">Purpose</label>
      <select class="select-field" id="purpose_id" name="purpose_id"<?= $lock ?>>
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
      <select class="select-field" id="donation_type" name="donation_type"<?= $lock ?>>
        <option value="one_time" <?= old('donation_type', 'one_time') === 'one_time' ? 'selected' : '' ?>>One-time</option>
        <option value="monthly" <?= old('donation_type') === 'monthly' ? 'selected' : '' ?>>Monthly pledge instalment</option>
      </select>
    </div>
    <div class="field">
      <label class="checkbox-row" style="margin-top:28px"><input type="checkbox" name="anonymous" value="1" <?= old('anonymous') === '1' ? 'checked' : '' ?><?= $lock ?>> Anonymous / donor unknown</label>
    </div>
  </div>

  <h3 style="margin:18px 0 8px">Donor</h3>
  <div class="form-row">
    <div class="field">
      <label for="donor_name">Name</label>
      <input class="input" id="donor_name" name="donor_name" maxlength="150" value="<?= old('donor_name') ?>"<?= $lock ?>>
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
      <?php if (!$full): ?><small class="muted">Used for the WhatsApp message, so keep it in international format.</small><?php endif; ?>
    </div>
    <div class="field">
      <label for="note">Internal note</label>
      <input class="input" id="note" name="note" maxlength="255" value="<?= old('note') ?>">
    </div>
  </div>

  <?php if ($full): ?>
  <div class="field"><label for="slip_file">Replace bank / deposit slip <span class="muted">(optional, PNG or JPG, 2 MB maximum)</span></label>
    <input type="file" id="slip_file" name="payment_proof" accept=".jpg,.jpeg,.png" style="max-width:100%">
    <?php if (!empty($donation['payment_proof_path'])): ?>
      <small class="muted">Current slip: <a href="<?= e(base_url('admin/donations/' . $did . '/proof')) ?>" target="_blank" rel="noopener">view</a>. Leave empty to keep it.</small>
    <?php endif; ?>
  </div>
  <?php require __DIR__ . '/../../partials/donation-extra-fields.php'; ?>
  <?php endif; ?>

  <div class="field"><label for="edit_reason">Reason for this edit<span class="req">*</span></label>
    <input class="input" id="edit_reason" name="reason" required minlength="5" maxlength="500" value="<?= old('reason') ?>" placeholder="e.g. Donor phoned to correct the amount on the deposit slip">
    <small class="muted">Saved in the history and the audit log.</small></div>

  <div class="sr-actions" style="margin-top:18px">
    <button class="btn btn-brand" type="submit">Save changes</button>
    <a class="btn btn-light" href="<?= e(base_url('admin/donations/' . $did)) ?>">Cancel</a>
  </div>
</form>
