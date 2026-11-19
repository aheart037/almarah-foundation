<?php
/**
 * Admin overview — glass dashboard (redesign of the v3.1 dashboard).
 *
 * Same data contract as the V3 dashboard ($finance, $inbox, $content, $system, $campaignStats);
 * every value is read null-safely, so it also renders with an older DashboardController.
 * Blocks only appear for roles that may see them.
 *
 * @var array|null $finance  @var array|null $inbox  @var array|null $content  @var array|null $system
 */
require_once __DIR__ . '/../partials/dash-widgets.php';

$finance = $finance ?? null; $inbox = $inbox ?? null; $content = $content ?? null; $system = $system ?? null;
$campaignStats = $campaignStats ?? null;
$can = $can ?? static fn (string $p): bool => false;
$currency = (string) ($finance['currency'] ?? 'PKR');
$symbol = \App\Core\Money::symbol($currency);
$first = trim((string) ($authUser['first_name'] ?? ''));

$trendRows = is_array($finance['trend'] ?? null) ? $finance['trend'] : [];
$trendMinor = (int) array_sum(dv31_series($trendRows, 'minor'));
$trendCount = (int) array_sum(dv31_series($trendRows, 'count'));
$awaiting = dv31_int($finance['awaitingReview'] ?? 0);
$pendingPay = dv31_int($finance['pending'] ?? 0);
$failed = dv31_int($finance['failed'] ?? 0);
$openInbox = $inbox !== null ? (int) array_sum(array_column((array) ($inbox['boxes'] ?? []), 'open')) : 0;
$weekInbox = $inbox !== null ? (int) array_sum(array_column((array) ($inbox['boxes'] ?? []), 'week')) : 0;
$delta = dv31_delta($trendMinor, dv31_int($finance['trendPrevMinor'] ?? 0));

/* ---- KPI cards ---- */
$kpis = [];
$num = static fn (int $v): array => ['kind' => 'int', 'count' => $v, 'text' => number_format($v)];
$mon = static fn (int $minor) => ['kind' => 'money', 'count' => intdiv(abs($minor), 100), 'text' => money_short($minor, $currency)];
if ($finance !== null) {
    $kpis[] = ['tone' => 'rose',   'icon' => 'gift',      'inBalance' => true, 'label' => 'Verified · last 30 days', 'v' => $mon($trendMinor), 'sub' => number_format($trendCount) . ' gift' . ($trendCount === 1 ? '' : 's'), 'delta' => $delta, 'spark' => dv31_spark(dv31_series($trendRows, 'minor')), 'href' => 'admin/donations'];
    $kpis[] = ['tone' => 'gold',   'icon' => 'calendar',  'inBalance' => true, 'label' => 'Verified · this month', 'v' => $mon(dv31_int($finance['month']['minor'] ?? 0)), 'sub' => number_format(dv31_int($finance['month']['cnt'] ?? 0)) . ' donations', 'href' => 'admin/donations'];
    $kpis[] = ['tone' => 'green',  'icon' => 'shield',    'inBalance' => true, 'label' => 'Verified donations', 'v' => $mon(dv31_int($finance['verified']['minor'] ?? 0)), 'sub' => number_format(dv31_int($finance['verified']['cnt'] ?? 0)) . ' completed, staff-verified', 'href' => 'admin/donations'];
    $kpis[] = ['tone' => 'amber',  'icon' => 'hourglass', 'label' => 'Pending verification', 'v' => $num($awaiting), 'sub' => 'Gifts awaiting staff review', 'href' => 'admin/donations', 'alert' => $awaiting > 0];
    $kpis[] = ['tone' => 'violet', 'icon' => 'users',     'inBalance' => true, 'label' => 'Donors', 'v' => $num(dv31_int($finance['donors'] ?? 0)), 'sub' => 'Donor records', 'href' => 'admin/donors'];
    $kpis[] = ['tone' => 'slate',  'icon' => 'alert',     'label' => 'Awaiting payment', 'v' => $num($pendingPay), 'sub' => $failed . ' failed in 30 days', 'href' => 'admin/donations'];
}
if ($campaignStats !== null) {
    $kpis[] = ['tone' => 'teal', 'icon' => 'flag',  'label' => 'Active campaigns', 'v' => $num(dv31_int($campaignStats['active'] ?? 0)), 'sub' => 'Within scheduled dates', 'href' => 'admin/campaigns'];
    $kpis[] = ['tone' => 'rose', 'icon' => 'hands', 'label' => 'Donation purposes', 'v' => $num(dv31_int($campaignStats['purposes'] ?? 0)), 'sub' => 'Active on the donation form', 'href' => 'admin/reports/purposes'];
}
if ($inbox !== null) {
    if (isset($inbox['boxes']['volunteers'])) {
        $kpis[] = ['tone' => 'teal', 'icon' => 'hands', 'label' => 'Volunteer applications', 'v' => $num(dv31_int($inbox['boxes']['volunteers']['total'] ?? 0)), 'sub' => dv31_int($inbox['boxes']['volunteers']['open'] ?? 0) . ' to action', 'href' => 'admin/inbox/volunteers'];
    }
    if (isset($inbox['boxes']['contact'])) {
        $kpis[] = ['tone' => 'violet', 'icon' => 'mail', 'label' => 'Contact inquiries', 'v' => $num(dv31_int($inbox['boxes']['contact']['total'] ?? 0)), 'sub' => dv31_int($inbox['boxes']['contact']['open'] ?? 0) . ' to action', 'href' => 'admin/inbox/contact'];
    }
    $kpis[] = ['tone' => 'amber', 'icon' => 'inbox', 'label' => 'Inbox', 'v' => $num($openInbox), 'sub' => $weekInbox . ' new this week', 'href' => 'admin/inbox', 'alert' => $openInbox > 0];
}
if ($content !== null) {
    $kpis[] = ['tone' => 'slate', 'icon' => 'globe', 'label' => 'Website pages', 'v' => $num(dv31_int($content['pages'] ?? 0)), 'sub' => dv31_int($content['drafts'] ?? 0) . ' drafts · ' . dv31_int($content['slides'] ?? 0) . ' active slides', 'href' => 'admin/cms/pages'];
}

