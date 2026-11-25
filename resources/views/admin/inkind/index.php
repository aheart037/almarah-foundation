<?php
/**
 * In-kind (goods) donations: finance work list.
 * @var array $filters @var array $result @var int $page @var int $lastPage @var int $total
 */
$labels = \App\Services\InKindDonationService::STATUSES;
$counts = $result['counts'];
$all = array_sum($counts);
$query = array_filter($filters, static fn ($v): bool => $v !== '');
$tabUrl = static fn (string $status): string => base_url('admin/inkind' . ($status !== '' || $query ? '?' . http_build_query(array_filter(array_merge($query, ['status' => $status, 'page' => null]), static fn ($v): bool => $v !== '' && $v !== null)) : ''));
$mailBadge = ['sent' => ['badge badge-green', 'Emailed'], 'queued' => ['badge badge-amber', 'Email queued'], 'failed' => ['badge badge-red', 'Email failed'], 'skipped' => ['badge badge-grey', 'No email'], 'not_sent' => ['badge badge-grey', 'Not sent']];
$cardLink = 'display:block;color:inherit;text-decoration:none';
?>
<div class="dash-head">
  <div>
    <h1>In-kind donations</h1>
    <p class="sub">Goods donated by supporters. Finance verifies each one, then the thank-you certificate is generated and emailed. Goods never count in money totals.</p>
  </div>
  <div class="sr-actions">
    <a class="btn btn-outline-brand" href="<?= e(base_url('donate/goods')) ?>" target="_blank" rel="noopener">Open public form</a>
  </div>
</div>

<div class="stat-cards">
  <a class="stat-card<?= $counts['pending'] > 0 ? ' brand' : '' ?>" style="<?= $cardLink ?>" href="<?= e($tabUrl('pending')) ?>">
    <div class="k">Pending verification</div><div class="v"><?= (int) $counts['pending'] ?></div><div class="d"><?= $counts['pending'] > 0 ? 'Waiting for finance' : 'Nothing waiting' ?></div>
  </a>
  <a class="stat-card" style="<?= $cardLink ?>" href="<?= e($tabUrl('verified')) ?>">
    <div class="k">Verified</div><div class="v"><?= (int) $counts['verified'] ?></div><div class="d">Certificate issued</div>
  </a>
  <a class="stat-card" style="<?= $cardLink ?>" href="<?= e($tabUrl('rejected')) ?>">
    <div class="k">Rejected</div><div class="v"><?= (int) $counts['rejected'] ?></div><div class="d">Not accepted</div>
  </a>
  <a class="stat-card" style="<?= $cardLink ?>" href="<?= e($tabUrl('')) ?>">
    <div class="k">All submissions</div><div class="v"><?= (int) $all ?></div><div class="d">Since launch</div>
  </a>
</div>

