<?php
/** @var array $bank @var array $filters @var array $result @var int $page @var int $lastPage */
$labels = ['pending_verification' => 'Pending verification', 'verified' => 'Verified', 'rejected' => 'Rejected', 'unreviewed' => 'Not reviewed (legacy)', 'gateway_unconfirmed' => 'Gateway not confirmed'];
$badge = ['verified' => 'badge badge-green', 'pending_verification' => 'badge badge-amber', 'rejected' => 'badge badge-red'];
$bid = (int) $bank['id'];
$query = array_filter($filters, static fn ($v): bool => $v !== '');
?>
<div class="dash-head">
  <div><h1><?= e((string) $bank['bank_name']) ?> ledger</h1>
    <p class="sub"><?= e(trim((string) ($bank['account_title'] ?? '') . (!empty($bank['account_number']) ? ' · ' . $bank['account_number'] : ''))) ?: 'Payments made through the online payment gateway' ?>. Times are UTC.</p></div>
  <div class="sr-actions">
    <a class="btn btn-light" href="<?= e(base_url('admin/ledger/banks')) ?>">&larr; All banks</a>
    <?php if (isset($can) && $can('reports.view')): ?><a class="btn btn-primary" href="<?= e(base_url('admin/ledger/banks/' . $bid . '/export' . ($query ? '?' . http_build_query($query) : ''))) ?>">Download CSV</a><?php endif; ?>
  </div>
</div>
<div class="stat-grid">
  <?php foreach ($result['confirmed'] as $cur => $c): ?>
    <div class="stat-card"><div class="k">Confirmed <?= e($cur) ?></div><div class="v"><?= e(money((int) $c['minor'], (string) $cur)) ?></div><div class="d"><?= (int) $c['n'] ?> transaction(s)</div></div>
  <?php endforeach; ?>
  <?php if (!$result['confirmed']): ?><div class="stat-card"><div class="k">Confirmed</div><div class="v">0</div></div><?php endif; ?>
  <div class="stat-card"><div class="k">Records shown</div><div class="v"><?= (int) $result['total'] ?></div></div>
</div>
<section class="panel">
  <form class="toolbar" method="get" action="<?= e(base_url('admin/ledger/banks/' . $bid)) ?>">
    <input class="input" type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="Donor, reference, transaction ID, phone">
    <select class="select-field" name="status" aria-label="Status"><option value="">Any status</option>
      <?php foreach ($labels as $k => $l): ?><option value="<?= e($k) ?>" <?= $filters['status'] === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select>
    <select class="select-field" name="mode" aria-label="Mode"><option value="">Any mode</option>
      <?php foreach (\App\Services\BankLedgerService::MODES as $k => $l): ?><option value="<?= e($k) ?>" <?= $filters['mode'] === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select>
    <input class="input" type="date" name="date_from" value="<?= e($filters['date_from']) ?>" aria-label="From">
    <input class="input" type="date" name="date_to" value="<?= e($filters['date_to']) ?>" aria-label="To">
    <button class="btn btn-light" type="submit">Filter</button>
  </form>
  <div class="table-wrap"><table class="table">
    <thead><tr><th>Date &amp; time</th><th>Donor</th><th>Mode</th><th>Website reference</th><th>Bank transaction ID / slip no.</th><th class="num">Amount</th><th>Status</th><th>Verified</th></tr></thead>
    <tbody>
    <?php foreach ($result['rows'] as $r): ?>
      <tr>
        <td><?= e(dt((string) $r['txn_at'], 'j M Y')) ?><br><small class="muted"><?= e(dt((string) $r['txn_at'], 'H:i')) ?></small></td>
        <td><?= $r['anonymous'] ? 'Anonymous' : e((string) $r['donor_name']) ?><br><small class="muted"><?= e((string) $r['donor_email']) ?> <?= e((string) $r['donor_phone']) ?></small></td>
        <td><?= e(\App\Services\BankLedgerService::modeLabel((string) $r['source'], $r['gateway_code'])) ?></td>
        <td><a href="<?= e(base_url('admin/donations/' . (int) $r['id'])) ?>"><?= e((string) $r['public_reference']) ?></a></td>
        <td><?= e((string) ($r['bank_reference'] ?: $r['provider_txn'] ?: '—')) ?></td>
        <td class="num"><?= e(money((int) $r['amount_minor'], (string) $r['currency'])) ?></td>
        <td><span class="<?= e($badge[$r['ledger_status']] ?? 'badge badge-grey') ?>"><?= e($labels[$r['ledger_status']] ?? $r['ledger_status']) ?></span></td>
        <td><?= $r['verified_at'] ? e(dt((string) $r['verified_at'], 'j M Y H:i')) . '<br><small class="muted">' . e((string) ($r['verified_by_email'] ?? '')) . '</small>' : '—' ?></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$result['rows']): ?><tr><td colspan="8" class="muted">No transactions match.</td></tr><?php endif; ?>
    </tbody></table></div>
  <p class="muted">Page <?= (int) $page ?> of <?= (int) $lastPage ?> &middot; <?= (int) $result['total'] ?> record(s)
    <?php if ($page > 1): ?><a href="?<?= e(http_build_query(array_merge($filters, ['page' => $page - 1]))) ?>">Previous</a><?php endif; ?>
    <?php if ($page < $lastPage): ?><a href="?<?= e(http_build_query(array_merge($filters, ['page' => $page + 1]))) ?>">Next</a><?php endif; ?></p>
</section>