/* ---- Attention items ---- */
$attention = [];
if ($finance !== null && $awaiting > 0) { $attention[] = ['n' => $awaiting, 't' => 'pending verification', 'h' => 'admin/donations', 'tone' => 'amber']; }
if ($finance !== null && $failed > 0)   { $attention[] = ['n' => $failed, 't' => 'failed payments (30 days)', 'h' => 'admin/donations', 'tone' => 'red']; }
foreach ((array) ($inbox['boxes'] ?? []) as $key => $box) {
    if ($key === 'newsletter' || (int) ($box['open'] ?? 0) === 0) { continue; }
    $attention[] = ['n' => (int) $box['open'], 't' => strtolower((string) ($box['label'] ?? $key)), 'h' => 'admin/inbox/' . $key, 'tone' => 'violet'];
}
if ($system !== null && dv31_int($system['mailFailed'] ?? 0) > 0) { $attention[] = ['n' => (int) $system['mailFailed'], 't' => 'failed emails', 'h' => 'admin/email-queue', 'tone' => 'red']; }

/* ---- Chart data (embedded as inert JSON) ---- */
$mapRows = static function (array $rows): array {
    $o = [];
    foreach ($rows as $r) {
        if (!is_array($r)) { continue; }
        $o[] = ['l' => (string) ($r['label'] ?? $r['month'] ?? $r['ym'] ?? $r['day'] ?? ''), 'm' => dv31_int($r['minor'] ?? 0), 'c' => dv31_int($r['count'] ?? $r['cnt'] ?? 0)];
    }
    return $o;
};
$chart = ['symbol' => $symbol, 'currency' => $currency, 'daily' => $mapRows($trendRows), 'monthly' => $mapRows(is_array($finance['monthlyTrend'] ?? null) ? $finance['monthlyTrend'] : [])];
$json = static fn ($v): string => (string) json_encode($v, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);

