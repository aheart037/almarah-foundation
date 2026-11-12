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
