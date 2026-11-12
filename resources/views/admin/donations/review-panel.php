<?php
/**
 * Verification panel: slip, bank/cash details, duplicate warning, finance decision form and history.
 * @var array $donation @var ?array $bank @var array $reviewHistory @var array $duplicates @var ?array $staffIdentity
 */
$vs = (string) ($donation['verification_status'] ?? 'legacy');
$isGateway = $donation['gateway_id'] !== null;
$awaiting = in_array($vs, ['submitted', 'legacy'], true) && !in_array((string) $donation['status'], ['failed', 'cancelled', 'refunded', 'abandoned'], true)
    && (!$isGateway || (string) $donation['status'] === 'completed');
$label = $isGateway && $vs === 'submitted' && (string) $donation['status'] !== 'completed' ? 'Awaiting gateway payment' : ['submitted' => 'Pending verification', 'verified' => 'Verified', 'rejected' => 'Rejected', 'legacy' => 'Not reviewed (legacy)'][$vs] ?? $vs;
?>
<section class="panel" id="verification">
  <h2>Verification</h2>
  <p><strong><?= e($label) ?></strong>
    <?php if (!empty($donation['verified_at'])): ?> &middot; <?= e(dt((string) $donation['verified_at'], 'j M Y H:i')) ?> UTC by <?= e((string) ($staffIdentity['verified_by'] ?? 'system')) ?><?php endif; ?></p>
  <ul class="plain">
    <li>Bank account: <?= $bank ? e($bank['bank_name'] . ' (' . $bank['currency'] . ')') : '&mdash;' ?></li>
    <li>Reference / slip no.: <?= e((string) ($donation['bank_reference'] ?? '—')) ?></li>
    <?php if (!empty($donation['deposited_at'])): ?><li>Cash deposited: <?= e((string) $donation['deposited_at']) ?></li><?php endif; ?>
    <li>Recorded by: <?= e((string) ($staffIdentity['created_by'] ?? 'Donor (public form)')) ?></li>
    <?php if (!empty($donation['payment_proof_path'])): ?>
      <li><a href="<?= e(base_url('admin/donations/' . (int) $donation['id'] . '/proof')) ?>" target="_blank" rel="noopener">View bank / deposit slip</a></li>
    <?php endif; ?>
  </ul>
  <?php
  // Acknowledgment recipient + referrer entered by the donor / staff (shown here so finance sees it while verifying).
  $__v = static fn (string $k): string => trim((string) ($donation[$k] ?? ''));
  $__other = isset($donation['acknowledgment_self']) && (int) $donation['acknowledgment_self'] === 0 && $__v('recipient_name') !== '';
  $__hasRef = !empty($donation['referral_enabled']) && $__v('referrer_name') !== '';
  $__certName = $__other ? $__v('recipient_name') : ($__v('donor_name') !== '' ? $__v('donor_name') : '—');
  ?>
  <div style="margin:14px 0;padding:14px 16px;border:1px solid #eadde3;border-radius:12px;background:#fdf8fa">
    <p style="margin:0 0 8px"><strong>Certificate &amp; acknowledgment name:</strong> <?= e($__certName) ?>
      <span class="muted" style="font-size:12px">(<?= $__other ? 'someone else, entered by the donor' : 'the donor' ?>)</span></p>
    <?php if ($__other): ?>
      <dl class="kv" style="margin:0 0 10px">
        <dt>Recipient</dt><dd><?= e($__v('recipient_name')) ?></dd>
        <?php if ($__v('recipient_email') !== ''): ?><dt>Recipient email</dt><dd><?= e($__v('recipient_email')) ?></dd><?php endif; ?>
        <?php if ($__v('recipient_phone') !== ''): ?><dt>Recipient phone</dt><dd><?= e($__v('recipient_phone')) ?></dd><?php endif; ?>
        <?php if ($__v('recipient_address') !== ''): ?><dt>Recipient address</dt><dd><?= nl2br(e($__v('recipient_address'))) ?></dd><?php endif; ?>
      </dl>
    <?php endif; ?>
    <?php if ($__v('acknowledgment_instructions') !== ''): ?>
      <p style="margin:0 0 10px"><strong>Instructions:</strong> <?= nl2br(e($__v('acknowledgment_instructions'))) ?></p>
    <?php endif; ?>
    <?php if ($__hasRef): ?>
      <dl class="kv" style="margin:0">
        <dt>Referred by</dt><dd><?= e($__v('referrer_name')) ?></dd>
        <?php if ($__v('referrer_phone') !== ''): ?><dt>Referrer phone</dt><dd><?= e($__v('referrer_phone')) ?></dd><?php endif; ?>
        <?php if ($__v('referral_notes') !== ''): ?><dt>Referral notes</dt><dd><?= nl2br(e($__v('referral_notes'))) ?></dd><?php endif; ?>
      </dl>
    <?php else: ?>
      <p class="muted" style="margin:0;font-size:13px">No referrer was added.</p>
    <?php endif; ?>
  </div>
  <?php if ($duplicates): ?>
    <div class="alert alert-error" role="alert"><strong>Possible duplicate:</strong> the same reference appears on
      <?php foreach ($duplicates as $dup): ?><a href="<?= e(base_url('admin/donations/' . (int) $dup['id'])) ?>"><?= e((string) $dup['public_reference']) ?></a> <?php endforeach; ?>
      &mdash; check before verifying.</div>
  <?php endif; ?>
  <?php if ($awaiting && isset($can) && $can('donations.verify')): ?>
    <form method="post" action="<?= e(base_url('admin/donations/' . (int) $donation['id'] . '/review')) ?>">
      <?= csrf_field() ?>
      <div class="field"><label for="review_comment">Comment (required, at least 5 characters, when rejecting)</label>
        <textarea class="input" id="review_comment" name="comment" maxlength="1000"></textarea></div>
      <button class="btn btn-primary" type="submit" name="decision" value="approve">Verify</button>
      <button class="btn btn-light" type="submit" name="decision" value="reject">Reject</button>
    </form>
  <?php endif; ?>
  <?php if ($reviewHistory): ?>
    <h3>History</h3>
    <table class="table"><thead><tr><th>When (UTC)</th><th>Who</th><th>Event</th><th>Comment</th></tr></thead><tbody>
      <?php foreach ($reviewHistory as $ev): ?>
        <tr><td><?= e(dt((string) $ev['created_at'], 'j M Y H:i')) ?></td><td><?= e((string) ($ev['actor_email'] ?? 'Donor/system')) ?></td>
          <td><?= e((string) $ev['decision']) ?></td><td class="wrap"><?= e((string) ($ev['comment'] ?? '')) ?></td></tr>
      <?php endforeach; ?>
    </tbody></table>
  <?php endif; ?>
</section>