/* ---- Donut: giving by purpose ---- */
$palette = ['#A92D63', '#F2C200', '#0F9D8A', '#6D5BD0', '#E4572E', '#3B82C4'];
$purposes = array_values(array_filter((array) ($finance['byPurpose'] ?? []), 'is_array'));
$purposeTotal = (int) array_sum(array_map(static fn (array $r): int => dv31_int($r['minor'] ?? 0), $purposes));

/* ---- Glass-dashboard helpers (presentation only) ---- */
$ggIcon = static function (string $name): string {
    if ($name === 'home') {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M3 9.5 12 3l9 6.5V20a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/></svg>';
    }
    return dv31_icon($name);
};
$ggState = static function (string $s): string {
    if (in_array($s, ['completed', 'published', 'approved', 'active', 'sent', 'verified', 'reactivated', 'accepted'], true)) { return 'teal'; }
    if (in_array($s, ['failed', 'rejected', 'cancelled', 'suspended', 'abandoned', 'expired', 'revoked'], true)) { return 'coral'; }
    if (in_array($s, ['processing', 'pending', 'pending_review', 'draft', 'queued', 'paused', 'changes_requested', 'sending'], true)) { return 'gold'; }
    return 'ink';
};
$recent = array_values(array_filter((array) ($finance['recent'] ?? []), 'is_array'));
$recentStatuses = array_values(array_unique(array_filter(array_map(static fn (array $d): string => (string) ($d['status'] ?? ''), $recent))));
sort($recentStatuses);
$metricCards = array_values(array_filter($kpis, static fn (array $k): bool => $finance === null || empty($k['inBalance'])));

