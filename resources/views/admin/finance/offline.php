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

  <div class="field"><label for="slip_file">Bank / deposit slip<span class="req">*</span> (PNG or JPG, 2 MB maximum)</label>
    <div id="slipDrop" style="border:2px dashed #cdb3c0;border-radius:12px;padding:18px 14px;text-align:center;background:#fdf8fa;transition:all .15s">
      <div style="font-weight:600">Drag &amp; drop the slip here, or choose a file</div>
      <input type="file" id="slip_file" name="payment_proof" accept=".jpg,.jpeg,.png" required style="margin-top:8px;max-width:100%">
      <div id="slipPrev" style="display:none;align-items:center;gap:12px;margin-top:12px;justify-content:center">
        <img id="slipThumb" alt="Slip preview" style="width:64px;height:64px;object-fit:cover;border-radius:8px;border:1px solid #eadde3">
        <span id="slipName" class="muted" style="font-size:13px"></span>
      </div>
      <div id="slipMsg" role="status" style="margin-top:8px;font-size:13px;min-height:1em"></div>
    </div>
  </div>
  <script>
  (function () {
    var drop = document.getElementById('slipDrop'), input = document.getElementById('slip_file'), msg = document.getElementById('slipMsg');
    if (!drop || !input) return;
    function say(t, bad) { msg.textContent = t; msg.style.color = bad ? '#c0392b' : '#1f8a4c'; }
    function show() {
      var f = input.files[0], prev = document.getElementById('slipPrev');
      if (!f) { prev.style.display = 'none'; return; }
      if (!/^image\/(jpeg|png)$/.test(f.type)) { say('Please choose a PNG or JPG image.', true); input.value = ''; prev.style.display = 'none'; return; }
      if (f.size > 2 * 1024 * 1024) { say('This image is larger than 2 MB. Please use a smaller file.', true); input.value = ''; prev.style.display = 'none'; return; }
      document.getElementById('slipThumb').src = URL.createObjectURL(f);
      document.getElementById('slipName').textContent = f.name + ' (' + Math.max(1, Math.round(f.size / 1024)) + ' KB)';
      prev.style.display = 'flex'; say('Slip ready.', false);
    }
    function hl(on) { drop.style.borderColor = on ? '#a92d63' : '#cdb3c0'; drop.style.borderStyle = on ? 'solid' : 'dashed'; drop.style.background = on ? '#fbe9f1' : '#fdf8fa'; }
    ['dragenter', 'dragover'].forEach(function (ev) { drop.addEventListener(ev, function (e) { e.preventDefault(); hl(true); }); });
    ['dragleave', 'dragend'].forEach(function (ev) { drop.addEventListener(ev, function () { hl(false); }); });
    drop.addEventListener('drop', function (e) {
      e.preventDefault(); hl(false);
      var files = e.dataTransfer && e.dataTransfer.files; if (!files || !files.length) return;
      if (!window.DataTransfer) { say('Your browser cannot accept dropped files. Please use the file chooser.', true); return; }
      var dt = new DataTransfer(); dt.items.add(files[0]); input.files = dt.files; show();
    });
    input.addEventListener('change', show);
  })();
  </script>
  <?php require __DIR__ . '/../../partials/donation-extra-fields.php'; ?>
  <p>Receipt email is available only after final verification.</p>

  <div class="sr-actions" style="margin-top:18px">
    <button class="btn btn-brand" type="submit">Save donation</button>
    <a class="btn btn-light" href="<?= e(base_url('admin/finance')) ?>">Cancel</a>
  </div>
</form>