<section class="panel">
  <nav class="tabs" aria-label="Filter by status">
    <a href="<?= e($tabUrl('')) ?>" class="<?= $filters['status'] === '' ? 'active' : '' ?>">All (<?= (int) $all ?>)</a>
    <?php foreach ($labels as $key => $label): ?>
      <a href="<?= e($tabUrl($key)) ?>" class="<?= $filters['status'] === $key ? 'active' : '' ?>"><?= e($label) ?> (<?= (int) $counts[$key] ?>)</a>
    <?php endforeach; ?>
  </nav>

  <form class="toolbar" method="get" action="<?= e(base_url('admin/inkind')) ?>" style="margin-top:16px">
    <input type="hidden" name="status" value="<?= e($filters['status']) ?>">
    <div class="search-field">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
      <input class="input" type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="Search donor, reference, phone or item">
    </div>
    <div class="field" style="margin:0">
      <label class="sr-only" for="ik_from">From date</label>
      <input class="input" type="date" id="ik_from" name="date_from" value="<?= e($filters['date_from']) ?>" title="From date">
    </div>
    <div class="field" style="margin:0">
      <label class="sr-only" for="ik_to">To date</label>
      <input class="input" type="date" id="ik_to" name="date_to" value="<?= e($filters['date_to']) ?>" title="To date">
    </div>
    <button class="btn btn-brand" type="submit">Filter</button>
    <?php if ($query): ?><a class="btn btn-light" href="<?= e(base_url('admin/inkind')) ?>">Clear</a><?php endif; ?>
  </form>

  <p class="text-muted"><?= e(number_format($total)) ?> donation<?= $total === 1 ? '' : 's' ?> found.</p>

  <?php if ($result['rows'] === []): ?>
    <div class="empty-state">
      <h3>No in-kind donations found</h3>
      <p><?= $query ? 'Try clearing the filters or widening the date range.' : 'Goods donations submitted on the public Donate Goods form will appear here for verification.' ?></p>
      <?php if ($query): ?><a class="btn btn-brand" href="<?= e(base_url('admin/inkind')) ?>">Show everything</a><?php endif; ?>
    </div>
  <?php else: ?>
  <?php $canDelete = isset($can) && $can('inkind.delete'); ?>
  <?php if ($canDelete): ?>
  <form method="post" action="<?= e(base_url('admin/inkind/bulk-delete')) ?>" data-bulk data-confirm-template="Permanently delete {n} selected in-kind donation(s) and their photos? Verified ones are skipped. This cannot be undone.">
    <?= csrf_field() ?>
    <?php $bulkNoun = 'in-kind donations'; require __DIR__ . '/../../partials/bulk-delete-bar.php'; ?>
  <?php endif; ?>
  <div class="table-wrap">
    <table class="data-table">
      <thead><tr><?php if ($canDelete): ?><th style="width:32px"><input type="checkbox" data-bulk-all aria-label="Select all deletable donations"></th><?php endif; ?><th>Reference</th><th>Donor</th><th>Items donated</th><th>Status</th><th>Certificate</th></tr></thead>
      <tbody>
      <?php foreach ($result['rows'] as $r): ?>
        <tr>
          <?php if ($canDelete): $why = \App\Services\RecordDeletionService::inkindBlocker($r); ?>
            <td><input type="checkbox" name="ids[]" value="<?= (int) $r['id'] ?>" data-bulk-item <?= $why !== null ? 'disabled title="' . e($why) . '"' : '' ?> aria-label="Select <?= e((string) $r['public_reference']) ?>"></td>
          <?php endif; ?>
          <td style="white-space:nowrap">
            <a class="mono" href="<?= e(base_url('admin/inkind/' . (int) $r['id'])) ?>"><strong><?= e((string) $r['public_reference']) ?></strong></a>
            <div class="muted" style="font-size:12px"><?= e(dt((string) $r['created_at'], 'j M Y · H:i')) ?></div>
          </td>
          <td>
            <?= e((string) $r['donor_name']) ?>
            <div class="muted" style="font-size:12px"><?= e((string) $r['donor_phone']) ?><?= !empty($r['donor_city']) ? ' · ' . e((string) $r['donor_city']) : '' ?></div>
          </td>
          <td class="wrap" style="min-width:200px;max-width:300px">
            <?= e(mb_strimwidth((string) $r['summary'], 0, 100, '…')) ?>
            <div class="muted" style="font-size:12px"><?= number_format((int) $r['qty_total']) ?> in total · <?= $r['handover_method'] === 'pickup' ? 'We collect' : 'Donor drops off' ?><?= !empty($r['handover_date']) ? ' · ' . e(dt((string) $r['handover_date'], 'j M')) : '' ?></div>
          </td>
          <td style="white-space:nowrap"><span class="<?= e(status_badge_class((string) $r['status'] === 'verified' ? 'approved' : (string) $r['status'])) ?>"><?= e($labels[$r['status']] ?? (string) $r['status']) ?></span>
            <?php if ($r['status'] === 'pending'): ?><div style="margin-top:8px"><a class="btn btn-brand btn-sm" href="<?= e(base_url('admin/inkind/' . (int) $r['id'])) ?>">Review</a></div><?php endif; ?></td>
          <td>
            <?php if (!empty($r['certificate_number'])): ?>
              <span class="mono" style="font-size:12px"><?= e((string) $r['certificate_number']) ?></span><br>
              <span class="<?= e($mailBadge[$r['certificate_email']][0] ?? 'badge badge-grey') ?>"><?= e($mailBadge[$r['certificate_email']][1] ?? '—') ?></span>
            <?php else: ?><span class="muted">&mdash;</span><?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php if ($canDelete): ?></form><?php endif; ?>
  <?php require __DIR__ . '/../../partials/pagination.php'; ?>
  <?php endif; ?>
</section>