?>
<div class="gg-wrap">
<div class="gg" id="gg" data-refresh-url="<?= e(base_url('admin')) ?>">

  <!-- Welcome -->
  <header class="gg-head" data-reveal>
    <div>
      <p class="gg-eyebrow"><span class="gg-live"><i></i> Live</span> <span data-clock>Almarah Foundation admin</span></p>
      <h1><span data-greeting>Welcome back</span><?= $first !== '' ? ', <em>' . e($first) . '</em>' : '' ?></h1>
      <p class="gg-sub">Here is what needs attention today across donations, the inbox and the website.</p>
    </div>
    <div class="gg-actions">
      <?php if ($finance !== null): ?><a class="gg-btn gg-btn-gold" href="<?= e(base_url('admin/donations')) ?>"><?= $ggIcon('gift') ?> Donations</a><?php endif; ?>
      <?php if ($content !== null): ?><a class="gg-btn gg-btn-ghost" href="<?= e(base_url('admin/cms/pages/create')) ?>"><?= $ggIcon('plus') ?> New page</a><?php endif; ?>
      <?php if ($finance !== null && $can('reports.view')): ?><a class="gg-btn gg-btn-ghost" href="<?= e(base_url('admin/reports')) ?>"><?= $ggIcon('pulse') ?> Reports</a><?php endif; ?>
      <button type="button" class="gg-btn gg-btn-ghost" data-refresh><?= $ggIcon('refresh') ?> <span>Refresh</span></button>
    </div>
  </header>

  <?php if ($system !== null && empty($system['mailConfigured'])): ?>
  <div class="gg-banner" role="alert" data-reveal>
    <?= $ggIcon('mail') ?>
    <div><b>Email is not configured.</b> Donation receipts and form notifications cannot be sent until an authorized administrator configures SMTP<?php if ($can('settings.manage')): ?> under <a href="<?= e(base_url('admin/settings')) ?>">Settings</a><?php endif; ?>.</div>
  </div>
  <?php endif; ?>

  <?php if ($attention !== []): ?>
  <section class="gg-attn" aria-label="Needs your attention" data-reveal data-attn>
    <h2><span class="gg-pulse" aria-hidden="true"></span> Needs your attention</h2>
    <div class="gg-attn-list">
      <?php foreach ($attention as $a): ?>
        <a class="gg-attn-item gg-t-<?= e($a['tone']) ?>" href="<?= e(base_url($a['h'])) ?>"><b><?= number_format($a['n']) ?></b> <span><?= e($a['t']) ?></span> <?= $ggIcon('arrow') ?></a>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <?php if ($finance !== null): ?>
  <!-- Revenue + chart -->
  <div class="gg-top-grid">
    <section class="gg-panel gg-balance" data-reveal>
      <div class="gg-panel-head"><div><h2>Verified donations</h2><p class="gg-muted">Staff-verified · last 30 days</p></div><span class="gg-unit"><?= e($currency) ?></span></div>
      <div class="gg-balance-number"><b class="gg-num" style="font-weight:inherit" data-gg-count="<?= intdiv(abs($trendMinor), 100) ?>" data-kind="money" data-symbol="<?= e($symbol) ?>"><?= e(money_short($trendMinor, $currency)) ?></b></div>
      <div class="gg-balance-change">
        <span class="gg-trend gg-<?= e($delta['dir']) ?>"><?= $delta['dir'] === 'up' ? '▲' : ($delta['dir'] === 'down' ? '▼' : '●') ?> <?= e($delta['text']) ?></span>
        <span><?= number_format($trendCount) ?> gift<?= $trendCount === 1 ? '' : 's' ?></span>
      </div>
      <?= dv31_spark(dv31_series($trendRows, 'minor')) ?>
      <div class="gg-balance-actions">
        <a class="gg-btn gg-btn-primary" href="<?= e(base_url('admin/donations')) ?>">View donations <?= $ggIcon('arrow') ?></a>
        <?php if ($can('reports.view')): ?><a class="gg-btn gg-btn-ghost" href="<?= e(base_url('admin/reports')) ?>">View reports <?= $ggIcon('arrow') ?></a>
        <?php else: ?><a class="gg-btn gg-btn-ghost" href="<?= e(base_url('admin/donors')) ?>">View donors <?= $ggIcon('arrow') ?></a><?php endif; ?>
      </div>
      <div class="gg-streams" aria-label="Verified totals">
        <a href="<?= e(base_url('admin/donations')) ?>"><span>This month</span><strong><?= e(money_short(dv31_int($finance['month']['minor'] ?? 0), $currency)) ?></strong></a>
        <a href="<?= e(base_url('admin/donations')) ?>"><span>All verified</span><strong><?= e(money_short(dv31_int($finance['verified']['minor'] ?? 0), $currency)) ?></strong></a>
        <a href="<?= e(base_url('admin/donors')) ?>"><span>Donors</span><strong><?= number_format(dv31_int($finance['donors'] ?? 0)) ?></strong></a>
      </div>
    </section>

    <section class="gg-panel" data-reveal data-chart>
      <div class="gg-panel-head">
        <div><h2>Donations over time</h2><p class="gg-muted" data-chart-sub>Daily totals, last 30 days</p></div>
        <div class="gg-controls">
          <div class="gg-seg" role="group" aria-label="Period">
            <button type="button" class="is-on" data-period="daily" aria-pressed="true">30 days</button>
            <button type="button" data-period="monthly" aria-pressed="false"<?= $chart['monthly'] === [] ? ' hidden' : '' ?>>12 months</button>
          </div>
          <div class="gg-seg" role="group" aria-label="Measure">
            <button type="button" class="is-on" data-measure="m" aria-pressed="true">Amount</button>
            <button type="button" data-measure="c" aria-pressed="false">Gifts</button>
          </div>
        </div>
      </div>
      <div class="gg-chart" data-chart-canvas role="img" aria-label="Verified donations chart"></div>
      <script type="application/json" data-chart-data><?= $json($chart) ?></script>
    </section>
  </div>
  <?php endif; ?>

  <!-- Key figures -->
  <?php if ($metricCards !== []): ?>
  <section class="gg-metrics" aria-label="Key figures">
    <?php foreach ($metricCards as $i => $k): ?>
    <a class="gg-metric gg-t-<?= e($k['tone']) ?><?= !empty($k['alert']) ? ' is-alert' : '' ?>" href="<?= e(base_url($k['href'])) ?>" data-reveal<?= $i === 0 ? ' data-feature="true"' : '' ?> style="--i:<?= (int) $i ?>">
      <span class="gg-metric-head"><span><?= e($k['label']) ?></span><span class="gg-metric-icon"><?= $ggIcon($k['icon']) ?></span></span>
      <b class="gg-metric-value gg-num" data-gg-count="<?= (int) $k['v']['count'] ?>" data-kind="<?= e($k['v']['kind']) ?>"<?= $k['v']['kind'] === 'money' ? ' data-symbol="' . e($symbol) . '"' : '' ?>><?= e($k['v']['text']) ?></b>
      <span class="gg-metric-caption"><?= e($k['sub']) ?></span>
    </a>
    <?php endforeach; ?>
  </section>
  <?php endif; ?>

  <?php if ($finance !== null): ?>
  <div class="gg-grid-2">
    <!-- By purpose -->
    <section class="gg-panel" data-reveal>
      <div class="gg-panel-head"><div><h2>By purpose</h2><p class="gg-muted">Where donors chose to give · 90 days</p></div></div>
      <?php if ($purposes === [] || $purposeTotal <= 0): ?>
        <div class="gg-empty"><?= $ggIcon('hands') ?><p>Purpose totals appear once donations are verified.</p></div>
      <?php else: ?>
        <div class="gg-donut-wrap">
          <svg class="gg-donut" viewBox="0 0 42 42" role="img" aria-label="Giving by purpose">
            <circle class="gg-donut-bg" cx="21" cy="21" r="15.9155"/>
            <?php $off = 0.0; foreach ($purposes as $i => $r): $pct = round(dv31_int($r['minor'] ?? 0) / $purposeTotal * 100, 2); ?>
              <circle class="gg-seg-arc" cx="21" cy="21" r="15.9155" style="--pct:<?= $pct ?>;--off:<?= round($off, 2) ?>;--c:<?= e($palette[$i % count($palette)]) ?>;--d:<?= (int) $i * 120 ?>ms"/>
            <?php $off += $pct; endforeach; ?>
          </svg>
          <div class="gg-donut-center"><span>Total</span><b><?= e(money_short($purposeTotal, $currency)) ?></b></div>
        </div>
        <ul class="gg-legend">
          <?php foreach ($purposes as $i => $r): $m = dv31_int($r['minor'] ?? 0); ?>
            <li><i style="background:<?= e($palette[$i % count($palette)]) ?>"></i><span><?= e((string) ($r['name'] ?? 'General Fund')) ?></span><b><?= e(money_short($m, $currency)) ?></b><em><?= $purposeTotal > 0 ? round($m / $purposeTotal * 100) : 0 ?>%</em></li>
          <?php endforeach; ?>
        </ul>
        <?php if ($content !== null): ?><p class="gg-link-row"><a href="<?= e(base_url('admin/cms/purposes')) ?>">Manage purposes <?= $ggIcon('arrow') ?></a></p><?php endif; ?>
      <?php endif; ?>
    </section>

    <!-- Recent donations -->
    <section class="gg-panel" data-reveal>
      <div class="gg-panel-head">
        <div><h2>Recent donations</h2><p class="gg-muted">Latest activity, newest first</p></div>
        <?php if ($recent !== []): ?>
        <div class="gg-table-tools">
          <label class="gg-search"><?= $ggIcon('seo') ?><input type="search" placeholder="Search recent donations" aria-label="Search recent donations" data-feed-search></label>
          <label class="gg-filter"><?= $ggIcon('pulse') ?><select aria-label="Filter by status" data-feed-status>
            <option value="">All statuses</option>
            <?php foreach ($recentStatuses as $st): ?><option value="<?= e($st) ?>"><?= e(ucwords(str_replace('_', ' ', $st))) ?></option><?php endforeach; ?>
          </select></label>
        </div>
        <?php endif; ?>
      </div>
      <?php if ($recent === []): ?>
        <div class="gg-empty"><?= $ggIcon('gift') ?><p>No donations yet. Gifts made through the Donate page will be listed here.</p></div>
      <?php else: ?>
        <div class="gg-table-scroll">
          <table>
            <thead><tr><th scope="col">Donor</th><th scope="col">Gateway</th><th scope="col" class="num">Amount</th><th scope="col">Status</th></tr></thead>
            <tbody data-feed>
              <?php foreach ($recent as $n => $d): $anon = !empty($d['anonymous']); $name = $anon ? 'Anonymous' : (string) ($d['donor_name'] ?? '—'); $st = (string) ($d['status'] ?? ''); ?>
              <tr data-feed-item data-status="<?= e($st) ?>">
                <td><span class="gg-donor"><span class="gg-avatar<?= $n % 3 === 1 ? ' v2' : ($n % 3 === 2 ? ' v3' : '') ?>"><?= e($anon ? '•' : dv31_initials($name)) ?></span>
                  <div><a href="<?= e(base_url('admin/donations/' . (int) ($d['id'] ?? 0))) ?>"><?= e($name) ?></a><small class="mono"><?= e((string) ($d['public_reference'] ?? '')) ?></small></div></span></td>
                <td><?= e(strtoupper((string) ($d['gateway_code'] ?? '—'))) ?></td>
                <td class="num"><b><?= e(money(dv31_int($d['amount_minor'] ?? 0), (string) ($d['currency'] ?? 'PKR'))) ?></b></td>
                <td><span class="gg-status" data-state="<?= e($ggState($st)) ?>"><i aria-hidden="true"></i><?= e(str_replace('_', ' ', $st)) ?></span></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <p class="gg-muted" data-feed-empty hidden role="status">No recent donations match your search.</p>
        <p class="gg-link-row"><a href="<?= e(base_url('admin/donations')) ?>">View all donations <?= $ggIcon('arrow') ?></a></p>
      <?php endif; ?>
      <?php if (!empty($finance['gatewayHealth'])): ?>
        <p class="gg-gateways"><span>Gateways</span>
          <?php foreach ((array) $finance['gatewayHealth'] as $code => $g): $ok = !empty($g['enabled']) && !empty($g['configured']); ?>
            <span class="gg-pill <?= $ok ? 'ok' : (!empty($g['enabled']) ? 'warn' : 'off') ?>"><?= e(strtoupper((string) $code)) ?></span>
          <?php endforeach; ?>
        </p>
      <?php endif; ?>
    </section>
  </div>
  <?php endif; ?>

  <!-- Inbox / website / system -->
  <div class="gg-grid-3">
    <?php if ($inbox !== null): ?>
    <section class="gg-panel" data-reveal>
      <div class="gg-panel-head"><div><h2>Inbox</h2><p class="gg-muted">Form submissions waiting for a reply or review</p></div></div>
      <ul class="gg-boxes">
        <?php foreach ((array) ($inbox['boxes'] ?? []) as $key => $b): ?>
          <li><a href="<?= e(base_url('admin/inbox/' . $key)) ?>"><span><?= e((string) ($b['label'] ?? $key)) ?></span>
            <span class="gg-box-n <?= (int) ($b['open'] ?? 0) > 0 ? 'hot' : '' ?>"><?= (int) ($b['open'] ?? 0) ?> to action</span>
            <small><?= (int) ($b['week'] ?? 0) ?> this week · <?= number_format((int) ($b['total'] ?? 0)) ?> all time</small></a></li>
        <?php endforeach; ?>
      </ul>
      <?php if (!empty($inbox['latestContact'])): ?>
        <h3 class="gg-h3">Latest messages</h3>
        <ul class="gg-feed">
          <?php foreach ($inbox['latestContact'] as $m): ?>
          <li><span class="gg-avatar"><?= e(dv31_initials((string) ($m['name'] ?? ''))) ?></span>
            <span class="gg-feed-main"><a href="<?= e(base_url('admin/inbox/contact/' . (int) ($m['id'] ?? 0))) ?>"><?= e((string) ($m['name'] ?? '')) ?></a>
              <small><?= e((string) (($m['subject'] ?? '') ?: '(no subject)')) ?></small></span>
            <span class="gg-feed-end"><small><?= e(dt((string) ($m['created_at'] ?? ''), 'j M H:i')) ?></small><span class="gg-pill"><?= e((string) ($m['status'] ?? '')) ?></span></span></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </section>
    <?php endif; ?>

    <?php if ($content !== null): ?>
    <section class="gg-panel" data-reveal>
      <div class="gg-panel-head"><div><h2>Website health</h2><p class="gg-muted">Dead links visitors hit, and what is live</p></div></div>
      <div class="gg-facts">
        <div><span>Active banner</span><b><?= !empty($content['banner']['title']) ? e((string) $content['banner']['title']) : '<i class="gg-none">None</i>' ?></b></div>
        <div><span>Redirects in force</span><b><?= (int) ($content['redirects'] ?? 0) ?></b></div>
      </div>
      <?php if (!empty($content['notFound'])): ?>
        <h3 class="gg-h3">Most-requested missing pages</h3>
        <ul class="gg-list">
          <?php foreach ($content['notFound'] as $nf): ?>
            <li><span class="mono"><?= e((string) ($nf['path'] ?? '')) ?></span><b><?= (int) ($nf['hits'] ?? 0) ?></b>
              <a class="gg-mini" href="<?= e(base_url('admin/cms/redirects/create?from_path=' . rawurlencode((string) ($nf['path'] ?? '')))) ?>">Redirect</a></li>
          <?php endforeach; ?>
        </ul>
        <p class="gg-link-row"><a href="<?= e(base_url('admin/cms/not-found')) ?>">Full 404 log <?= $ggIcon('arrow') ?></a></p>
      <?php else: ?>
        <p class="gg-muted" style="margin-top:12px">No missing-page requests logged.</p>
      <?php endif; ?>
      <p class="gg-quick">
        <a href="<?= e(base_url('admin/cms/slides')) ?>">Slider</a><a href="<?= e(base_url('admin/cms/banners')) ?>">Banners</a><a href="<?= e(base_url('admin/menus')) ?>">Menus</a><a href="<?= e(base_url('admin/cms/media')) ?>">Media</a>
      </p>
    </section>
    <?php endif; ?>

    <?php if ($system !== null): ?>
    <section class="gg-panel" data-reveal>
      <div class="gg-panel-head"><div><h2>System</h2><p class="gg-muted">Mail queue, backups and recent staff activity</p></div></div>
      <div class="gg-facts">
        <div><span>Email queue</span><b><?= (int) ($system['mailQueued'] ?? 0) ?> waiting<?= (int) ($system['mailFailed'] ?? 0) > 0 ? ' · <i class="gg-bad">' . (int) $system['mailFailed'] . ' failed</i>' : '' ?></b></div>
        <div><span>Last backup</span><b><?= !empty($system['lastBackup']['created_at']) ? e(dt((string) $system['lastBackup']['created_at'], 'j M Y H:i')) : '<i class="gg-none">Never</i>' ?></b></div>
      </div>
      <?php if (!empty($system['recentAudit'])): ?>
        <ul class="gg-timeline">
          <?php foreach ($system['recentAudit'] as $a): ?>
            <li><time><?= e(dt((string) ($a['created_at'] ?? ''), 'j M H:i')) ?></time>
              <span><b><?= e(trim((string) ($a['first_name'] ?? '') . ' ' . (string) ($a['last_name'] ?? '')) ?: 'System') ?></b> · <?= e(str_replace(['.', '_'], [' › ', ' '], (string) ($a['action'] ?? ''))) ?></span></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
      <p class="gg-link-row"><a href="<?= e(base_url('admin/audit')) ?>">Audit log <?= $ggIcon('arrow') ?></a> <a href="<?= e(base_url('admin/email-queue')) ?>">Email queue <?= $ggIcon('arrow') ?></a></p>
    </section>
    <?php endif; ?>
  </div>

  <p class="gg-stamp" aria-live="polite"><span class="gg-live"><i></i></span> <span data-updated>Updated just now</span></p>
</div>

</div>
