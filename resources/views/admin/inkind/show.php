<?php
/**
 * One in-kind donation: verification, items, donor, certificate history and event timeline.
 * @var array $d @var array $items @var array $events @var array $certs @var int $photoCount @var string $certName
 */
$labels = \App\Services\InKindDonationService::STATUSES;
$cats = \App\Services\InKindDonationService::CATEGORIES;
$badgeStatus = static fn (string $s): string => status_badge_class($s === 'verified' ? 'approved' : $s);
$mailBadge = ['sent' => ['badge badge-green', 'Emailed'], 'queued' => ['badge badge-amber', 'Email queued'], 'failed' => ['badge badge-red', 'Email failed'], 'skipped' => ['badge badge-grey', 'No email'], 'not_sent' => ['badge badge-grey', 'Not sent']];
$v = static fn (string $k): string => trim((string) ($d[$k] ?? ''));
$canVerify = isset($can) && $can('donations.verify');
$other = (int) ($d['acknowledgment_self'] ?? 1) === 0 && $v('recipient_name') !== '';
$hasRef = !empty($d['referral_enabled']) && $v('referrer_name') !== '';
$latest = null;
foreach ($certs as $c) { if ($c['status'] === 'generated') { $latest = $c; break; } }
$totalQty = array_sum(array_map(static fn (array $i): int => (int) $i['quantity'], $items));
$eventLabels = ['submitted' => 'Donation submitted', 'verified' => 'Verified by finance', 'rejected' => 'Rejected', 'reopened' => 'Reopened', 'certificate_generated' => 'Certificate generated', 'certificate_emailed' => 'Certificate emailed', 'certificate_failed' => 'Certificate failed'];
?>
<div class="dash-head">
  <div>
    <h1><span class="mono"><?= e((string) $d['public_reference']) ?></span></h1>
    <p class="sub">
      <span class="<?= e($badgeStatus((string) $d['status'])) ?>"><?= e($labels[$d['status']] ?? (string) $d['status']) ?></span>
      &nbsp;Submitted by <?= e((string) $d['donor_name']) ?> on <?= e(dt((string) $d['created_at'], 'j M Y, H:i')) ?> UTC
    </p>
  </div>
  <div class="sr-actions">
    <a class="btn btn-light" href="<?= e(base_url('admin/inkind')) ?>">Back to in-kind donations</a>
    <?php if ($latest): ?><a class="btn btn-outline-brand" href="<?= e(base_url('admin/inkind/certificates/' . (int) $latest['id'] . '/download')) ?>">Download certificate</a><?php endif; ?>
  </div>
</div>

<section class="panel">
  <div class="panel-head">
    <h2>Verification</h2>
    <span class="<?= e($badgeStatus((string) $d['status'])) ?>"><?= e($labels[$d['status']] ?? (string) $d['status']) ?></span>
  </div>
  <?php if ($d['status'] === 'pending'): ?>
    <p class="panel-sub">Check the items with the donor, or on receipt. Verifying generates the thank-you certificate in the name of <strong><?= e($certName) ?></strong> and emails it to <strong><?= e($v('donor_email')) ?></strong>.</p>
    <?php if ($canVerify): ?>
    <form method="post" action="<?= e(base_url('admin/inkind/' . (int) $d['id'] . '/decide')) ?>">
      <?= csrf_field() ?>
      <div class="field">
        <label for="ik-note">Note <span class="muted">(required when rejecting)</span></label>
        <textarea class="input" id="ik-note" name="note" rows="2" maxlength="1000" placeholder="E.g. Received 50 pairs of shoes on 12 Nov, counted by the warehouse team"></textarea>
      </div>
      <div class="form-actions">
        <button class="btn btn-brand" type="submit" name="decision" value="approve">Verify &amp; send certificate</button>
        <button class="btn btn-outline-brand" type="submit" name="decision" value="reject" onclick="return confirm('Reject this donation?')">Reject</button>
      </div>
    </form>
    <?php else: ?><p class="muted">Your role can view this donation but not verify it.</p><?php endif; ?>
  <?php else: ?>
    <dl class="kv">
      <dt>Decision</dt><dd><?= e($labels[$d['status']] ?? (string) $d['status']) ?> by <?= e(trim((string) ($d['reviewer_name'] ?? '')) ?: 'staff') ?><?= $v('reviewed_at') !== '' ? ' on ' . e(dt($v('reviewed_at'), 'j M Y, H:i')) . ' UTC' : '' ?></dd>
      <?php if ($v('review_note') !== ''): ?><dt>Note</dt><dd><?= nl2br(e($v('review_note'))) ?></dd><?php endif; ?>
    </dl>
    <?php if ($canVerify && $d['status'] === 'rejected'): ?>
    <form method="post" action="<?= e(base_url('admin/inkind/' . (int) $d['id'] . '/decide')) ?>" class="form-actions" style="margin-top:14px">
      <?= csrf_field() ?><input type="hidden" name="decision" value="reopen">
      <button class="btn btn-light" type="submit">Reopen as pending</button>
    </form>
    <?php endif; ?>
  <?php endif; ?>
