<?php
/**
 * Bank transfer donations.
 *  mode=''        two options
 *  mode='report'  "I already paid": details + transaction/slip number + bank + slip upload
 *  mode='new'     "I want to transfer now": our bank accounts, then back to report
 * @var list<array> $banks @var list<string> $currencies @var array $currencyOptions @var string $selectedCurrency
 * @var string $transferNonce @var string $mode @var ?string $submittedRef
 */
$mode = (string) ($mode ?? '');
if (error_for('form') !== '' || old('donor_email') !== '') { $mode = 'report'; }
$cur = (string) $selectedCurrency;
$q = static fn (string $m, string $c): string => base_url('donate/transfer?mode=' . $m . '&currency=' . rawurlencode($c));
$banksFor = array_values(array_filter($banks, static fn ($b): bool => strtoupper((string) ($b['currency'] ?? 'PKR')) === $cur));
?>
<link rel="stylesheet" href="<?= e(asset('assets/css/donate-transfer.css')) ?>">
<section class="dn-hero">
  <div class="container">
    <span class="dn-eyebrow">Bank Transfer</span>
    <h1>Donate by Bank Transfer</h1>
    <p>Pay from your bank, then tell us about it. Our finance team verifies the payment and emails you a thank-you with your tax exemption certificate.</p>
  </div>
</section>

<main class="tf-wrap">
<?php if (!empty($submittedRef)): ?>
  <div class="tf-panel tf-done">
    <div class="tf-check">&#10003;</div>
    <h2>Thank you! We received your report.</h2>
    <div>Your reference number</div>
    <div class="tf-ref"><?= e((string) $submittedRef) ?></div>
    <ul class="tf-steps" style="text-align:left;max-width:460px;margin:0 auto 20px">
      <li><span class="tf-step">1</span><div><b>Received &mdash; processing</b>Your donation is now waiting for verification.</div></li>
      <li><span class="tf-step">2</span><div><b>Finance verifies the payment</b>We check that the money reached our bank account.</div></li>
      <li><span class="tf-step">3</span><div><b>Thank-you email + tax exemption certificate</b>Sent automatically to your email as soon as it is verified.</div></li>
    </ul>
    <p class="tf-sub">Please keep your reference number. If something is unclear we will contact you.</p>
    <a class="tf-btn alt" href="<?= e(base_url('donate')) ?>">Back to Donate</a>
  </div>
