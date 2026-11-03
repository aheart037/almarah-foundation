<?php
/**
 * Finance desk: monthly summary, reconciliation launcher, recent runs,
 * recent manual adjustments.
 *
 * @var string $month  YYYY-MM
 * @var array  $summary @var array $trend @var array $runs @var array $adjustments
 * @var int    $stalePending @var int $openIssues @var int $pendingTxns @var array $gateways
 */
$monthLabel = date('F Y', strtotime($month . '-01'));
$prev = date('Y-m', strtotime($month . '-01 -1 month'));
$next = date('Y-m', strtotime($month . '-01 +1 month'));
$maxTrend = max(1, ...array_map(static fn ($r) => (int) $r['total'], $trend ?: [['total' => 1]]));
?>
<div class="dash-head">
  <div>
    <h1>Finance desk</h1>
    <p class="sub">Reconcile gateway payments, record bank transfers and export monthly ledgers.</p>
  </div>
  <div class="sr-actions">
    <a class="btn btn-brand" href="<?= e(base_url('admin/finance/offline')) ?>">+ Record offline donation</a>
    <a class="btn btn-outline-brand" href="<?= e(base_url('admin/finance/export?month=' . $month)) ?>">Export <?= e($monthLabel) ?> CSV</a>
  </div>
</div>

<div class="stat-cards">
  <div class="stat-card brand">
    <div class="k"><?= e($monthLabel) ?></div>
    <div class="v"><?= e(money_short((int) $summary['completed'])) ?></div>
    <div class="d"><?= (int) $summary['count'] ?> confirmed donation<?= (int) $summary['count'] === 1 ? '' : 's' ?></div>
  </div>
  <div class="stat-card">
    <div class="k">Open reconciliation items</div>
    <div class="v"><?= (int) $openIssues ?></div>
    <div class="d">unresolved discrepancies across all runs</div>
  </div>
  <div class="stat-card">
    <div class="k">Stale pending</div>
    <div class="v"><?= (int) $stalePending ?></div>
    <div class="d">pending &gt; 24 h · <?= (int) $pendingTxns ?> transaction<?= $pendingTxns === 1 ? '' : 's' ?> awaiting gateway check</div>
  </div>
</div>

<div class="grid-2">
  <section class="panel">
    <div class="panel-head">
      <h2>Summary — <?= e($monthLabel) ?></h2>
      <div class="sr-actions">
        <a class="btn btn-light btn-sm" href="<?= e(base_url('admin/finance?month=' . $prev)) ?>">&larr; <?= e(date('M Y', strtotime($prev . '-01'))) ?></a>
        <?php if ($next <= gmdate('Y-m')): ?><a class="btn btn-light btn-sm" href="<?= e(base_url('admin/finance?month=' . $next)) ?>"><?= e(date('M Y', strtotime($next . '-01'))) ?> &rarr;</a><?php endif; ?>
      </div>
    </div>
    <h3 class="muted" style="font-size:13px;margin:12px 0 6px">By purpose</h3>
    <table class="table">
      <tbody>
      <?php foreach ($summary['by_purpose'] as $r): ?>
        <tr><td><?= e((string) $r['label']) ?></td><td class="num"><?= (int) $r['n'] ?></td><td class="num"><?= e(money((int) $r['total'])) ?></td></tr>
      <?php endforeach; ?>
      <?php if ($summary['by_purpose'] === []): ?><tr><td class="muted" colspan="3">No confirmed donations this month.</td></tr><?php endif; ?>
      </tbody>
    </table>
    <h3 class="muted" style="font-size:13px;margin:16px 0 6px">By method</h3>
    <table class="table">
      <tbody>
      <?php foreach ($summary['by_method'] as $r): ?>
        <tr><td><?= e(ucwords(str_replace('_', ' ', (string) $r['label']))) ?></td><td class="num"><?= (int) $r['n'] ?></td><td class="num"><?= e(money((int) $r['total'])) ?></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <h3 class="muted" style="font-size:13px;margin:16px 0 6px">All statuses (created this month)</h3>
    <div style="display:flex;flex-wrap:wrap;gap:8px">
      <?php foreach ($summary['by_status'] as $r): ?>
        <span class="<?= e(status_badge_class((string) $r['status'])) ?>"><?= e((string) $r['status']) ?> · <?= (int) $r['n'] ?> · <?= e(money_short((int) $r['total'])) ?></span>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="panel">
    <h2>Run a reconciliation</h2>
    <p class="muted">Compares donations against gateway transactions for the period and lists every discrepancy for review.</p>
    <form method="post" action="<?= e(base_url('admin/finance/reconcile')) ?>" class="stack">
      <?= csrf_field() ?>
      <div class="form-row">
        <div class="field">
          <label for="period_type">Period</label>
          <select class="select-field" id="period_type" name="period_type" data-toggle-period>
            <option value="monthly">Calendar month</option>
            <option value="daily">Single day</option>
            <option value="custom">Custom range</option>
          </select>
        </div>
        <div class="field">
          <label for="gateway_id">Gateway</label>
          <select class="select-field" id="gateway_id" name="gateway_id">
            <option value="">All (incl. offline)</option>
            <?php foreach ($gateways as $g): ?><option value="<?= (int) $g['id'] ?>"><?= e((string) $g['name']) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="field" data-period="monthly">
          <label for="rec_month">Month</label>
          <input class="input" type="month" id="rec_month" name="month" value="<?= e($month) ?>" max="<?= e(gmdate('Y-m')) ?>">
        </div>
        <div class="field" data-period="daily" hidden>
          <label for="rec_day">Day</label>
          <input class="input" type="date" id="rec_day" name="day" value="<?= e(gmdate('Y-m-d')) ?>">
        </div>
        <div class="field" data-period="custom" hidden>
          <label for="rec_start">From</label>
          <input class="input" type="date" id="rec_start" name="start" value="<?= e(gmdate('Y-m-01')) ?>">
        </div>
        <div class="field" data-period="custom" hidden>
          <label for="rec_end">To</label>
          <input class="input" type="date" id="rec_end" name="end" value="<?= e(gmdate('Y-m-d')) ?>">
        </div>
      </div>
      <div class="sr-actions">
        <button class="btn btn-brand" type="submit">Run reconciliation</button>
      </div>
    </form>
    <hr>
    <form method="post" action="<?= e(base_url('admin/finance/sync-pending')) ?>">
      <?= csrf_field() ?>
      <p class="muted" style="margin:0 0 8px">Ask Meezan / Etisalat for the current status of every pending transaction (the same job the <code>payments:reconcile</code> cron runs).</p>
      <button class="btn btn-outline-brand" type="submit">Check <?= (int) $pendingTxns ?> pending transaction<?= $pendingTxns === 1 ? '' : 's' ?> now</button>
    </form>
  </section>