</section>

<section class="panel">
  <div class="panel-head">
    <h2>Items donated</h2>
    <span class="text-muted"><?= count($items) ?> item line<?= count($items) === 1 ? '' : 's' ?> · <?= number_format($totalQty) ?> in total</span>
  </div>
  <div class="table-wrap">
    <table class="data-table">
      <thead><tr><th>#</th><th>Category</th><th>Item</th><th class="num">Quantity</th><th>Unit</th><th>Details</th></tr></thead>
      <tbody>
      <?php foreach ($items as $n => $i): ?>
        <tr>
          <td class="muted"><?= $n + 1 ?></td>
          <td><?= e($cats[$i['category']] ?? (string) $i['category']) ?></td>
          <td><strong><?= e((string) $i['item_name']) ?></strong></td>
          <td class="num"><strong><?= number_format((int) $i['quantity']) ?></strong></td>
          <td><?= e((string) ($i['unit'] ?? '')) ?: '<span class="muted">&mdash;</span>' ?></td>
          <td class="wrap"><?= e((string) ($i['details'] ?? '')) ?: '<span class="muted">&mdash;</span>' ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php if ($v('description') !== ''): ?><p style="margin-top:14px"><strong>Donor notes:</strong> <?= nl2br(e($v('description'))) ?></p><?php endif; ?>
  <?php if ($photoCount > 0): ?>
    <h3 style="font-size:15px;margin:18px 0 10px">Photos from the donor</h3>
    <div style="display:flex;gap:12px;flex-wrap:wrap">
      <?php for ($p = 0; $p < $photoCount; $p++): $src = e(base_url('admin/inkind/' . (int) $d['id'] . '/photo/' . $p)); ?>
        <a href="<?= $src ?>" target="_blank" rel="noopener"><img src="<?= $src ?>" alt="Photo <?= $p + 1 ?> of the donated items" style="width:150px;height:150px;object-fit:cover;border-radius:12px;border:1px solid #eadde3"></a>
      <?php endfor; ?>
    </div>
  <?php endif; ?>
</section>

<div class="grid-2">
  <section class="panel">
    <h2>Donor &amp; hand-over</h2>
    <dl class="kv">
      <dt>Name</dt><dd><?= e($v('donor_name')) ?></dd>
      <dt>Email</dt><dd><a href="mailto:<?= e($v('donor_email')) ?>"><?= e($v('donor_email')) ?></a></dd>
      <dt>Phone</dt><dd><?= e($v('donor_phone')) ?></dd>
      <?php if ($v('donor_city') !== ''): ?><dt>City</dt><dd><?= e($v('donor_city')) ?></dd><?php endif; ?>
      <?php if ($v('donor_address') !== ''): ?><dt>Address</dt><dd><?= nl2br(e($v('donor_address'))) ?></dd><?php endif; ?>
      <dt>Hand-over</dt><dd><?= $d['handover_method'] === 'pickup' ? 'We collect from the donor' : 'Donor drops the goods off' ?></dd>
      <?php if ($v('handover_date') !== ''): ?><dt>Date</dt><dd><?= e(dt($v('handover_date'), 'j M Y')) ?></dd><?php endif; ?>
      <?php if ($v('pickup_address') !== ''): ?><dt>Pick-up address</dt><dd><?= nl2br(e($v('pickup_address'))) ?></dd><?php endif; ?>
    </dl>
  </section>

  <section class="panel">
    <h2>Certificate name &amp; referral</h2>
    <dl class="kv">
      <dt>Certificate in the name of</dt><dd><strong><?= e($certName) ?></strong> <span class="muted">(<?= $other ? 'someone else, entered by the donor' : 'the donor' ?>)</span></dd>
      <?php if ($other): ?>
        <?php if ($v('recipient_email') !== ''): ?><dt>Recipient email</dt><dd><?= e($v('recipient_email')) ?></dd><?php endif; ?>
        <?php if ($v('recipient_phone') !== ''): ?><dt>Recipient phone</dt><dd><?= e($v('recipient_phone')) ?></dd><?php endif; ?>
        <?php if ($v('recipient_address') !== ''): ?><dt>Recipient address</dt><dd><?= nl2br(e($v('recipient_address'))) ?></dd><?php endif; ?>
      <?php endif; ?>
      <?php if ($v('acknowledgment_instructions') !== ''): ?><dt>Instructions</dt><dd><?= nl2br(e($v('acknowledgment_instructions'))) ?></dd><?php endif; ?>
      <dt>Referred by</dt><dd><?= $hasRef ? e($v('referrer_name')) : '<span class="muted">No referrer</span>' ?></dd>
      <?php if ($hasRef && $v('referrer_phone') !== ''): ?><dt>Referrer phone</dt><dd><?= e($v('referrer_phone')) ?></dd><?php endif; ?>
      <?php if ($hasRef && $v('referral_notes') !== ''): ?><dt>Referral notes</dt><dd><?= nl2br(e($v('referral_notes'))) ?></dd><?php endif; ?>
    </dl>
  </section>
