<?php
/** @var array $filters @var array $result @var array $banks @var int $page @var int $lastPage */
$labels = ['pending_verification' => 'Pending verification', 'verified' => 'Verified', 'rejected' => 'Rejected', 'unreviewed' => 'Not reviewed (legacy)', 'gateway_unconfirmed' => 'Gateway not confirmed'];
$channels = ['gateway' => 'Online – gateway', 'online_slip' => 'Online – bank slip', 'offline' => 'Offline – staff'];
$badge = ['verified' => 'badge badge-green', 'pending_verification' => 'badge badge-amber', 'rejected' => 'badge badge-red'];
?>
<div class="dash-head">
  <div><h1>Donation ledger</h1><p class="sub">Only verified donations count toward confirmed totals. Records are never deleted.</p></div>
  <div class="sr-actions">
    <?php if (isset($can) && $can('donations.record')): ?><a class="btn btn-primary" href="<?= e(base_url('admin/finance/offline')) ?>">Record offline donation</a><?php endif; ?>
    <a class="btn btn-primary" href="<?= e(base_url('admin/ledger/banks')) ?>">Bank ledgers</a>
    <a class="btn btn-light" href="<?= e(base_url('admin/donations')) ?>">All donations</a>
  </div>
</div>
<div class="stat-grid">
  <?php foreach ($result['confirmed'] as $cur => $minor): ?>
    <div class="stat-card"><div class="k">Confirmed <?= e($cur) ?></div><div class="v"><?= e(money((int) $minor, (string) $cur)) ?></div></div>
  <?php endforeach; ?>
  <?php if (!$result['confirmed']): ?><div class="stat-card"><div class="k">Confirmed</div><div class="v">0</div></div><?php endif; ?>
  <?php foreach ($result['pending'] as $cur => $minor): ?>
    <div class="stat-card"><div class="k">Pending verification <?= e($cur) ?></div><div class="v"><?= e(money((int) $minor, (string) $cur)) ?></div><div class="d">Not counted</div></div>
  <?php endforeach; ?>
</div>
<section class="panel">
  <form class="toolbar" method="get" action="<?= e(base_url('admin/ledger')) ?>">
    <input class="input" type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="Reference, donor, email, phone or bank ref">
    <select class="select-field" name="channel" aria-label="Online or offline"><option value="">Online + offline</option>
      <option value="online" <?= $filters['channel'] === 'online' ? 'selected' : '' ?>>Online</option><option value="offline" <?= $filters['channel'] === 'offline' ? 'selected' : '' ?>>Offline</option></select>
    <select class="select-field" name="bank_account_id" aria-label="Bank"><option value="">Any bank</option>
      <?php foreach ($banks as $b): ?><option value="<?= (int) $b['id'] ?>" <?= (int) $filters['bank_account_id'] === (int) $b['id'] ? 'selected' : '' ?>><?= e($b['bank_name'] . ' (' . $b['currency'] . ')') ?></option><?php endforeach; ?></select>
    <select class="select-field" name="status" aria-label="Status"><option value="">Any status</option>
      <?php foreach ($labels as $k => $l): ?><option value="<?= e($k) ?>" <?= $filters['status'] === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select>
    <input class="input" type="date" name="date_from" value="<?= e($filters['date_from']) ?>" aria-label="From">
    <input class="input" type="date" name="date_to" value="<?= e($filters['date_to']) ?>" aria-label="To">
    <button class="btn btn-light" type="submit">Filter</button>
  </form>
  <div class="table-wrap"><table class="table">
    <thead><tr><th>Reference</th><th>Donor</th><th class="num">Amount</th><th>Date</th><th>Bank / method</th><th>Source</th><th>Status</th><th>Verified by</th><th>Verified on</th></tr></thead>
    <tbody>
    <?php foreach ($result['rows'] as $r): ?>
      <tr>
        <td><a href="<?= e(base_url('admin/donations/' . (int) $r['id'])) ?>"><?= e($r['public_reference']) ?></a></td>
        <td><?= $r['anonymous'] ? 'Anonymous' : e((string) $r['donor_name']) ?><br><small class="muted"><?= e((string) $r['donor_email']) ?> <?= e((string) $r['donor_phone']) ?></small></td>
        <td class="num"><?= e(money((int) $r['amount_minor'], (string) $r['currency'])) ?></td>
        <td><?= e(dt((string) $r['donation_date'], 'j M Y')) ?></td>
        <td><?= e((string) ($r['bank_name'] ?? $r['gateway_code'] ?? '—')) ?><br><small class="muted"><?= e(ucfirst(str_replace('_', ' ', (string) $r['source']))) ?></small></td>
        <td><?= e($channels[$r['channel']] ?? $r['channel']) ?></td>
        <td><span class="<?= e($badge[$r['ledger_status']] ?? 'badge badge-grey') ?>"><?= e($labels[$r['ledger_status']] ?? $r['ledger_status']) ?></span></td>
        <td><?= e((string) ($r['verified_by_email'] ?? ($r['channel'] === 'gateway' && $r['ledger_status'] === 'verified' ? 'Gateway' : '—'))) ?></td>
        <td><?= $r['verified_at'] ? e(dt((string) $r['verified_at'], 'j M Y')) : '—' ?></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$result['rows']): ?><tr><td colspan="9" class="muted">No donations match.</td></tr><?php endif; ?>
    </tbody></table></div>
  <p class="muted">Page <?= (int) $page ?> of <?= (int) $lastPage ?> &middot; <?= (int) $result['total'] ?> record(s)
    <?php if ($page > 1): ?><a href="?<?= e(http_build_query(array_merge($filters, ['page' => $page - 1]))) ?>">Previous</a><?php endif; ?>
    <?php if ($page < $lastPage): ?><a href="?<?= e(http_build_query(array_merge($filters, ['page' => $page + 1]))) ?>">Next</a><?php endif; ?></p>
</section>
