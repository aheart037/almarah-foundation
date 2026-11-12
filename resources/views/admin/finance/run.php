<?php
/**
 * One reconciliation run: dashboard cards, every donation in the period, and the discrepancy items.
 * @var array $run @var array $items @var array $issues @var array $summary @var array $donations @var int $donationTotal @var int $page @var int $lastPage
 */
$open = count(array_filter($items, static fn ($i) => $i['resolved_at'] === null));
?>
<div class="dash-head">
  <div>
    <h1>Reconciliation #<?= (int) $run['id'] ?></h1>
    <p class="sub">
      <?= e(substr((string) $run['period_start'], 0, 10)) ?> → <?= e(substr((string) $run['period_end'], 0, 10)) ?>
      · <?= e((string) ($run['gateway_name'] ?? 'All methods')) ?>
      · run by <?= e((string) ($run['run_by_name'] ?? 'system')) ?> on <?= e(dt((string) $run['created_at'], 'j M Y H:i')) ?>
    </p>
  </div>
  <a class="btn btn-light" href="<?= e(base_url('admin/finance')) ?>">Back to finance desk</a>
</div>

<div class="stat-cards">
  <div class="stat-card"><div class="k">Donations in period</div><div class="v"><?= (int) $run['donations_count'] ?></div><div class="d"><?= e(money((int) $run['donations_minor'])) ?> PKR created (all currencies counted above)</div></div>
  <div class="stat-card brand"><div class="k">Verified PKR</div><div class="v"><?= (int) $run['verified_count'] ?></div><div class="d"><?= e(money((int) $run['verified_minor'])) ?> independently verified</div></div>
  <div class="stat-card"><div class="k">Pending PKR</div><div class="v"><?= (int) $run['pending_count'] ?></div><div class="d"><?= e(money((int) $run['pending_minor'])) ?> · <?= (int) $run['failed_count'] ?> failed</div></div>
  <div class="stat-card"><div class="k">Discrepancies</div><div class="v"><?= (int) $run['mismatch_count'] ?></div><div class="d"><?= $open ?> still open</div></div>
</div>

<?php
$donations = $donations ?? []; $donationTotal = (int) ($donationTotal ?? count($donations)); $page = (int) ($page ?? 1); $lastPage = (int) ($lastPage ?? 1);
$perPage = 100; $from = $donationTotal === 0 ? 0 : ($page - 1) * $perPage + 1; $to = ($page - 1) * $perPage + count($donations);
?>
<section class="panel" id="run-donations">
  <h2>All donations in this period</h2>
  <p class="muted" style="margin-top:-4px">
    <?= $donationTotal === 0 ? 'No donations were created in this period.' : 'Showing ' . number_format($from) . '–' . number_format($to) . ' of ' . number_format($donationTotal) . ' donation' . ($donationTotal === 1 ? '' : 's') . ' created between ' . e(substr((string) $run['period_start'], 0, 10)) . ' and ' . e(substr((string) $run['period_end'], 0, 10)) . ' (' . e((string) ($run['gateway_name'] ?? 'all methods')) . ').' ?>
  </p>
  <?php if ($donations !== []): ?>
  <div class="table-wrap">
    <table class="table">
      <thead><tr><th>#</th><th>Date</th><th style="min-width:150px">Donation</th><th>Method</th><th>Reference</th><th class="num">Amount</th><th>Payment</th><th>Verification</th><th>Reconciliation</th></tr></thead>
      <tbody>
      <?php foreach ($donations as $n => $d):
        $method = !empty($d['gateway_name']) ? (string) $d['gateway_name'] : ucwords(str_replace('_', ' ', (string) ($d['payment_method'] ?: ($d['source'] ?: 'offline'))));
        $ref = trim((string) ($d['txn_ref'] ?? '')) !== '' ? (string) $d['txn_ref'] : (string) ($d['bank_reference'] ?? '');
      ?>
        <tr>
          <td class="muted"><?= (int) ($from + $n) ?></td>
          <td style="white-space:nowrap"><?= e(dt((string) $d['created_at'], 'j M Y')) ?><div class="muted" style="font-size:12px"><?= e(dt((string) $d['created_at'], 'H:i')) ?> UTC<?= !empty($d['transaction_at']) && substr((string) $d['transaction_at'], 0, 10) !== substr((string) $d['created_at'], 0, 10) ? ' · paid ' . e(dt((string) $d['transaction_at'], 'j M')) : '' ?></div></td>
          <td>
            <a class="mono" href="<?= e(base_url('admin/donations/' . (int) $d['id'])) ?>"><?= e((string) $d['public_reference']) ?></a>
            <div class="muted" style="font-size:12px"><?= e((string) ($d['donor_name'] ?? 'Anonymous')) ?></div>
          </td>
          <td><?= e($method) ?></td>
          <td class="mono" style="font-size:12px;word-break:break-all"><?= $ref !== '' ? e($ref) : '&mdash;' ?></td>
          <td class="num" style="white-space:nowrap"><?= e(money((int) $d['amount_minor'], (string) $d['currency'])) ?></td>
          <td><span class="<?= e(status_badge_class((string) $d['status'])) ?>"><?= e((string) $d['status']) ?></span></td>
          <td><span class="<?= e(status_badge_class((string) $d['verification_status'])) ?>"><?= e(ucwords(str_replace('_', ' ', (string) $d['verification_status']))) ?></span></td>
          <td>
            <?php if ((int) $d['open_flags'] > 0): ?><span class="badge badge-amber">Discrepancy</span>
            <?php elseif ((int) $d['flag_count'] > 0): ?><span class="badge badge-green">Resolved</span>
            <?php else: ?><span class="muted">Matched</span><?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php $total = $donationTotal; $query = []; require __DIR__ . '/../../partials/pagination.php'; ?>
  <?php endif; ?>