</div>

<?php if ($d['status'] === 'verified'): ?>
<section class="panel">
  <div class="panel-head">
    <h2>Certificate history</h2>
    <?php if ($canVerify): ?>
    <form method="post" action="<?= e(base_url('admin/inkind/' . (int) $d['id'] . '/certificate')) ?>" class="form-actions" style="margin:0">
      <?= csrf_field() ?>
      <?php if (!$latest): ?>
        <button class="btn btn-brand btn-sm" type="submit">Generate certificate</button>
      <?php else: ?>
        <button class="btn btn-outline-brand btn-sm" type="submit" name="reissue" value="1" onclick="return confirm('Create a new certificate version? Earlier versions stay in the history.')">Re-issue as new version</button>
      <?php endif; ?>
    </form>
    <?php endif; ?>
  </div>
  <?php if (!$certs): ?>
    <div class="empty-state"><h3>No certificate yet</h3><p>Use “Generate certificate” to create and email it.</p></div>
  <?php else: ?>
  <div class="table-wrap">
    <table class="data-table">
      <thead><tr><th>Certificate</th><th>Generated</th><th>Email</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($certs as $c): ?>
        <tr>
          <td>
            <strong>v<?= (int) $c['version'] ?></strong><?= $latest && (int) $latest['id'] === (int) $c['id'] ? ' <span class="badge badge-green">Current</span>' : '' ?>
            <div class="mono" style="font-size:12px;margin-top:4px"><?= e((string) $c['certificate_number']) ?></div>
            <div class="muted" style="font-size:12px">In the name of <?= e((string) ($c['certificate_name'] ?? '')) ?></div>
          </td>
          <td style="white-space:nowrap"><?= e(dt((string) $c['created_at'], 'j M Y')) ?>
            <div class="muted" style="font-size:12px"><?= e(dt((string) $c['created_at'], 'H:i')) ?> · <?= e(trim((string) ($c['generated_by_name'] ?? '')) ?: 'system') ?></div></td>
          <td>
            <?php if ($c['status'] === 'failed'): ?>
              <span class="badge badge-red">Generation failed</span><div class="muted wrap" style="font-size:12px;max-width:240px"><?= e((string) $c['generation_error']) ?></div>
            <?php else: ?>
              <span class="<?= e($mailBadge[$c['email_status']][0] ?? 'badge badge-grey') ?>"><?= e($mailBadge[$c['email_status']][1] ?? '—') ?></span>
              <?php if ($c['email_to']): ?><div class="muted" style="font-size:12px"><?= e((string) $c['email_to']) ?><?= $c['email_sent_at'] ? '<br>' . e(dt((string) $c['email_sent_at'], 'j M, H:i')) : '' ?></div><?php endif; ?>
              <?php if ($c['email_status'] === 'failed' && $c['email_last_error']): ?><div class="muted wrap" style="font-size:12px;max-width:240px"><?= e((string) $c['email_last_error']) ?></div><?php endif; ?>
            <?php endif; ?>
          </td>
          <td>
            <?php if ($c['status'] === 'generated'): ?>
            <div style="display:flex;flex-direction:column;gap:6px;align-items:flex-start">
              <a class="btn btn-light btn-sm" href="<?= e(base_url('admin/inkind/certificates/' . (int) $c['id'] . '/download')) ?>">Download PDF</a>
              <?php if ($canVerify): ?>
              <form method="post" action="<?= e(base_url('admin/inkind/certificates/' . (int) $c['id'] . '/resend')) ?>"><?= csrf_field() ?>
                <button class="btn btn-outline-brand btn-sm" type="submit"><?= $c['email_status'] === 'sent' ? 'Resend email' : 'Send email' ?></button></form>
              <?php endif; ?>
            </div>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</section>
<?php endif; ?>

<section class="panel">
  <h2>History</h2>
  <ul class="timeline">
    <?php foreach ($events as $ev): ?>
      <li>
        <span class="when"><?= e(dt((string) $ev['created_at'], 'j M Y, H:i')) ?> UTC</span>
        <div class="what"><?= e($eventLabels[$ev['action']] ?? ucfirst(str_replace('_', ' ', (string) $ev['action']))) ?></div>
        <div class="meta"><?= e(trim((string) ($ev['actor_name'] ?? '')) ?: 'Donor / system') ?><?= !empty($ev['note']) ? ' · ' . e((string) $ev['note']) : '' ?></div>
      </li>
    <?php endforeach; ?>
  </ul>
</section>