<?php else: ?>

  <div class="tf-choose">
    <a class="tf-card<?= $mode === 'report' ? ' is-active' : '' ?>" href="<?= e($q('report', $cur)) ?>">
      <span class="tf-ico">&#128206;</span>
      <h2>I already paid</h2>
      <p>I transferred or deposited to one of your bank accounts. I have the slip or transaction ID.</p>
    </a>
    <a class="tf-card<?= $mode === 'new' ? ' is-active' : '' ?>" href="<?= e($q('new', $cur)) ?>">
      <span class="tf-ico">&#127974;</span>
      <h2>I want to make a new transfer</h2>
      <p>Show me your bank account details so I can pay now.</p>
    </a>
  </div>

  <?php if ($msg = flash('success')): ?><div class="tf-panel" role="status"><?= e($msg) ?></div><?php endif; ?>
  <?php if (error_for('form') !== ''): ?><div class="tf-alert" role="alert"><?= error_for('form') ?></div><?php endif; ?>

  <?php if ($mode === 'new'): ?>
    <div class="tf-panel">
      <h3><span class="tf-step">1</span>Choose your currency</h3>
      <div class="tf-pills">
        <?php foreach ($currencies as $code): ?><a class="<?= $code === $cur ? 'on' : '' ?>" href="<?= e($q('new', $code)) ?>"><?= e($code) ?></a><?php endforeach; ?>
      </div>
      <h3><span class="tf-step">2</span>Pay into any of these accounts</h3>
      <p class="tf-sub">Use your bank app, ATM, or deposit at a branch. Tap <em>Copy</em> to copy a number.</p>
      <?php if (!$banksFor): ?><p>No account is published for <?= e($cur) ?> yet. Please choose another currency.</p><?php endif; ?>
      <?php foreach ($banksFor as $b): ?>
        <div class="tf-bank">
          <h4><?= e((string) $b['bank_name']) ?><?= !empty($b['account_type']) ? ' &middot; ' . e((string) $b['account_type']) : '' ?></h4>
          <dl>
            <dt>Account title</dt><dd><?= e((string) $b['account_title']) ?></dd><span></span>
            <?php foreach (['account_number' => 'Account no.', 'iban' => 'IBAN', 'swift_code' => 'SWIFT / Sort', 'branch' => 'Branch'] as $k => $label): if (!empty($b[$k])): ?>
              <dt><?= e($label) ?></dt><dd><?= e((string) $b[$k]) ?></dd><button type="button" class="tf-copy" data-copy="<?= e((string) $b[$k]) ?>">Copy</button>
            <?php endif; endforeach; ?>
          </dl>
        </div>
      <?php endforeach; ?>
      <h3 style="margin-top:18px"><span class="tf-step">3</span>Then report your payment</h3>
      <p class="tf-sub">Keep the slip or transaction ID. It only takes a minute and lets us verify your donation and send your certificate.</p>
      <a class="tf-btn" href="<?= e($q('report', $cur)) ?>">I have paid &mdash; report my payment</a>
    </div>

  <?php elseif ($mode === 'report'): ?>
    <form id="tfForm" method="post" enctype="multipart/form-data" action="<?= e(base_url('donate/transfer')) ?>" novalidate>
      <?= csrf_field() ?>
      <input type="hidden" name="transfer_attempt" value="<?= e($transferNonce) ?>">
      <label style="display:none" aria-hidden="true">Website <input name="website" autocomplete="off" tabindex="-1"></label>

      <div class="tf-panel">
        <h3><span class="tf-step">1</span>Your details</h3>
        <p class="tf-sub">We email your thank-you and certificate to this address.</p>
        <div class="tf-grid">
          <div class="tf-field full"><label for="donor_name">Full name *</label><input class="input" id="donor_name" name="donor_name" maxlength="150" required autocomplete="name" value="<?= old('donor_name') ?>"></div>
          <div class="tf-field"><label for="donor_email">Email *</label><input class="input" id="donor_email" type="email" name="donor_email" maxlength="191" required autocomplete="email" value="<?= old('donor_email') ?>"></div>
          <div class="tf-field"><label for="donor_phone">Mobile / WhatsApp *</label><input class="input" id="donor_phone" name="donor_phone" maxlength="40" required autocomplete="tel" inputmode="tel" value="<?= old('donor_phone') ?>"></div>
        </div>
      </div>

      <div class="tf-panel">
        <h3><span class="tf-step">2</span>Payment details</h3>
        <div class="tf-grid">
          <div class="tf-field"><label for="transferCurrency">Currency *</label>
            <select id="transferCurrency" name="currency" required>
              <?php foreach ($currencies as $code): ?><option value="<?= e($code) ?>"<?= old('currency', $cur) === $code ? ' selected' : '' ?>><?= e(($currencyOptions[$code]['name'] ?? $code) . ' (' . $code . ')') ?></option><?php endforeach; ?>
            </select></div>
          <div class="tf-field"><label for="amount">Amount you paid *</label><input class="input" id="amount" type="number" inputmode="decimal" step="0.01" min="1" max="5000000" name="amount" required value="<?= old('amount') ?>"></div>
          <div class="tf-field full"><label for="bank_account_id">Which of our bank accounts did you pay into? *</label>
            <select id="bank_account_id" name="bank_account_id" required><option value="">Select the bank&hellip;</option>
              <?php foreach ($banks as $b): ?><option data-currency="<?= e(strtoupper((string) ($b['currency'] ?? 'PKR'))) ?>" value="<?= (int) $b['id'] ?>"<?= (int) old('bank_account_id') === (int) $b['id'] ? ' selected' : '' ?>><?= e((string) $b['bank_name'] . ' - ' . (string) $b['account_title'] . ' (' . (string) ($b['currency'] ?? 'PKR') . ') ...' . substr((string) ($b['account_number'] ?? $b['iban'] ?? ''), -4)) ?></option><?php endforeach; ?>
            </select></div>
          <div class="tf-field full"><label for="bank_reference">Slip number or transaction ID *</label><input class="input" id="bank_reference" name="bank_reference" maxlength="120" required autocomplete="off" value="<?= old('bank_reference') ?>"><div class="tf-hint">Printed on your deposit slip, or shown in your banking app after the transfer.</div></div>
          <div class="tf-field"><label for="received_at">Date of payment *</label><input class="input" id="received_at" type="date" name="received_at" max="<?= gmdate('Y-m-d') ?>" required value="<?= old('received_at', gmdate('Y-m-d')) ?>"></div>
          <div class="tf-field"><label for="source">How did you pay? *</label>
            <select id="source" name="source"><option value="online_transfer">Mobile / online banking</option><option value="bank_transfer">Branch deposit or bank transfer</option><option value="other">Other</option></select></div>
        </div>
      </div>

      <div class="tf-panel">
        <h3><span class="tf-step">3</span>Upload your slip *</h3>
        <p class="tf-sub">A clear photo or screenshot of the deposit slip or transfer confirmation. Large photos are shrunk automatically.</p>
        <label class="tf-drop" id="tfDrop" for="payment_proof">
          <strong>Tap to choose a photo or screenshot</strong>
          <span>JPG or PNG</span>
          <input type="file" id="payment_proof" name="payment_proof" accept="image/*" required>
        </label>
        <div class="tf-preview" id="tfPreview"><img alt="Slip preview" id="tfThumb"><div><div id="tfName"></div><div class="tf-hint" id="tfSize"></div></div></div>
        <div class="tf-msg" id="tfMsg" role="status"></div>
        <div class="tf-field" style="margin-top:14px"><label for="note">Note (optional)</label><textarea class="input" id="note" name="note" maxlength="255" rows="2"><?= old('note') ?></textarea></div>
      </div>

      <details class="tf-panel tf-more"<?= old('acknowledgment_self', '1') === '0' || old('referral_enabled', '0') === '1' ? ' open' : '' ?>>
        <summary>Optional: send the acknowledgment to someone else, or add a referrer</summary>
        <?php require __DIR__ . '/../../partials/donation-extra-fields.php'; ?>
      </details>

      <button class="tf-btn" id="tfSubmit" type="submit">Submit my payment for verification</button>
      <p class="tf-hint" style="text-align:center;margin-top:10px">Reporting a payment does not confirm it. Our finance team verifies that the funds arrived, then your thank-you and tax exemption certificate are emailed automatically.</p>
    </form>
    <p class="tf-hint" style="text-align:center">Was a report rejected? <a href="<?= e(base_url('donate/transfer/resubmit')) ?>">Correct and resubmit it</a>. Prefer to pay by card? <a href="<?= e(base_url('donate')) ?>">Donate online</a>.</p>

  <?php else: ?>
    <p class="tf-hint" style="text-align:center">Choose one of the options above to continue. Prefer to pay by card? <a href="<?= e(base_url('donate')) ?>">Donate online</a>.</p>
  <?php endif; ?>