</section>

<?php if (!empty($summary['issues'])): ?>
<section class="panel">
  <h2>Issue breakdown</h2>
  <div style="display:flex;flex-wrap:wrap;gap:8px">
    <?php foreach ($summary['issues'] as $key => $n): ?>
      <span class="badge badge-amber"><?= e($issues[$key] ?? $key) ?> · <?= (int) $n ?></span>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<section class="panel">
  <h2>Items</h2>
  <?php if ($items === []): ?>
    <p class="muted">No discrepancies found — every donation in this period matches its gateway record.</p>
  <?php else: ?>
  <div class="table-wrap">
    <table class="table">
      <thead><tr><th style="min-width:150px">Donation</th><th style="min-width:200px">Issue</th><th>Details</th><th>Status</th><th style="min-width:280px">Resolution</th></tr></thead>
      <tbody>
      <?php foreach ($items as $i): ?>
        <tr>
          <td>
            <a class="mono" href="<?= e(base_url('admin/donations/' . (int) $i['donation_id'])) ?>"><?= e((string) $i['public_reference']) ?></a>
            <div class="muted" style="font-size:12px"><?= e((string) ($i['donor_name'] ?? 'Anonymous')) ?> · <?= e(money((int) $i['amount_minor'], (string) $i['currency'])) ?></div>
          </td>
          <td><?= e($issues[$i['issue']] ?? (string) $i['issue']) ?></td>
          <td class="wrap" style="max-width:260px"><?= e((string) $i['details']) ?></td>
          <td><span class="<?= e(status_badge_class((string) $i['donation_status'])) ?>"><?= e((string) $i['donation_status']) ?></span></td>
          <td>
            <?php if ($i['resolved_at'] !== null): ?>
              <span class="badge badge-green">Resolved</span>
              <div class="muted" style="font-size:12px;margin-top:4px"><?= e((string) $i['resolution_note']) ?> — <?= e((string) ($i['resolved_by_name'] ?? '')) ?>, <?= e(dt((string) $i['resolved_at'], 'j M H:i')) ?></div>
            <?php else: ?>
              <form method="post" action="<?= e(base_url('admin/finance/items/' . (int) $i['id'] . '/resolve')) ?>" style="display:flex;gap:6px;align-items:flex-start">
                <?= csrf_field() ?>
                <input type="hidden" name="run_id" value="<?= (int) $run['id'] ?>">
                <input class="input" name="note" placeholder="How was this resolved?" required minlength="5" maxlength="500" style="flex:1">
                <button class="btn btn-light btn-sm" type="submit">Resolve</button>
              </form>
              <a class="btn btn-outline-brand btn-sm" style="margin-top:6px" href="<?= e(base_url('admin/finance/donations/' . (int) $i['donation_id'] . '/adjust')) ?>">Adjust donation</a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</section>