</div>

<section class="panel">
  <h2>Last 12 months</h2>
  <div class="bars" style="display:flex;align-items:flex-end;gap:6px;height:120px">
    <?php foreach ($trend as $r): $h = max(4, (int) round(100 * (int) $r['total'] / $maxTrend)); ?>
      <a href="<?= e(base_url('admin/finance?month=' . $r['ym'])) ?>" title="<?= e($r['ym'] . ': ' . money((int) $r['total']) . ' (' . (int) $r['n'] . ')') ?>"
         style="flex:1;max-width:72px;display:flex;flex-direction:column;justify-content:flex-end;align-items:center;gap:4px;text-decoration:none;color:inherit">
        <span style="display:block;width:100%;height:<?= $h ?>px;background:var(--brand,#1e5e3a);border-radius:4px 4px 0 0"></span>
        <small class="muted" style="font-size:10px"><?= e(date('M', strtotime($r['ym'] . '-01'))) ?></small>
      </a>
    <?php endforeach; ?>
    <?php if ($trend === []): ?><p class="muted">No confirmed donations yet.</p><?php endif; ?>
  </div>
</section>

<div class="grid-2">
  <section class="panel">
    <h2>Recent reconciliation runs</h2>
    <?php if ($runs === []): ?><p class="muted">No runs yet.</p><?php else: ?>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>#</th><th>Period</th><th>Donations</th><th>Issues</th><th>Open</th><th>By</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($runs as $r): ?>
          <tr>
            <td><?= (int) $r['id'] ?></td>
            <td style="white-space:nowrap"><?= e(substr((string) $r['period_start'], 0, 10)) ?> → <?= e(substr((string) $r['period_end'], 0, 10)) ?><div class="muted" style="font-size:12px"><?= e((string) ($r['gateway_name'] ?? 'All')) ?></div></td>
            <td class="num"><?= (int) $r['donations_count'] ?><div class="muted" style="font-size:12px"><?= e(money_short((int) $r['verified_minor'])) ?> verified</div></td>
            <td class="num"><?= (int) $r['mismatch_count'] ?></td>
            <td class="num"><?= (int) $r['open_items'] > 0 ? '<span class="badge badge-amber">' . (int) $r['open_items'] . '</span>' : '<span class="badge badge-green">0</span>' ?></td>
            <td><?= e((string) ($r['run_by_name'] ?? 'system')) ?><div class="muted" style="font-size:12px"><?= e(dt((string) $r['created_at'], 'j M H:i')) ?></div></td>
            <td><a class="btn btn-light btn-sm" href="<?= e(base_url('admin/finance/reconciliations/' . (int) $r['id'])) ?>">Open</a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </section>

  <section class="panel">
    <h2>Recent manual adjustments</h2>
    <?php if ($adjustments === []): ?><p class="muted">No adjustments recorded.</p><?php else: ?>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Donation</th><th>Change</th><th>Reason</th><th>By</th></tr></thead>
        <tbody>
        <?php foreach ($adjustments as $a): ?>
          <tr>
            <td><a href="<?= e(base_url('admin/donations/' . (int) $a['donation_id'])) ?>" class="mono"><?= e((string) $a['public_reference']) ?></a></td>
            <td style="min-width:140px"><?= e(str_replace('_', ' ', (string) $a['adjustment_type'])) ?><div class="muted" style="font-size:12px"><?= e((string) $a['old_status']) ?> → <?= e((string) $a['new_status']) ?><?= (int) $a['old_amount_minor'] !== (int) $a['new_amount_minor'] ? ' · ' . e(money((int) $a['old_amount_minor'])) . ' → ' . e(money((int) $a['new_amount_minor'])) : '' ?></div></td>
            <td class="wrap" style="max-width:260px"><?= e((string) $a['reason']) ?><?php if (!empty($a['evidence_reference'])): ?><div class="muted" style="font-size:12px">Ref: <?= e((string) $a['evidence_reference']) ?></div><?php endif; ?></td>
            <td><?= e((string) ($a['actor_name'] ?? '—')) ?><div class="muted" style="font-size:12px"><?= e(dt((string) $a['created_at'], 'j M H:i')) ?></div></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </section>
</div>
