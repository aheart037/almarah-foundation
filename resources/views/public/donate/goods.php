<?php
/**
 * Donate goods / in-kind. Same look and flow as the bank-transfer report form, with item lines instead of payment details.
 * @var string $goodsNonce @var ?string $submittedRef @var array<string,string> $categories @var int $maxItems
 */
$oldAll = function_exists('_flash_memo') ? _flash_memo('_old', []) : [];
$oldAll = is_array($oldAll) ? $oldAll : [];
$arr = static fn (string $k): array => is_array($oldAll[$k] ?? null) ? array_values($oldAll[$k]) : [];
$names = $arr('item_name');
$rowCount = max(1, count($names));
$val = static fn (string $k, int $i): string => e(is_array($oldAll[$k] ?? null) && is_scalar($oldAll[$k][$i] ?? null) ? (string) $oldAll[$k][$i] : '');
$pickup = old('handover_method', 'drop_off') === 'pickup';

/** One item row; $i = index into the previously submitted arrays (or -1 for the blank template). */
$itemRow = static function (int $i) use ($categories, $val): void {
    $selected = $i >= 0 ? $val('item_category', $i) : '';
    ?>
    <div class="gd-item">
      <div class="tf-grid">
        <div class="tf-field"><label>Category</label>
          <select name="item_category[]">
            <?php foreach ($categories as $key => $label): ?><option value="<?= e($key) ?>"<?= $selected === $key ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
          </select></div>
        <div class="tf-field"><label>Item *</label><input class="input" name="item_name[]" maxlength="150" placeholder="E.g. Winter jackets" value="<?= $i >= 0 ? $val('item_name', $i) : '' ?>"></div>
        <div class="tf-field"><label>Quantity *</label><input class="input" type="number" inputmode="numeric" min="1" max="9999999" step="1" name="item_qty[]" placeholder="E.g. 200" value="<?= $i >= 0 ? $val('item_qty', $i) : '' ?>"></div>
        <div class="tf-field"><label>Unit</label><input class="input" name="item_unit[]" maxlength="30" list="gdUnits" placeholder="pairs, packets, boxes&hellip;" value="<?= $i >= 0 ? $val('item_unit', $i) : '' ?>"></div>
        <div class="tf-field full"><label>Details <span class="tf-hint" style="font-weight:400">(size, condition, expiry date&hellip;)</span></label><input class="input" name="item_details[]" maxlength="500" value="<?= $i >= 0 ? $val('item_details', $i) : '' ?>"></div>
      </div>
      <button type="button" class="gd-remove" aria-label="Remove this item">Remove item</button>
    </div>
    <?php
};
?>
<link rel="stylesheet" href="<?= e(asset('assets/css/donate-transfer.css')) ?>">
<style>
.gd-item{position:relative;padding:14px;border:1.5px solid #eadde3;border-radius:14px;background:#fdf8fa;margin-bottom:12px}
.gd-remove{margin-top:8px;background:none;border:0;color:#a92d63;font-weight:600;font-size:.85rem;cursor:pointer;padding:4px 0}
.gd-add{background:#fff;border:1.5px dashed #a92d63;color:#a92d63;border-radius:12px;padding:11px 16px;font-weight:700;cursor:pointer;width:100%}
.gd-add:hover{background:#fbe9f1}
.gd-add[disabled]{opacity:.5;cursor:not-allowed}
.gd-choices{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:12px}
.gd-choice{display:block;padding:13px 14px;border:1.5px solid #e6cfd9;border-radius:13px;background:#fff;cursor:pointer}
.gd-choice b{display:block;color:#2b1a22}.gd-choice small{color:#7b6e75}
.gd-in{position:absolute;opacity:0;pointer-events:none}
.gd-in:checked + .gd-choice{border-color:#a92d63;background:#fdf5f8;box-shadow:0 0 0 3px rgba(169,45,99,.10)}
.gd-in:focus-visible + .gd-choice{outline:3px solid #F2C200;outline-offset:2px}
.gd-photos{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}
.gd-photo{border:2px dashed #cdb3c0;border-radius:12px;padding:12px;text-align:center;font-size:.85rem;color:#6e6469;background:#fdf8fa;overflow:hidden}
.gd-photo img{display:none;width:100%;height:84px;object-fit:cover;border-radius:8px;margin-bottom:6px}
.gd-photo input{max-width:100%;font-size:.8rem}
@media (max-width:620px){.gd-choices,.gd-photos{grid-template-columns:1fr}}
</style>
<section class="dn-hero">
  <div class="container">
    <span class="dn-eyebrow">Goods &amp; In-Kind</span>
    <h1>Donate Goods / In-Kind</h1>
    <p>Giving shoes, jackets, food packets or other items? Tell us what you are donating. Our team verifies the donation and emails you a thank-you certificate.</p>
  </div>
</section>

<main class="tf-wrap">
<?php if (!empty($submittedRef)): ?>
  <div class="tf-panel tf-done">
    <div class="tf-check">&#10003;</div>
    <h2>Thank you! We received your donation details.</h2>
    <div>Your reference number</div>
    <div class="tf-ref"><?= e((string) $submittedRef) ?></div>
    <ul class="tf-steps" style="text-align:left;max-width:460px;margin:0 auto 20px">
      <li><span class="tf-step">1</span><div><b>Received &mdash; processing</b>Your goods donation is now waiting for verification.</div></li>
      <li><span class="tf-step">2</span><div><b>Our team verifies it</b>We confirm the items and arrange the hand-over or pick-up with you.</div></li>
      <li><span class="tf-step">3</span><div><b>Thank-you certificate</b>Emailed to you as soon as the donation is verified.</div></li>
    </ul>
    <p class="tf-sub">Please keep your reference number. If something is unclear we will contact you.</p>
    <a class="tf-btn alt" href="<?= e(base_url('donate')) ?>">Back to Donate</a>
  </div>
<?php else: ?>

  <?php if (error_for('form') !== ''): ?><div class="tf-alert" role="alert"><?= error_for('form') ?></div><?php endif; ?>

  <form id="gdForm" method="post" enctype="multipart/form-data" action="<?= e(base_url('donate/goods')) ?>" novalidate>
    <?= csrf_field() ?>
    <input type="hidden" name="goods_attempt" value="<?= e($goodsNonce) ?>">
    <label style="display:none" aria-hidden="true">Website <input name="website" autocomplete="off" tabindex="-1"></label>

    <div class="tf-panel">
      <h3><span class="tf-step">1</span>Your details</h3>
      <p class="tf-sub">We email your thank-you certificate to this address.</p>
      <div class="tf-grid">
        <div class="tf-field full"><label for="donor_name">Full name *</label><input class="input" id="donor_name" name="donor_name" maxlength="150" required autocomplete="name" value="<?= old('donor_name') ?>"></div>
        <div class="tf-field"><label for="donor_email">Email *</label><input class="input" id="donor_email" type="email" name="donor_email" maxlength="191" required autocomplete="email" value="<?= old('donor_email') ?>"></div>
        <div class="tf-field"><label for="donor_phone">Mobile / WhatsApp *</label><input class="input" id="donor_phone" name="donor_phone" maxlength="40" required autocomplete="tel" inputmode="tel" value="<?= old('donor_phone') ?>"></div>
        <div class="tf-field"><label for="donor_city">City</label><input class="input" id="donor_city" name="donor_city" maxlength="100" autocomplete="address-level2" value="<?= old('donor_city') ?>"></div>
        <div class="tf-field"><label for="donor_address">Address</label><input class="input" id="donor_address" name="donor_address" maxlength="500" autocomplete="street-address" value="<?= old('donor_address') ?>"></div>
      </div>
    </div>

    <div class="tf-panel">
      <h3><span class="tf-step">2</span>What are you donating?</h3>
      <p class="tf-sub">Add each kind of item on its own line, for example 50 pairs of shoes, then 200 jackets.</p>
      <div id="gdItems">
        <?php for ($i = 0; $i < $rowCount; $i++) { $itemRow($i < count($names) ? $i : -1); } ?>
      </div>
      <datalist id="gdUnits"><option value="pieces"><option value="pairs"><option value="packets"><option value="boxes"><option value="bags"><option value="sets"><option value="kg"></datalist>
      <button type="button" class="gd-add" id="gdAdd">+ Add another item</button>
      <div class="tf-field" style="margin-top:14px"><label for="description">Anything else we should know? <span class="tf-hint" style="font-weight:400">(optional)</span></label>
        <textarea class="input" id="description" name="description" maxlength="2000" rows="3" placeholder="E.g. items are new, washed and packed in boxes"><?= old('description') ?></textarea></div>
    </div>

    <div class="tf-panel">
      <h3><span class="tf-step">3</span>How will the goods reach us?</h3>
      <div class="gd-choices">
        <div><input class="gd-in" type="radio" id="hm_drop" name="handover_method" value="drop_off"<?= $pickup ? '' : ' checked' ?>><label class="gd-choice" for="hm_drop"><b>I will drop them off</b><small>At our office or a location we agree on</small></label></div>
        <div><input class="gd-in" type="radio" id="hm_pick" name="handover_method" value="pickup"<?= $pickup ? ' checked' : '' ?>><label class="gd-choice" for="hm_pick"><b>Please collect them</b><small>Our team will contact you to arrange pick-up</small></label></div>
      </div>
      <div class="tf-grid">
        <div class="tf-field"><label for="handover_date">Preferred or actual date</label><input class="input" id="handover_date" type="date" name="handover_date" value="<?= old('handover_date') ?>"></div>
        <div class="tf-field full" id="gdPickupBox"<?= $pickup ? '' : ' hidden' ?>><label for="pickup_address">Pick-up address *</label><textarea class="input" id="pickup_address" name="pickup_address" maxlength="500" rows="2" placeholder="House, street, area, city"><?= old('pickup_address') ?></textarea></div>
      </div>
    </div>

    <div class="tf-panel">
      <h3><span class="tf-step">4</span>Photos of the items <span class="tf-hint" style="font-weight:400">(optional)</span></h3>
      <p class="tf-sub">Up to 3 photos help us verify faster. Large photos are shrunk automatically.</p>
      <div class="gd-photos">
        <?php for ($p = 1; $p <= 3; $p++): ?>
        <div class="gd-photo"><img alt="" id="gdThumb<?= $p ?>"><input type="file" name="photo_<?= $p ?>" accept="image/*" data-gd-photo="<?= $p ?>"><div class="tf-hint" id="gdMsg<?= $p ?>"></div></div>
        <?php endfor; ?>
      </div>
    </div>

    <details class="tf-panel tf-more"<?= old('acknowledgment_self', '1') === '0' || old('referral_enabled', '0') === '1' ? ' open' : '' ?>>
      <summary>Optional: put the certificate in someone else&rsquo;s name, or add a referrer</summary>
      <?php require __DIR__ . '/../../partials/donation-extra-fields.php'; ?>
    </details>

    <button class="tf-btn" id="gdSubmit" type="submit">Submit my donation for verification</button>
    <p class="tf-hint" style="text-align:center;margin-top:10px">Submitting does not confirm the donation. Our team verifies it first, then emails your thank-you certificate.</p>
  </form>
  <p class="tf-hint" style="text-align:center">Prefer to give money? <a href="<?= e(base_url('donate')) ?>">Donate online or by bank transfer</a>.</p>
<?php endif; ?>
</main>

<?php if (empty($submittedRef)): ?>
<template id="gdRowTpl"><?php $itemRow(-1); ?></template>
<script>
(function () {
  var form = document.getElementById('gdForm'); if (!form) return;
  var box = document.getElementById('gdItems'), add = document.getElementById('gdAdd'), MAX = <?= (int) $maxItems ?>;
  function count() { return box.querySelectorAll('.gd-item').length; }
  function refresh() {
    add.disabled = count() >= MAX;
    box.querySelectorAll('.gd-remove').forEach(function (b) { b.hidden = count() < 2; });
  }
  add.addEventListener('click', function () {
    if (count() >= MAX) return;
    box.appendChild(document.getElementById('gdRowTpl').content.cloneNode(true)); refresh();
    var last = box.querySelector('.gd-item:last-child input[name="item_name[]"]'); if (last) last.focus();
  });
  box.addEventListener('click', function (e) {
    var b = e.target.closest ? e.target.closest('.gd-remove') : null;
    if (b && count() > 1) { b.closest('.gd-item').remove(); refresh(); }
  });
  refresh();

  // Pick-up address only when we collect.
  var pbox = document.getElementById('gdPickupBox'), pa = document.getElementById('pickup_address');
  function syncPickup() { var on = document.getElementById('hm_pick').checked; pbox.hidden = !on; pa.required = on; }
  form.querySelectorAll('input[name="handover_method"]').forEach(function (r) { r.addEventListener('change', syncPickup); }); syncPickup();

  // Photos: preview + shrink big phone photos to a small JPEG before upload.
  var LIMIT = 2.5 * 1024 * 1024, busy = 0;
  function say(n, t, bad) { var m = document.getElementById('gdMsg' + n); m.textContent = t; m.style.color = bad ? '#c0392b' : '#1f8a4c'; }
  function toJpeg(file) {
    return new Promise(function (resolve, reject) {
      var url = URL.createObjectURL(file), img = new Image();
      img.onload = function () {
        var scale = Math.min(1, 1600 / Math.max(img.width, img.height)), q = 0.85;
        var c = document.createElement('canvas'); c.width = Math.round(img.width * scale); c.height = Math.round(img.height * scale);
        c.getContext('2d').drawImage(img, 0, 0, c.width, c.height);
        (function tryOnce() {
          c.toBlob(function (blob) {
            if (!blob) { reject(new Error('blob')); return; }
            if (blob.size > LIMIT && q > 0.4) { q -= 0.15; tryOnce(); return; }
            URL.revokeObjectURL(url);
            resolve(new File([blob], (file.name.replace(/\.[^.]+$/, '') || 'photo') + '.jpg', { type: 'image/jpeg' }));
          }, 'image/jpeg', q);
        })();
      };
      img.onerror = function () { reject(new Error('read')); };
      img.src = url;
    });
  }
  form.querySelectorAll('[data-gd-photo]').forEach(function (input) {
    var n = input.getAttribute('data-gd-photo'), thumb = document.getElementById('gdThumb' + n);
    input.addEventListener('change', function () {
      var f = input.files[0]; if (!f) { thumb.style.display = 'none'; say(n, ''); return; }
      busy++; say(n, 'Preparing...');
      var ok = /^image\/(jpeg|png)$/.test(f.type) && f.size <= LIMIT;
      (ok ? Promise.resolve(f) : toJpeg(f)).then(function (out) {
        if (out !== f && window.DataTransfer) { var dt = new DataTransfer(); dt.items.add(out); input.files = dt.files; }
        thumb.src = URL.createObjectURL(input.files[0]); thumb.style.display = 'block';
        say(n, Math.max(1, Math.round(input.files[0].size / 1024)) + ' KB ready');
      }).catch(function () { input.value = ''; thumb.style.display = 'none'; say(n, 'Could not read this file. Choose a JPG or PNG.', true); })
        .then(function () { busy--; });
    });
  });

  form.addEventListener('submit', function (e) {
    if (busy > 0) { e.preventDefault(); return; }
    if (!form.reportValidity()) { e.preventDefault(); return; }
    var filled = 0;
    box.querySelectorAll('.gd-item').forEach(function (row) {
      var nm = row.querySelector('[name="item_name[]"]'), q = row.querySelector('[name="item_qty[]"]');
      nm.setCustomValidity(''); q.setCustomValidity('');
      if (nm.value.trim() || q.value.trim()) {
        filled++;
        if (!nm.value.trim()) nm.setCustomValidity('Please name this item.');
        if (!q.value.trim() || +q.value < 1) q.setCustomValidity('Please enter a quantity of at least 1.');
      }
    });
    if (filled === 0) { var first = box.querySelector('[name="item_name[]"]'); first.setCustomValidity('Please add at least one item.'); }
    if (!form.reportValidity()) { e.preventDefault(); return; }
    var b = document.getElementById('gdSubmit'); b.disabled = true; b.textContent = 'Sending...';
  });
  form.addEventListener('input', function (e) { if (e.target.setCustomValidity) e.target.setCustomValidity(''); });
})();
</script>
<?php endif; ?>
