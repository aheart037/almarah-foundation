<?php
/**
 * Edit an in-kind (goods) donation: donor, items, hand-over, acknowledgment and referrer.
 * Photos and the verification status are not changed here.
 * @var array $d @var array $items
 */
$cats = \App\Services\InKindDonationService::CATEGORIES;
$maxItems = \App\Services\InKindDonationService::MAX_ITEMS;
$did = (int) $d['id'];
$flashed = _flash_memo('_old', []);
// After a failed save the submitted rows come back; otherwise show the saved items.
if (is_array($flashed) && is_array($flashed['item_name'] ?? null)) {
    $rows = [];
    foreach ($flashed['item_name'] as $i => $n) {
        $rows[] = [
            'category' => (string) ($flashed['item_category'][$i] ?? 'other'), 'item_name' => (string) $n,
            'quantity' => (string) ($flashed['item_qty'][$i] ?? ''), 'unit' => (string) ($flashed['item_unit'][$i] ?? ''),
            'details' => (string) ($flashed['item_details'][$i] ?? ''),
        ];
    }
} else {
    $rows = $items;
}
if ($rows === []) {
    $rows[] = ['category' => 'other', 'item_name' => '', 'quantity' => '', 'unit' => '', 'details' => ''];
}
$pickup = old('handover_method', 'drop_off') === 'pickup';
$itemRow = static function (array $r) use ($cats): void { ?>
  <div class="gd-item form-row" style="align-items:flex-end;border-bottom:1px dashed #e6cfd9;padding-bottom:10px;margin-bottom:10px">
    <div class="field"><label>Category</label>
      <select class="select-field" name="item_category[]">
        <?php foreach ($cats as $k => $label): ?><option value="<?= e($k) ?>" <?= (string) $r['category'] === $k ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
      </select></div>
    <div class="field"><label>Item *</label><input class="input" name="item_name[]" maxlength="150" value="<?= e((string) $r['item_name']) ?>"></div>
    <div class="field"><label>Quantity *</label><input class="input" type="number" min="1" max="9999999" step="1" name="item_qty[]" value="<?= e((string) $r['quantity']) ?>"></div>
    <div class="field"><label>Unit</label><input class="input" name="item_unit[]" maxlength="30" value="<?= e((string) ($r['unit'] ?? '')) ?>"></div>
    <div class="field" style="flex:2 1 260px"><label>Details</label><input class="input" name="item_details[]" maxlength="500" value="<?= e((string) ($r['details'] ?? '')) ?>"></div>
    <div class="field" style="flex:0 0 auto"><button type="button" class="btn btn-light btn-sm" data-remove-item>Remove</button></div>
  </div>
<?php };
?>
<div class="dash-head">
  <div>
    <h1>Edit <span class="mono"><?= e((string) $d['public_reference']) ?></span></h1>
    <p class="sub">Every change is written to the donation's history and the audit log.</p>
  </div>
  <a class="btn btn-light" href="<?= e(base_url('admin/inkind/' . $did)) ?>">Back to donation</a>
</div>

<?php if ((string) $d['status'] === 'verified'): ?>
  <div class="alert alert-warning">This donation is verified and a certificate has been issued. Saving does <strong>not</strong> change that certificate; after saving, use <em>Re-issue</em> on the donation page if it should show the new details.</div>
<?php endif; ?>

