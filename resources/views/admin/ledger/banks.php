<?php
/** @var array $filters @var list<array> $summary */
$qs = static fn (array $extra = []): string => http_build_query(array_filter(array_merge(['date_from' => $filters['date_from'], 'date_to' => $filters['date_to']], $extra), static fn ($v): bool => $v !== '' && $v !== null));
?>
<div class="dash-head">
  <div><h1>Bank ledgers</h1><p class="sub">Every bank account and what it received. Only finance-verified (or gateway-confirmed) payments count as confirmed. Times are UTC.</p></div>
  <div class="sr-actions"><a class="btn btn-light" href="<?= e(base_url('admin/ledger')) ?>">All donations ledger</a></div>
</div>
<section class="panel">
  <form class="toolbar" method="get" action="<?= e(base_url('admin/ledger/banks')) ?>">
    <label class="muted">From <input class="input" type="date" name="date_from" value="<?= e($filters['date_from']) ?>"></label>
    <label class="muted">To <input class="input" type="date" name="date_to" value="<?= e($filters['date_to']) ?>"></label>
    <button class="btn btn-light" type="submit">Apply</button>
  </form>
  <div class="table-wrap"><table class="table">
    <thead><tr><th>Bank / account</th><th class="num">Confirmed transactions</th><th class="num">Confirmed total</th><th>Mode (confirmed)</th><th class="num">Pending</th><th class="num">Rejected</th><th>Last confirmed</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($summary as $r): ?>
      <tr>
        <td><strong><?= e((string) $r['bank_name']) ?></strong> <span class="muted"><?= e((string) $r['currency']) ?></span>
          <?php if (!empty($r['account_title'])): ?><br><small class="muted"><?= e((string) $r['account_title']) ?><?= !empty($r['account_number']) ? ' &middot; ...' . e(substr((string) $r['account_number'], -4)) : '' ?></small><?php endif; ?></td>
        <td class="num"><?= (int) $r['confirmed_count'] ?></td>
        <td class="num"><strong><?= e(money((int) $r['confirmed_minor'], (string) $r['currency'])) ?></strong></td>
        <td><?php foreach ($r['modes'] as $label => $n): ?><div><small><?= e($label) ?>: <strong><?= (int) $n ?></strong></small></div><?php endforeach; ?><?= $r['modes'] ? '' : '<span class="muted">—</span>' ?></td>
        <td class="num"><?= (int) $r['pending_count'] ?><?= (int) $r['pending_count'] > 0 ? '<br><small class="muted">' . e(money((int) $r['pending_minor'], (string) $r['currency'])) . '</small>' : '' ?></td>
        <td class="num"><?= (int) $r['rejected_count'] ?></td>
        <td><?= $r['last_confirmed_at'] ? e(dt((string) $r['last_confirmed_at'], 'j M Y H:i')) : '—' ?></td>
        <td><a class="btn btn-light" href="<?= e(base_url('admin/ledger/banks/' . (int) $r['bank_id'] . '?' . $qs(['currency' => (string) $r['currency']]))) ?>">Open ledger</a></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$summary): ?><tr><td colspan="8" class="muted">No bank transactions yet.</td></tr><?php endif; ?>
    </tbody></table></div>
</section>
