<?php
/**
 * Optional acknowledgment-recipient and referral fields shared by the public donate page, the public
 * transfer form and the staff offline form. Field names match DonationReviewService; the sections open
 * with pure CSS (radio / checkbox state), so the form works without JavaScript.
 */
$__self = old('acknowledgment_self', '1') !== '0';
$__ref  = old('referral_enabled', '0') === '1';
?>
<style>
.xf{margin:14px 0 6px;display:grid;gap:14px;text-align:left}
.xf *{box-sizing:border-box}
.xf-card{margin:0;padding:18px;border:1px solid var(--c-line,rgba(169,45,99,.14));border-radius:16px;background:#fff;min-width:0}
.xf-sr{position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;padding:0;border:0}
.xf-head{display:flex;gap:12px;align-items:flex-start;margin:0 0 14px}
.xf-ico{flex:0 0 38px;width:38px;height:38px;border-radius:11px;background:var(--c-blush,#FDF5F8);color:var(--c-primary,#A92D63);display:flex;align-items:center;justify-content:center}
.xf-ico svg{width:20px;height:20px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
.xf-title{display:block;font-weight:800;font-size:1rem;color:var(--c-primary-deep,#6B0F35);line-height:1.3}
.xf-sub{display:block;font-size:.85rem;color:#6e6469;line-height:1.45;margin-top:2px;font-weight:400}
.xf-opt{display:inline-block;margin-left:6px;padding:1px 8px;border-radius:99px;background:var(--c-blush,#FDF5F8);color:var(--c-primary,#A92D63);font-size:.68rem;font-weight:700;letter-spacing:.3px;vertical-align:middle}
.xf-choices{display:grid;grid-template-columns:1fr 1fr;gap:10px;position:relative}
.xf-in{position:absolute;opacity:0;width:1px;height:1px;pointer-events:none}
.xf-choice{display:flex;flex-direction:column;gap:2px;padding:13px 14px 13px 42px;border:1.5px solid #e6cfd9;border-radius:13px;background:#fff;cursor:pointer;position:relative;transition:border-color .15s,background .15s,box-shadow .15s;min-height:62px}
.xf-choice::before{content:'';position:absolute;left:14px;top:16px;width:18px;height:18px;border-radius:50%;border:2px solid #cdb3c0;background:#fff;transition:all .15s}
.xf-choice b{font-size:.93rem;color:#2b1a22}
.xf-choice small{font-size:.78rem;color:#7b6e75;line-height:1.35}
.xf-in:checked + .xf-choice{border-color:var(--c-primary,#A92D63);background:var(--c-blush,#FDF5F8);box-shadow:0 0 0 3px rgba(169,45,99,.10)}
.xf-in:checked + .xf-choice::before{border-color:var(--c-primary,#A92D63);background:radial-gradient(circle,var(--c-primary,#A92D63) 0 4px,#fff 5px)}
.xf-in:focus-visible + .xf-choice,.xf-in:focus-visible + .xf-toggle{outline:3px solid #F2C200;outline-offset:2px}
.xf-toggle{display:flex;align-items:center;gap:14px;padding:13px 14px;border:1.5px solid #e6cfd9;border-radius:13px;background:#fff;cursor:pointer;transition:border-color .15s,background .15s}
.xf-toggle b{display:block;font-size:.93rem;color:#2b1a22}
.xf-toggle small{display:block;font-size:.78rem;color:#7b6e75;line-height:1.35}
.xf-switch{flex:0 0 44px;width:44px;height:26px;border-radius:99px;background:#d9c9d1;position:relative;transition:background .2s}
.xf-switch::after{content:'';position:absolute;left:3px;top:3px;width:20px;height:20px;border-radius:50%;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.25);transition:transform .2s}
.xf-in:checked + .xf-toggle{border-color:var(--c-primary,#A92D63);background:var(--c-blush,#FDF5F8)}
.xf-in:checked + .xf-toggle .xf-switch{background:var(--c-primary,#A92D63)}
.xf-in:checked + .xf-toggle .xf-switch::after{transform:translateX(18px)}
.xf-panel{display:none;grid-template-columns:1fr 1fr;gap:12px;margin-top:14px;padding-top:16px;border-top:1px dashed #e6cfd9}
.xf-choices .xf-panel{grid-column:1 / -1}
#xf_ack_other:checked ~ .xf-panel,#xf_ref:checked ~ .xf-panel{display:grid}
.xf-field{min-width:0}
.xf-field.wide{grid-column:1 / -1}
.xf-field label{display:block;font-weight:700;font-size:.85rem;color:#2b1a22;margin:0 0 5px}
.xf-field label i{font-style:normal;color:#c0392b}
.xf-field .input,.xf-field textarea{width:100%;display:block;padding:11px 13px;border:1.5px solid #e6cfd9;border-radius:11px;font:inherit;font-size:.95rem;background:#fff;color:#1A0810;min-height:46px}
.xf-field textarea{min-height:74px;resize:vertical}
.xf-field .input:focus,.xf-field textarea:focus{outline:none;border-color:var(--c-primary,#A92D63);box-shadow:0 0 0 3px rgba(169,45,99,.12)}
.xf-hint{display:block;margin-top:5px;font-size:.76rem;color:#7b6e75;line-height:1.4}
.xf-note{grid-column:1 / -1;display:flex;gap:9px;align-items:flex-start;padding:10px 12px;border-radius:11px;background:#fff8e1;border:1px solid #f4e2a1;color:#6b5412;font-size:.82rem;line-height:1.45}
.xf-note svg{flex:0 0 17px;width:17px;height:17px;margin-top:1px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
@media (max-width:620px){.xf-choices,.xf-panel{grid-template-columns:1fr}.xf-card{padding:15px}}
</style>

<div class="xf" data-xf>
  <fieldset class="xf-card">
    <legend class="xf-sr">Acknowledgment</legend>
    <div class="xf-head" aria-hidden="true">
      <span class="xf-ico"><svg viewBox="0 0 24 24"><path d="M4 7h16v12H4z"/><path d="m4 8 8 6 8-6"/></svg></span>
      <span><span class="xf-title">Acknowledgment <span class="xf-opt">Optional</span></span>
      <span class="xf-sub">Who should the thank-you and tax exemption certificate be in the name of?</span></span>
    </div>
    <div class="xf-choices">
      <input class="xf-in" type="radio" id="xf_ack_self" name="acknowledgment_self" value="1" <?= $__self ? 'checked' : '' ?>>
      <label class="xf-choice" for="xf_ack_self"><b>The donor</b><small>In the name of the person donating</small></label>
      <input class="xf-in" type="radio" id="xf_ack_other" name="acknowledgment_self" value="0" <?= $__self ? '' : 'checked' ?>>
      <label class="xf-choice" for="xf_ack_other"><b>Someone else</b><small>A gift or donation made on another person&rsquo;s behalf</small></label>

      <div class="xf-panel" id="xf_other_panel">
        <div class="xf-field wide">
          <label for="xf_recipient_name">Recipient full name <i>*</i></label>
          <input class="input" id="xf_recipient_name" name="recipient_name" maxlength="160" autocomplete="off" placeholder="E.g. Ayesha Khan" value="<?= old('recipient_name') ?>">
          <span class="xf-hint">The tax exemption certificate will be issued in this name.</span>
        </div>
        <div class="xf-field">
          <label for="xf_recipient_email">Recipient email</label>
          <input class="input" type="email" id="xf_recipient_email" name="recipient_email" maxlength="191" autocomplete="off" placeholder="name@example.com" value="<?= old('recipient_email') ?>">
        </div>
        <div class="xf-field">
          <label for="xf_recipient_phone">Recipient phone</label>
          <input class="input" type="tel" id="xf_recipient_phone" name="recipient_phone" maxlength="40" autocomplete="off" placeholder="+92 300 0000000" value="<?= old('recipient_phone') ?>">
        </div>
        <div class="xf-field wide">
          <label for="xf_recipient_address">Recipient address</label>
          <textarea class="input" id="xf_recipient_address" name="recipient_address" maxlength="500" rows="2"><?= old('recipient_address') ?></textarea>
        </div>
        <div class="xf-field wide">
          <label for="xf_ack_instructions">Instructions for the team</label>
          <textarea class="input" id="xf_ack_instructions" name="acknowledgment_instructions" maxlength="1000" rows="2" placeholder="E.g. a gift for a birthday, add a short dedication"><?= old('acknowledgment_instructions') ?></textarea>
        </div>
      </div>
    </div>
  </fieldset>

  <fieldset class="xf-card">
    <legend class="xf-sr">Referral</legend>
    <div class="xf-head" aria-hidden="true">
      <span class="xf-ico"><svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3.2"/><path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6"/><path d="M17 8v6M14 11h6"/></svg></span>
      <span><span class="xf-title">Referral <span class="xf-opt">Optional</span></span>
      <span class="xf-sub">Did someone introduce this donation to us?</span></span>
    </div>
    <div>
      <input class="xf-in" type="checkbox" id="xf_ref" name="referral_enabled" value="1" <?= $__ref ? 'checked' : '' ?>>
      <label class="xf-toggle" for="xf_ref"><span class="xf-switch"></span><span><b>This donation was referred by someone</b><small>Add the person who encouraged or arranged it</small></span></label>
      <div class="xf-panel" id="xf_ref_panel">
        <div class="xf-field">
          <label for="xf_referrer_name">Referrer full name <i>*</i></label>
          <input class="input" id="xf_referrer_name" name="referrer_name" maxlength="160" autocomplete="off" placeholder="E.g. Bilal Ahmed" value="<?= old('referrer_name') ?>">
        </div>
        <div class="xf-field">
          <label for="xf_referrer_phone">Referrer phone</label>
          <input class="input" type="tel" id="xf_referrer_phone" name="referrer_phone" maxlength="40" autocomplete="off" placeholder="+92 300 0000000" value="<?= old('referrer_phone') ?>">
        </div>
        <div class="xf-field wide">
          <label for="xf_referral_notes">Notes</label>
          <textarea class="input" id="xf_referral_notes" name="referral_notes" maxlength="1000" rows="2" placeholder="Anything our team should know about this referral"><?= old('referral_notes') ?></textarea>
        </div>
      </div>
    </div>
  </fieldset>
</div>
<script>
(function () {
  var root = document.querySelector('[data-xf]');
  if (!root) return;
  var other = root.querySelector('#xf_ack_other'), ref = root.querySelector('#xf_ref');
  var rn = root.querySelector('#xf_recipient_name'), fn = root.querySelector('#xf_referrer_name');
  function sync() { if (rn) rn.required = !!(other && other.checked); if (fn) fn.required = !!(ref && ref.checked); }
  root.addEventListener('change', sync); sync();
})();
</script>