<form method="post" action="<?= e(base_url('admin/inkind/' . $did . '/edit')) ?>" class="panel" novalidate id="ikEditForm">
  <?= csrf_field() ?>
  <?php if (error_for('form') !== ''): ?><div class="alert alert-error"><?= error_for('form') ?></div><?php endif; ?>

  <h3 style="margin:0 0 8px">Donor</h3>
  <div class="form-row">
    <div class="field"><label for="donor_name">Full name<span class="req">*</span></label><input class="input" id="donor_name" name="donor_name" maxlength="150" required value="<?= old('donor_name') ?>"></div>
    <div class="field"><label for="donor_email">Email<span class="req">*</span></label><input class="input" id="donor_email" type="email" name="donor_email" maxlength="191" required value="<?= old('donor_email') ?>"></div>
    <div class="field"><label for="donor_phone">Mobile / WhatsApp<span class="req">*</span></label><input class="input" id="donor_phone" name="donor_phone" maxlength="40" required value="<?= old('donor_phone') ?>"></div>
  </div>
  <div class="form-row">
    <div class="field"><label for="donor_city">City</label><input class="input" id="donor_city" name="donor_city" maxlength="100" value="<?= old('donor_city') ?>"></div>
    <div class="field"><label for="donor_address">Address</label><input class="input" id="donor_address" name="donor_address" maxlength="500" value="<?= old('donor_address') ?>"></div>
  </div>

  <h3 style="margin:18px 0 8px">Items</h3>
  <div id="ikItems"><?php foreach ($rows as $r) { $itemRow($r); } ?></div>
  <button type="button" class="btn btn-light btn-sm" id="ikAddItem">+ Add another item</button>
  <small class="muted" style="display:block;margin-top:6px">Up to <?= (int) $maxItems ?> items. Blank rows are ignored.</small>
  <div class="field" style="margin-top:12px"><label for="description">Notes</label>
    <textarea class="input" id="description" name="description" maxlength="2000" rows="3"><?= old('description') ?></textarea></div>

  <h3 style="margin:18px 0 8px">Hand-over</h3>
  <div class="form-row">
    <div class="field"><label for="handover_method">How the goods reach us</label>
      <select class="select-field" id="handover_method" name="handover_method">
        <option value="drop_off" <?= $pickup ? '' : 'selected' ?>>Dropped off</option>
        <option value="pickup" <?= $pickup ? 'selected' : '' ?>>Collected from the donor</option>
      </select></div>
    <div class="field"><label for="handover_date">Date</label><input class="input" id="handover_date" type="date" name="handover_date" value="<?= old('handover_date') ?>"></div>
  </div>
  <div class="field" id="ikPickupBox"<?= $pickup ? '' : ' hidden' ?>><label for="pickup_address">Pick-up address</label><textarea class="input" id="pickup_address" name="pickup_address" maxlength="500" rows="2"><?= old('pickup_address') ?></textarea></div>

  <?php require __DIR__ . '/../../partials/donation-extra-fields.php'; ?>

  <div class="field"><label for="edit_reason">Reason for this edit<span class="req">*</span></label>
    <input class="input" id="edit_reason" name="reason" required minlength="5" maxlength="500" value="<?= old('reason') ?>" placeholder="e.g. Donor phoned to correct the amount on the deposit slip">
    <small class="muted">Saved in the history and the audit log.</small></div>

  <div class="sr-actions" style="margin-top:18px">
    <button class="btn btn-brand" type="submit">Save changes</button>
    <a class="btn btn-light" href="<?= e(base_url('admin/inkind/' . $did)) ?>">Cancel</a>
  </div>
</form>

<template id="ikItemTpl"><?php $itemRow(['category' => 'other', 'item_name' => '', 'quantity' => '', 'unit' => '', 'details' => '']); ?></template>
<script>
(function () {
  var box = document.getElementById('ikItems'), tpl = document.getElementById('ikItemTpl'), max = <?= (int) $maxItems ?>;
  var add = document.getElementById('ikAddItem'), method = document.getElementById('handover_method'), pick = document.getElementById('ikPickupBox');
  if (!box || !tpl) return;
  add.addEventListener('click', function () {
    if (box.querySelectorAll('.gd-item').length >= max) return;
    box.appendChild(tpl.content.cloneNode(true));
  });
  box.addEventListener('click', function (e) {
    if (!e.target.matches('[data-remove-item]')) return;
    var row = e.target.closest('.gd-item');
    if (box.querySelectorAll('.gd-item').length > 1) row.remove();
    else row.querySelectorAll('input').forEach(function (i) { i.value = ''; });
  });
  if (method && pick) method.addEventListener('change', function () { pick.hidden = method.value !== 'pickup'; });
})();
</script>
