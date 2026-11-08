<?php
/**
 * Optional acknowledgment-recipient and referral fields shared by the public donate page, the public
 * transfer form and the staff offline form. Field names match DonationReviewService; no JavaScript needed.
 */
$__self = old('acknowledgment_self', '1') !== '0';
?>
<fieldset style="border:0;padding:0;margin:12px 0">
  <legend><strong>Acknowledgment</strong></legend>
  <p><label><input type="radio" name="acknowledgment_self" value="1" <?= $__self ? 'checked' : '' ?>> Send the acknowledgment to the donor</label><br>
     <label><input type="radio" name="acknowledgment_self" value="0" <?= $__self ? '' : 'checked' ?>> Send it to someone else (fill in the recipient below)</label></p>
  <details <?= $__self ? '' : 'open' ?>>
    <summary>Recipient details</summary>
    <p><label>Recipient full name <input class="input" name="recipient_name" maxlength="160" value="<?= old('recipient_name') ?>"></label></p>
    <p><label>Recipient phone <input class="input" name="recipient_phone" maxlength="40" value="<?= old('recipient_phone') ?>"></label></p>
    <p><label>Recipient email <input class="input" type="email" name="recipient_email" maxlength="191" value="<?= old('recipient_email') ?>"></label></p>
    <p><label>Recipient address <textarea class="input" name="recipient_address" maxlength="500"><?= old('recipient_address') ?></textarea></label></p>
  </details>
  <p><label>Acknowledgment instructions (optional) <textarea class="input" name="acknowledgment_instructions" maxlength="1000"><?= old('acknowledgment_instructions') ?></textarea></label></p>
</fieldset>
<fieldset style="border:0;padding:0;margin:12px 0">
  <legend><strong>Referral</strong></legend>
  <p><label><input type="checkbox" name="referral_enabled" value="1" <?= old('referral_enabled', '0') === '1' ? 'checked' : '' ?>> This donation was referred by someone</label></p>
  <details <?= old('referral_enabled', '0') === '1' ? 'open' : '' ?>>
    <summary>Referrer details</summary>
    <p><label>Referrer full name <input class="input" name="referrer_name" maxlength="160" value="<?= old('referrer_name') ?>"></label></p>
    <p><label>Referrer phone <input class="input" name="referrer_phone" maxlength="40" value="<?= old('referrer_phone') ?>"></label></p>
    <p><label>Referral notes <textarea class="input" name="referral_notes" maxlength="1000"><?= old('referral_notes') ?></textarea></label></p>
  </details>
</fieldset>
