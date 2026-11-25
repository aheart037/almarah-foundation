<?php
/**
 * Delete panel for one donation. Shown only to staff holding donations.delete.
 * @var array $donation
 */
if (!isset($can) || !$can('donations.delete')) { return; }
$deleteBlock = \App\Services\RecordDeletionService::quickDonationBlocker($donation);
?>
<section class="panel" style="margin-top:20px">
  <div class="panel-head"><h3>Delete this donation</h3></div>
  <?php if ($deleteBlock !== null): ?>
    <p class="muted">This donation cannot be deleted. <?= e($deleteBlock) ?></p>
  <?php else: ?>
    <p class="muted">For test, spam or abandoned entries only. This permanently removes the donation, its payment attempts, review history and any uploaded bank slip. A record of the deletion is kept in the audit log.</p>
    <form method="post" action="<?= e(base_url('admin/donations/' . (int) $donation['id'] . '/delete')) ?>" data-confirm="Permanently delete donation <?= e((string) $donation['public_reference']) ?>? This cannot be undone.">
      <?= csrf_field() ?>
      <div class="field" style="max-width:520px;margin:10px 0 12px"><label for="del_reason">Reason for deleting<span class="req">*</span></label><input class="input" id="del_reason" name="reason" required minlength="5" maxlength="500" placeholder="e.g. Test entry made while training staff"><small class="muted">Saved with the deletion in Deleted records.</small></div>
      <button class="btn btn-danger" type="submit">Delete permanently</button>
    </form>
  <?php endif; ?>
</section>
