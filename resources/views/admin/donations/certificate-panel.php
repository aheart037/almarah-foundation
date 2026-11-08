<?php
/**
 * Tax exemption certificate: status, download and recovery actions.
 * @var array $donation @var ?array $certificate @var ?string $certificateBlock reason a certificate cannot exist yet
 */
$cert = $certificate ?? null;
$did = (int) $donation['id'];
$gen = $cert['status'] ?? null;
$mailStatus = (string) ($cert['email_status'] ?? 'not_sent');
$mailLabel = ['not_sent' => 'not sent', 'queued' => 'queued', 'sent' => 'sent', 'failed' => 'failed', 'skipped' => 'skipped'][$mailStatus] ?? $mailStatus;
?>
<section class="panel" id="certificate">
  <h2>Tax exemption certificate</h2>
  <?php if (!$cert && !empty($certificateBlock)): ?>
    <p class="muted"><?= e((string) $certificateBlock) ?> A certificate is issued automatically once the donation is verified.</p>
  <?php else: ?>
    <dl class="kv">
      <dt>Generation</dt><dd><span class="<?= e(status_badge_class($gen === 'generated' ? 'completed' : (string) ($gen ?? 'pending'))) ?>"><?= e($gen ?? 'not generated') ?></span></dd>
      <?php if ($cert && $cert['certificate_number']): ?>
        <dt>Certificate no.</dt><dd class="mono"><?= e((string) $cert['certificate_number']) ?></dd>
      <?php endif; ?>
      <?php if (!empty($cert['issued_at'])): ?>
        <dt>Issued</dt><dd><?= e(dt((string) $cert['issued_at'], 'j F Y H:i')) ?> UTC</dd>
      <?php endif; ?>
      <?php if ($gen === 'generated'): ?>
        <dt>Email</dt>
        <dd><span class="<?= e(status_badge_class($mailStatus)) ?>"><?= e($mailLabel) ?></span>
          <?php if (!empty($cert['email_to'])): ?><span class="muted"> &middot; <?= e((string) $cert['email_to']) ?></span><?php endif; ?>
          <?php if ($mailStatus === 'sent' && !empty($cert['email_sent_at'])): ?><span class="muted"> &middot; <?= e(dt((string) $cert['email_sent_at'], 'j M Y H:i')) ?> UTC</span><?php endif; ?></dd>
        <?php if (!empty($cert['email_last_error']) && $mailStatus !== 'sent'): ?>
          <dt>Email error</dt><dd class="wrap"><?= e((string) $cert['email_last_error']) ?></dd>
        <?php endif; ?>
      <?php elseif (!empty($cert['generation_error'])): ?>
        <dt>Error</dt><dd class="wrap"><?= e((string) $cert['generation_error']) ?></dd>
      <?php endif; ?>
    </dl>
    <div class="sr-actions">
      <?php if ($gen === 'generated'): ?>
        <a class="btn btn-outline-brand" href="<?= e(base_url('admin/donations/' . $did . '/certificate')) ?>">Download PDF</a>
        <?php if ((in_array($mailStatus, ['failed', 'not_sent', 'skipped'], true) || ($mailStatus === 'queued' && !empty($cert['email_last_error']))) && isset($can) && $can('donations.verify')): ?>
          <form method="post" action="<?= e(base_url('admin/donations/' . $did . '/certificate/email')) ?>" style="display:inline"><?= csrf_field() ?><button class="btn btn-light" type="submit">Retry email</button></form>
        <?php endif; ?>
      <?php elseif (isset($can) && $can('donations.verify')): ?>
        <form method="post" action="<?= e(base_url('admin/donations/' . $did . '/certificate/generate')) ?>" style="display:inline"><?= csrf_field() ?><button class="btn btn-primary" type="submit">Generate certificate</button></form>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</section>
