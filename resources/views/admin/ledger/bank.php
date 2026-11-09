<?php
/** @var array $bank @var array $filters @var array $result @var int $page @var int $lastPage */
$labels = ['pending_verification' => 'Pending verification', 'verified' => 'Verified', 'rejected' => 'Rejected', 'unreviewed' => 'Not reviewed (legacy)', 'gateway_unconfirmed' => 'Gateway not confirmed'];
$badge = ['verified' => 'badge badge-green', 'pending_verification' => 'badge badge-amber', 'rejected' => 'badge badge-red'];
$bid = (int) $bank['id'];
$query = array_filter($filters, static fn ($v): bool => $v !== '');
$page_url = static fn (int $p): string => '?' . http_build_query(array_merge($query, ['page' => $p]));
?>
<link rel="stylesheet" href="<?= e(asset('assets/css/bank-ledger.css')) ?>">
<div class="dash-head">
  <div>
    <div class="bl-crumb"><a href="<?= e(base_url('admin/ledger/banks')) ?>">Bank ledgers</a> &rsaquo; <?= e((string) $bank['bank_name']) ?></div>
    <h1><?= e((string) $bank['bank_name']) ?> ledger</h1>
    <p class="sub"><?php if ($bid === 0): ?>Payments made through the online payment gateway.<?php else: ?><?= e(trim((string) ($bank['account_title'] ?? ''))) ?><?= !empty($bank['account_number']) ? ' · Account ' . e((string) $bank['account_number']) : '' ?><?= !empty($bank['iban']) ? ' · IBAN ' . e((string) $bank['iban']) : '' ?><?php endif; ?> · Times are UTC.</p>
  </div>
  <div class="sr-actions">
    <a class="btn btn-light" href="<?= e(base_url('admin/ledger/banks')) ?>">&larr; All banks</a>
    <?php if (isset($can) && $can('reports.view')): ?><a class="btn btn-brand" href="<?= e(base_url('admin/ledger/banks/' . $bid . '/export' . ($query ? '?' . http_build_query($query) : ''))) ?>">Download CSV</a><?php endif; ?>
  </div>
</div>

<div class="bl-kpis">
  <?php $first = true; foreach ($result['confirmed'] as $cur => $c): ?>
    <div class="bl-kpi <?= $first ? 'accent' : '' ?>"><div class="k">Confirmed · <?= e($cur) ?></div><div class="v"><?= e(money((int) $c['minor'], (string) $cur)) ?></div><div class="d"><?= (int) $c['n'] ?> transaction<?= $c['n'] === 1 ? '' : 's' ?></div></div>
  <?php $first = false; endforeach; ?>
  <?php if (!$result['confirmed']): ?><div class="bl-kpi accent"><div class="k">Confirmed</div><div class="v">0</div><div class="d">None in this view</div></div><?php endif; ?>
  <div class="bl-kpi"><div class="k">Records shown</div><div class="v"><?= (int) $result['total'] ?></div><div class="d">matching your filters</div></div>
</div>

<form class="bl-filter" method="get" action="<?= e(base_url('admin/ledger/banks/' . $bid)) ?>">
  <div style="grid-column:span 2"><label for="q">Search</label><input class="input" id="q" type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="Donor, reference, transaction ID, phone"></div>
  <div><label for="st">Status</label><select class="select-field" id="st" name="status"><option value="">Any status</option>
    <?php foreach ($labels as $k => $l): ?><option value="<?= e($k) ?>" <?= $filters['status'] === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></div>
  <div><label for="md">Mode</label><select class="select-field" id="md" name="mode"><option value="">Any mode</option>
    <?php foreach (\App\Services\BankLedgerService::MODES as $k => $l): ?><option value="<?= e($k) ?>" <?= $filters['mode'] === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></div>
  <div><label for="df">From</label><input class="input" id="df" type="date" name="date_from" value="<?= e($filters['date_from']) ?>"></div>
  <div><label for="dt">To</label><input class="input" id="dt" type="date" name="date_to" value="<?= e($filters['date_to']) ?>"></div>
  <div class="bl-actions"><button class="btn btn-brand" type="submit">Filter</button><a class="btn btn-light" href="<?= e(base_url('admin/ledger/banks/' . $bid)) ?>">Reset</a></div>
</form>

<section class="bl-panel">
  <div class="bl-tablewrap"><table class="bl-table">
    <thead><tr><th>Date &amp; time</th><th>Donor</th><th>Reference</th><th>Mode</th><th class="num">Amount</th><th>Status</th><th>Verified</th></tr></thead>
    <tbody>
    <?php foreach ($result['rows'] as $r): ?>
      <tr>
        <td data-label="Date &amp; time"><span><?= e(dt((string) $r['txn_at'], 'j M Y')) ?><span class="bl-sub"><?= e(dt((string) $r['txn_at'], 'H:i')) ?> UTC</span></span></td>
        <td data-label="Donor"><span><strong><?= $r['anonymous'] ? 'Anonymous' : e((string) $r['donor_name']) ?></strong><span class="bl-sub"><?= e(trim((string) $r['donor_email'] . ' ' . (string) $r['donor_phone'])) ?></span></span></td>
        <td data-label="Reference"><span><a class="bl-ref" href="<?= e(base_url('admin/donations/' . (int) $r['id'])) ?>"><?= e((string) $r['public_reference']) ?></a>
          <span class="bl-sub"><?= ($r['bank_reference'] || $r['provider_txn']) ? 'Txn / slip: <span class="bl-ref">' . e((string) ($r['bank_reference'] ?: $r['provider_txn'])) . '</span>' : 'No transaction ID' ?></span></span></td>
        <td data-label="Mode"><span class="bl-pill"><?= e(\App\Services\BankLedgerService::modeLabel((string) $r['source'], $r['gateway_code'])) ?></span></td>
        <td data-label="Amount" class="num"><span class="bl-amt"><?= e(money((int) $r['amount_minor'], (string) $r['currency'])) ?></span></td>
        <td data-label="Status"><span class="<?= e($badge[$r['ledger_status']] ?? 'badge badge-grey') ?>"><?= e($labels[$r['ledger_status']] ?? $r['ledger_status']) ?></span></td>
        <td data-label="Verified"><span><?= $r['verified_at'] ? e(dt((string) $r['verified_at'], 'j M Y H:i')) . '<span class="bl-sub">' . e((string) ($r['verified_by_email'] ?? '')) . '</span>' : '—' ?></span></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$result['rows']): ?><tr><td colspan="7" style="text-align:center;color:#7a6e73;padding:30px">No transactions match your filters.</td></tr><?php endif; ?>
    </tbody></table></div>
  <div class="bl-foot">
    <span>Page <?= (int) $page ?> of <?= (int) $lastPage ?> · <?= (int) $result['total'] ?> record(s)</span>
    <span class="bl-pager">
      <?php if ($page > 1): ?><a class="btn btn-light" href="<?= e($page_url($page - 1)) ?>">&larr; Previous</a><?php endif; ?>
      <?php if ($page < $lastPage): ?><a class="btn btn-light" href="<?= e($page_url($page + 1)) ?>">Next &rarr;</a><?php endif; ?>
    </span>
  </div>
</section>
