<?php
/** @var array $filters @var list<array> $summary */
$tot = []; $pend = [];
foreach ($summary as $r) {
    $c = (string) $r['currency'];
    $tot[$c] = ($tot[$c] ?? ['minor' => 0, 'n' => 0]);
    $tot[$c]['minor'] += (int) $r['confirmed_minor']; $tot[$c]['n'] += (int) $r['confirmed_count'];
    $pend[$c] = ($pend[$c] ?? 0) + (int) $r['pending_count'];
}
$qs = static fn (array $x = []): string => http_build_query(array_filter(array_merge(['date_from' => $filters['date_from'], 'date_to' => $filters['date_to']], $x), static fn ($v): bool => $v !== '' && $v !== null));
?>
<link rel="stylesheet" href="<?= e(asset('assets/css/bank-ledger.css')) ?>">
<div class="dash-head">
  <div><h1>Bank ledgers</h1><p class="sub">What each bank account has received. Only finance-verified (or gateway-confirmed) payments count as confirmed. Times are UTC.</p></div>
  <div class="sr-actions"><a class="btn btn-light" href="<?= e(base_url('admin/ledger')) ?>">All donations ledger</a></div>
</div>

<div class="bl-kpis">
  <?php $first = true; foreach ($tot as $cur => $t): ?>
    <div class="bl-kpi <?= $first ? 'accent' : '' ?>"><div class="k">Total confirmed · <?= e($cur) ?></div><div class="v"><?= e(money((int) $t['minor'], $cur)) ?></div><div class="d"><?= (int) $t['n'] ?> transaction<?= $t['n'] === 1 ? '' : 's' ?></div></div>
  <?php $first = false; endforeach; ?>
  <?php if (!$tot): ?><div class="bl-kpi accent"><div class="k">Total confirmed</div><div class="v">0</div><div class="d">No transactions yet</div></div><?php endif; ?>
  <div class="bl-kpi"><div class="k">Bank accounts</div><div class="v"><?= count(array_unique(array_column($summary, 'bank_id'))) ?></div><div class="d">with activity</div></div>
  <div class="bl-kpi"><div class="k">Waiting for finance</div><div class="v"><?= (int) array_sum($pend) ?></div><div class="d">not counted yet</div></div>
</div>

<form class="bl-filter" method="get" action="<?= e(base_url('admin/ledger/banks')) ?>">
  <div><label for="df">From date</label><input class="input" id="df" type="date" name="date_from" value="<?= e($filters['date_from']) ?>"></div>
  <div><label for="dt">To date</label><input class="input" id="dt" type="date" name="date_to" value="<?= e($filters['date_to']) ?>"></div>
  <div class="bl-actions"><button class="btn btn-brand" type="submit">Apply</button><a class="btn btn-light" href="<?= e(base_url('admin/ledger/banks')) ?>">Reset</a></div>
</form>

<?php if (!$summary): ?>
  <div class="bl-empty">No bank transactions yet.</div>
<?php else: ?>
<div class="bl-cards">
  <?php foreach ($summary as $r): ?>
    <article class="bl-card">
      <div class="bl-card-head">
        <div><h3><?= e((string) $r['bank_name']) ?></h3>
          <div class="sub"><?= !empty($r['account_title']) ? e((string) $r['account_title']) . (!empty($r['account_number']) ? ' · ····' . e(substr((string) $r['account_number'], -4)) : '') : 'Card and wallet payments' ?></div></div>
        <span class="bl-cur"><?= e((string) $r['currency']) ?></span>
      </div>
      <div class="bl-total"><?= e(money((int) $r['confirmed_minor'], (string) $r['currency'])) ?><small><?= (int) $r['confirmed_count'] ?> confirmed transaction<?= (int) $r['confirmed_count'] === 1 ? '' : 's' ?></small></div>
      <?php if ($r['modes']): ?><div class="bl-modes"><?php foreach ($r['modes'] as $label => $n): ?><span class="bl-mode"><?= e($label) ?><b><?= (int) $n ?></b></span><?php endforeach; ?></div><?php endif; ?>
      <div class="bl-stats">
        <div class="warn">Pending<b><?= (int) $r['pending_count'] ?></b></div>
        <div class="bad">Rejected<b><?= (int) $r['rejected_count'] ?></b></div>
        <div>Last confirmed<b style="font-size:.88rem"><?= $r['last_confirmed_at'] ? e(dt((string) $r['last_confirmed_at'], 'j M Y')) : '—' ?></b></div>
      </div>
      <a class="btn btn-brand bl-open" href="<?= e(base_url('admin/ledger/banks/' . (int) $r['bank_id'] . '?' . $qs(['currency' => (string) $r['currency']]))) ?>">Open ledger</a>
    </article>
  <?php endforeach; ?>
</div>
<?php endif; ?>