<?php endif; ?>
</main>

<script>
(function () {
  // Copy buttons
  document.querySelectorAll('.tf-copy').forEach(function (b) {
    b.addEventListener('click', function () {
      var v = b.getAttribute('data-copy');
      (navigator.clipboard ? navigator.clipboard.writeText(v) : Promise.reject()).catch(function () {
        var t = document.createElement('textarea'); t.value = v; document.body.appendChild(t); t.select(); try { document.execCommand('copy'); } catch (e) {} t.remove();
      });
      var old = b.textContent; b.textContent = 'Copied'; setTimeout(function () { b.textContent = old; }, 1500);
    });
  });

  var form = document.getElementById('tfForm'); if (!form) return;
  var cur = document.getElementById('transferCurrency'), bank = document.getElementById('bank_account_id');
  function filterBanks() {
    var first = null;
    Array.prototype.forEach.call(bank.options, function (o) {
      if (!o.value) return;
      var ok = o.getAttribute('data-currency') === cur.value; o.hidden = !ok; o.disabled = !ok;
      if (ok && !first) first = o;
    });
    if (bank.selectedOptions[0] && bank.selectedOptions[0].disabled) bank.value = '';
  }
  cur.addEventListener('change', filterBanks); filterBanks();

  // Slip upload: shrink big photos to a JPEG under 2 MB so phone photos never fail.
  var input = document.getElementById('payment_proof'), msg = document.getElementById('tfMsg'), drop = document.getElementById('tfDrop');
  var LIMIT = 1.9 * 1024 * 1024, ready = true;
  function size(n) { return n > 1048576 ? (n / 1048576).toFixed(1) + ' MB' : Math.round(n / 1024) + ' KB'; }
  function show(file) {
    document.getElementById('tfPreview').classList.add('show'); drop.classList.add('has-file');
    document.getElementById('tfThumb').src = URL.createObjectURL(file);
    document.getElementById('tfName').textContent = file.name; document.getElementById('tfSize').textContent = size(file.size);
  }
  function say(t, cls) { msg.textContent = t; msg.className = 'tf-msg ' + (cls || ''); }
  function toJpeg(file) {
    return new Promise(function (resolve, reject) {
      var img = new Image(), url = URL.createObjectURL(file);
      img.onload = function () {
        var max = 2000, s = Math.min(1, max / Math.max(img.width, img.height)), q = 0.85;
        var c = document.createElement('canvas'); c.width = Math.round(img.width * s); c.height = Math.round(img.height * s);
        var x = c.getContext('2d'); x.fillStyle = '#fff'; x.fillRect(0, 0, c.width, c.height); x.drawImage(img, 0, 0, c.width, c.height);
        (function attempt() {
          c.toBlob(function (blob) {
            if (!blob) return reject(new Error('convert'));
            if (blob.size > LIMIT && q > 0.4) { q -= 0.15; return attempt(); }
            resolve(new File([blob], (file.name.replace(/\.[^.]+$/, '') || 'slip') + '.jpg', { type: 'image/jpeg' }));
          }, 'image/jpeg', q);
        })();
      };
      img.onerror = function () { reject(new Error('read')); };
      img.src = url;
    });
  }
  input.addEventListener('change', function () {
    var f = input.files[0]; if (!f) return;
    ready = false; say('Preparing your photo...');
    var okType = /^image\/(jpeg|png)$/.test(f.type);
    var job = (okType && f.size <= LIMIT) ? Promise.resolve(f) : toJpeg(f);
    job.then(function (out) {
      if (out !== f && window.DataTransfer) { var dt = new DataTransfer(); dt.items.add(out); input.files = dt.files; }
      var cur = input.files[0];
      show(cur);
      if (cur.size > 2 * 1024 * 1024) { say('This image is still too large. Please take a smaller photo or screenshot.', 'err'); }
      else if (!/^image\/(jpeg|png)$/.test(cur.type)) { say('Please choose a JPG or PNG image.', 'err'); }
      else { say('Slip ready (' + size(cur.size) + ').', 'ok'); }
      ready = true;
    }).catch(function () { show(f); say('We could not read this file. Please choose a JPG or PNG photo or screenshot.', 'err'); ready = true; });
  });
  form.addEventListener('submit', function (e) {
    if (!ready) { e.preventDefault(); return; }
    if (!form.reportValidity()) { e.preventDefault(); return; }
    var f = input.files[0];
    if (f && f.size > 2 * 1024 * 1024) { e.preventDefault(); say('Please choose a smaller image (under 2 MB).', 'err'); return; }
    var b = document.getElementById('tfSubmit'); b.disabled = true; b.textContent = 'Sending...';
  });
})();
</script>
