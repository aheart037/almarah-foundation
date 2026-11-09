<?php
/** @var array $filters @var array $result @var bool $showAll @var int $page @var int $lastPage */
$labels = ['pending_verification' => 'Waiting for finance', 'verified' => 'Verified', 'rejected' => 'Rejected'];
$badge = ['verified' => 'badge badge-green', 'pending_verification' => 'badge badge-amber', 'rejected' => 'badge badge-red'];
$query = array_filter(array_diff_key($filters, ['channel' => 1, 'recorded_by' => 1]), static fn ($v): bool => $v !== '');
?>
<div class="dash-head">
  <div><h1><?= $showAll ? 'Offline donation entries' : 'My offline entries' ?></h1>
    <p class="sub">Donations collected by staff. Each entry is checked by finance before it counts as confirmed and the donor's certificate is sent.</p></div>
  <div class="sr-actions">
    <?php if (isset($can) && $can('donations.record')): ?><a class="btn btn-primary" href="<?= e(base_url('admin/finance/offline')) ?>">+ Record donation</a><?php endif; ?>
    <?php if (isset($can) && $can('donations.verify')): ?><a class="btn btn-light" href="<?= e(base_url('admin/donations?verification_status=submitted')) ?>">Verification queue</a><?php endif; ?>
  </div>
</div>
<div class="stat-grid">
  <?php foreach ($result['confirmed'] as $cur => $minor): ?><div class="stat-card"><div class="k">Verified <?= e($cur) ?></div><div class="v"><?= e(money((int) $minor, (string) $cur)) ?></div></div><?php endforeach; ?>
  <?php foreach ($result['pending'] as $cur => $minor): ?><div class="stat-card"><div class="k">Waiting for finance <?= e($cur) ?></div><div class="v"><?= e(money((int) $minor, (string) $cur)) ?></div><div class="d">Not counted yet</div></div><?php endforeach; ?>
  <?php if (!$result['confirmed'] && !$result['pending']): ?><div class="stat-card"><div class="k">Entries</div><div class="v"><?= (int) $result['total'] ?></div></div><?php endif; ?>
</div>
<section class="panel">
  <form class="toolbar" method="get" action="<?= e(base_url('admin/offline-donations')) ?>">
    <input class="input" type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="Donor, reference, slip number">
    <select class="select-field" name="status" aria-label="Status"><option value="">Any status</option>
      <?php foreach ($labels as $k => $l): ?><option value="<?= e($k) ?>" <?= $filters['status'] === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select>
    <input class="input" type="date" name="date_from" value="<?= e($filters['date_from']) ?>" aria-label="From">
    <input class="input" type="date" name="date_to" value="<?= e($filters['date_to']) ?>" aria-label="To">
    <button class="btn btn-light" type="submit">Filter</button>
  </form>
  <div class="table-wrap"><table class="table">
    <thead><tr><th>Date</th><th>Reference</th><th>Donor</th><th>Mode</th><th>Bank</th><th>Slip no. / transaction ID</th><th class="num">Amount</th><th>Status</th><?php if ($showAll): ?><th>Entered by</th><?php endif; ?></tr></thead>
    <tbody>
    <?php foreach ($result['rows'] as $r): ?>
      <tr>
        <td><?= e(dt((string) $r['donation_date'], 'j M Y')) ?></td>
        <td><a href="<?= e(base_url('admin/donations/' . (int) $r['id'])) ?>"><?= e((string) $r['public_reference']) ?></a></td>
        <td><?= $r['anonymous'] ? 'Anonymous' : e((string) $r['donor_name']) ?><br><small class="muted"><?= e((string) $r['donor_phone']) ?></small></td>
        <td><?= e(\App\Services\BankLedgerService::modeLabel((string) $r['source'], null)) ?></td>
        <td><?= e((string) ($r['bank_name'] ?? '—')) ?></td>
        <td><?= e((string) ($r['bank_reference'] ?: '—')) ?></td>
        <td class="num"><?= e(money((int) $r['amount_minor'], (string) $r['currency'])) ?></td>
        <td><span class="<?= e($badge[$r['ledger_status']] ?? 'badge badge-grey') ?>"><?= e($labels[$r['ledger_status']] ?? $r['ledger_status']) ?></span></td>
        <?php if ($showAll): ?><td><?= e((string) ($r['recorded_by_email'] ?? '—')) ?></td><?php endif; ?>
      </tr>
    <?php endforeach; ?>
    <?php if (!$result['rows']): ?><tr><td colspan="9" class="muted">No offline entries yet.</td></tr><?php endif; ?>
    </tbody></table></div>
  <p class="muted">Page <?= (int) $page ?> of <?= (int) $lastPage ?> &middot; <?= (int) $result['total'] ?> record(s)
    <?php if ($page > 1): ?><a href="?<?= e(http_build_query(array_merge($query, ['page' => $page - 1]))) ?>">Previous</a><?php endif; ?>
    <?php if ($page < $lastPage): ?><a href="?<?= e(http_build_query(array_merge($query, ['page' => $page + 1]))) ?>">Next</a><?php endif; ?></p>
</section>
