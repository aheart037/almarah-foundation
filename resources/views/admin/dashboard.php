<?php
/**
 * Admin overview — v3.1 animated dashboard.
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
    $kpis[] = ['tone' => 'rose',   'icon' => 'gift',      'label' => 'Verified · last 30 days', 'v' => $mon($trendMinor), 'sub' => number_format($trendCount) . ' gift' . ($trendCount === 1 ? '' : 's'), 'delta' => $delta, 'spark' => dv31_spark(dv31_series($trendRows, 'minor')), 'href' => 'admin/donations'];
    $kpis[] = ['tone' => 'gold',   'icon' => 'calendar',  'label' => 'Verified · this month', 'v' => $mon(dv31_int($finance['month']['minor'] ?? 0)), 'sub' => number_format(dv31_int($finance['month']['cnt'] ?? 0)) . ' donations', 'href' => 'admin/donations'];
    $kpis[] = ['tone' => 'green',  'icon' => 'shield',    'label' => 'Verified donations', 'v' => $mon(dv31_int($finance['verified']['minor'] ?? 0)), 'sub' => number_format(dv31_int($finance['verified']['cnt'] ?? 0)) . ' completed, staff-verified', 'href' => 'admin/donations'];
    $kpis[] = ['tone' => 'amber',  'icon' => 'hourglass', 'label' => 'Pending verification', 'v' => $num($awaiting), 'sub' => 'Gifts awaiting staff review', 'href' => 'admin/ledger?status=pending_verification', 'alert' => $awaiting > 0];
    $kpis[] = ['tone' => 'violet', 'icon' => 'users',     'label' => 'Donors', 'v' => $num(dv31_int($finance['donors'] ?? 0)), 'sub' => 'Donor records', 'href' => 'admin/donors'];
    $kpis[] = ['tone' => 'slate',  'icon' => 'alert',     'label' => 'Awaiting payment', 'v' => $num($pendingPay), 'sub' => $failed . ' failed in 30 days', 'href' => 'admin/donations'];
}
if ($campaignStats !== null) {
    $kpis[] = ['tone' => 'teal', 'icon' => 'flag',  'label' => 'Active campaigns', 'v' => $num(dv31_int($campaignStats['active'] ?? 0)), 'sub' => 'Within scheduled dates', 'href' => 'admin/campaigns'];
    $kpis[] = ['tone' => 'rose', 'icon' => 'hands', 'label' => 'Fundraisers', 'v' => $num(dv31_int($campaignStats['fundraisers'] ?? 0)), 'sub' => 'Published and approved', 'href' => 'admin/fundraisers'];
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
if ($finance !== null && $awaiting > 0) { $attention[] = ['n' => $awaiting, 't' => 'pending verification', 'h' => 'admin/ledger?status=pending_verification', 'tone' => 'amber']; }
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
?>
<link rel="stylesheet" href="<?= e(asset('assets/css/dashboard-v31.css')) ?>">

<div class="dv" id="dv" data-refresh-url="<?= e(base_url('admin')) ?>">

  <!-- Welcome banner -->
  <section class="dv-hero" data-reveal>
    <span class="dv-blob dv-blob-a" aria-hidden="true"></span><span class="dv-blob dv-blob-b" aria-hidden="true"></span><span class="dv-blob dv-blob-c" aria-hidden="true"></span>
    <div class="dv-hero-main">
      <p class="dv-eyebrow"><span class="dv-live"><i></i> Live</span> <span data-clock>Almarah Foundation admin</span></p>
      <h1><span data-greeting>Welcome back</span><?= $first !== '' ? ', <em>' . e($first) . '</em>' : '' ?></h1>
      <p class="dv-sub">Here is what needs attention today across donations, the inbox and the website.</p>
      <div class="dv-actions">
        <?php if ($finance !== null): ?><a class="dv-btn dv-btn-gold" href="<?= e(base_url('admin/donations')) ?>"><?= dv31_icon('gift') ?> Donations</a><?php endif; ?>
        <?php if ($content !== null): ?><a class="dv-btn dv-btn-ghost" href="<?= e(base_url('admin/cms/pages/create')) ?>"><?= dv31_icon('plus') ?> New page</a><?php endif; ?>
        <?php if ($finance !== null && $can('reports.view')): ?><a class="dv-btn dv-btn-ghost" href="<?= e(base_url('admin/reports')) ?>"><?= dv31_icon('pulse') ?> Reports</a><?php endif; ?>
        <button type="button" class="dv-btn dv-btn-ghost" data-refresh><?= dv31_icon('refresh') ?> <span>Refresh</span></button>
      </div>
    </div>
    <div class="dv-hero-stats">
      <?php if ($finance !== null): ?>
      <div class="dv-chip"><span class="k">Verified · 30 days</span><b class="dv-num" data-count="<?= intdiv(abs($trendMinor), 100) ?>" data-kind="money" data-symbol="<?= e($symbol) ?>"><?= e(money_short($trendMinor, $currency)) ?></b>
        <span class="dv-trend dv-<?= e($delta['dir']) ?>"><?= $delta['dir'] === 'up' ? '▲' : ($delta['dir'] === 'down' ? '▼' : '●') ?> <?= e($delta['text']) ?></span></div>
      <div class="dv-chip"><span class="k">Pending verification</span><b class="dv-num" data-count="<?= $awaiting ?>" data-kind="int"><?= number_format($awaiting) ?></b></div>
      <?php endif; ?>
      <?php if ($inbox !== null): ?><div class="dv-chip"><span class="k">Inbox to action</span><b class="dv-num" data-count="<?= $openInbox ?>" data-kind="int"><?= number_format($openInbox) ?></b></div><?php endif; ?>
    </div>
  </section>

  <?php if ($system !== null && empty($system['mailConfigured'])): ?>
  <div class="dv-banner" role="alert" data-reveal>
    <?= dv31_icon('mail') ?>
    <div><b>Email is not configured.</b> Donation receipts and form notifications cannot be sent until an authorized administrator configures SMTP<?php if ($can('settings.manage')): ?> under <a href="<?= e(base_url('admin/settings')) ?>">Settings</a><?php endif; ?>.</div>
  </div>
  <?php endif; ?>

  <?php if ($attention !== []): ?>
  <section class="dv-attn" aria-label="Needs your attention" data-reveal>
    <h2><span class="dv-pulse" aria-hidden="true"></span> Needs your attention</h2>
    <div class="dv-attn-list">
      <?php foreach ($attention as $a): ?>
        <a class="dv-attn-item dv-t-<?= e($a['tone']) ?>" href="<?= e(base_url($a['h'])) ?>"><b><?= number_format($a['n']) ?></b> <?= e($a['t']) ?> <?= dv31_icon('arrow') ?></a>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- KPI cards -->
  <?php if ($kpis !== []): ?>
  <section class="dv-kpis" aria-label="Key figures">
    <?php foreach ($kpis as $i => $k): ?>
    <a class="dv-kpi dv-t-<?= e($k['tone']) ?><?= !empty($k['alert']) ? ' is-alert' : '' ?>" href="<?= e(base_url($k['href'])) ?>" data-reveal style="--i:<?= (int) $i ?>">
      <span class="dv-kpi-icon"><?= dv31_icon($k['icon']) ?></span>
      <span class="dv-kpi-label"><?= e($k['label']) ?></span>
      <b class="dv-kpi-value dv-num" data-count="<?= (int) $k['v']['count'] ?>" data-kind="<?= e($k['v']['kind']) ?>"<?= $k['v']['kind'] === 'money' ? ' data-symbol="' . e($symbol) . '"' : '' ?>><?= e($k['v']['text']) ?></b>
      <span class="dv-kpi-sub"><?= e($k['sub']) ?></span>
      <?php if (!empty($k['delta'])): ?><span class="dv-trend dv-<?= e($k['delta']['dir']) ?>"><?= $k['delta']['dir'] === 'up' ? '▲' : ($k['delta']['dir'] === 'down' ? '▼' : '●') ?> <?= e($k['delta']['text']) ?></span><?php endif; ?>
      <?= $k['spark'] ?? '' ?>
    </a>
    <?php endforeach; ?>
  </section>
  <?php endif; ?>

  <!-- Charts -->
  <?php if ($finance !== null): ?>
  <div class="dv-grid-main">
    <section class="dv-card" data-reveal data-chart>
      <div class="dv-card-head">
        <div><h2>Verified donations</h2><p class="dv-muted" data-chart-sub>Daily totals, last 30 days</p></div>
        <div class="dv-controls">
          <div class="dv-seg" role="group" aria-label="Period">
            <button type="button" class="is-on" data-period="daily" aria-pressed="true">30 days</button>
            <button type="button" data-period="monthly" aria-pressed="false"<?= $chart['monthly'] === [] ? ' hidden' : '' ?>>12 months</button>
          </div>
          <div class="dv-seg" role="group" aria-label="Measure">
            <button type="button" class="is-on" data-measure="m" aria-pressed="true">Amount</button>
            <button type="button" data-measure="c" aria-pressed="false">Gifts</button>
          </div>
        </div>
      </div>
      <div class="dv-chart" data-chart-canvas role="img" aria-label="Verified donations chart"></div>
      <script type="application/json" data-chart-data><?= $json($chart) ?></script>
    </section>

    <section class="dv-card" data-reveal>
      <div class="dv-card-head"><div><h2>By purpose</h2><p class="dv-muted">Where donors chose to give · 90 days</p></div></div>
      <?php if ($purposes === [] || $purposeTotal <= 0): ?>
        <div class="dv-empty"><?= dv31_icon('hands') ?><p>Purpose totals appear once donations are verified.</p></div>
      <?php else: ?>
        <div class="dv-donut-wrap">
          <svg class="dv-donut" viewBox="0 0 42 42" role="img" aria-label="Giving by purpose">
            <circle class="dv-donut-bg" cx="21" cy="21" r="15.9155"/>
            <?php $off = 0.0; foreach ($purposes as $i => $r): $pct = round(dv31_int($r['minor'] ?? 0) / $purposeTotal * 100, 2); ?>
              <circle class="dv-seg-arc" cx="21" cy="21" r="15.9155" style="--pct:<?= $pct ?>;--off:<?= round($off, 2) ?>;--c:<?= e($palette[$i % count($palette)]) ?>;--d:<?= (int) $i * 120 ?>ms"/>
            <?php $off += $pct; endforeach; ?>
          </svg>
          <div class="dv-donut-center"><span>Total</span><b><?= e(money_short($purposeTotal, $currency)) ?></b></div>
        </div>
        <ul class="dv-legend">
          <?php foreach ($purposes as $i => $r): $m = dv31_int($r['minor'] ?? 0); ?>
            <li><i style="background:<?= e($palette[$i % count($palette)]) ?>"></i><span><?= e((string) ($r['name'] ?? 'General Fund')) ?></span><b><?= e(money_short($m, $currency)) ?></b><em><?= $purposeTotal > 0 ? round($m / $purposeTotal * 100) : 0 ?>%</em></li>
          <?php endforeach; ?>
        </ul>
        <?php if ($content !== null): ?><p class="dv-link-row"><a href="<?= e(base_url('admin/cms/purposes')) ?>">Manage purposes <?= dv31_icon('arrow') ?></a></p><?php endif; ?>
      <?php endif; ?>
    </section>
  </div>
  <?php endif; ?>

  <!-- Activity -->
  <div class="dv-grid-2">
    <?php if ($finance !== null): ?>
    <section class="dv-card" data-reveal>
      <div class="dv-card-head"><div><h2>Recent donations</h2><p class="dv-muted">Latest activity, newest first</p></div><a class="dv-more" href="<?= e(base_url('admin/donations')) ?>">View all <?= dv31_icon('arrow') ?></a></div>
      <?php $recent = array_values(array_filter((array) ($finance['recent'] ?? []), 'is_array')); ?>
      <?php if ($recent === []): ?>
        <div class="dv-empty"><?= dv31_icon('gift') ?><p>No donations yet. Gifts made through the Donate page will be listed here.</p></div>
      <?php else: ?>
        <input class="dv-search" type="search" placeholder="Search recent donations…" aria-label="Search recent donations" data-feed-search>
        <ul class="dv-feed" data-feed>
          <?php foreach ($recent as $d): $anon = !empty($d['anonymous']); $name = $anon ? 'Anonymous' : (string) ($d['donor_name'] ?? '—'); ?>
          <li data-feed-item>
            <span class="dv-avatar"><?= e($anon ? '•' : dv31_initials($name)) ?></span>
            <span class="dv-feed-main"><a href="<?= e(base_url('admin/donations/' . (int) ($d['id'] ?? 0))) ?>"><?= e($name) ?></a>
              <small><span class="mono"><?= e((string) ($d['public_reference'] ?? '')) ?></span> · <?= e(strtoupper((string) ($d['gateway_code'] ?? '—'))) ?></small></span>
            <span class="dv-feed-end"><b><?= e(money(dv31_int($d['amount_minor'] ?? 0), (string) ($d['currency'] ?? 'PKR'))) ?></b>
              <span class="<?= e(status_badge_class((string) ($d['status'] ?? ''))) ?>"><?= e((string) ($d['status'] ?? '')) ?></span></span>
          </li>
          <?php endforeach; ?>
        </ul>
        <p class="dv-muted" data-feed-empty hidden>No recent donations match your search.</p>
      <?php endif; ?>
      <?php if (!empty($finance['gatewayHealth'])): ?>
        <p class="dv-gateways"><span>Gateways</span>
          <?php foreach ((array) $finance['gatewayHealth'] as $code => $g): $ok = !empty($g['enabled']) && !empty($g['configured']); ?>
            <span class="dv-pill <?= $ok ? 'ok' : (!empty($g['enabled']) ? 'warn' : 'off') ?>"><?= e(strtoupper((string) $code)) ?></span>
          <?php endforeach; ?>
        </p>
      <?php endif; ?>
    </section>
    <?php endif; ?>

    <?php if ($inbox !== null): ?>
    <section class="dv-card" data-reveal>
      <div class="dv-card-head"><div><h2>Inbox</h2><p class="dv-muted">Form submissions waiting for a reply or review</p></div></div>
      <ul class="dv-boxes">
        <?php foreach ((array) ($inbox['boxes'] ?? []) as $key => $b): ?>
          <li><a href="<?= e(base_url('admin/inbox/' . $key)) ?>"><span><?= e((string) ($b['label'] ?? $key)) ?></span>
            <span class="dv-box-n <?= (int) ($b['open'] ?? 0) > 0 ? 'hot' : '' ?>"><?= (int) ($b['open'] ?? 0) ?> to action</span>
            <small><?= (int) ($b['week'] ?? 0) ?> this week · <?= number_format((int) ($b['total'] ?? 0)) ?> all time</small></a></li>
        <?php endforeach; ?>
      </ul>
      <?php if (!empty($inbox['latestContact'])): ?>
        <h3 class="dv-h3">Latest messages</h3>
        <ul class="dv-feed dv-feed-tight">
          <?php foreach ($inbox['latestContact'] as $m): ?>
          <li><span class="dv-avatar"><?= e(dv31_initials((string) ($m['name'] ?? ''))) ?></span>
            <span class="dv-feed-main"><a href="<?= e(base_url('admin/inbox/contact/' . (int) ($m['id'] ?? 0))) ?>"><?= e((string) ($m['name'] ?? '')) ?></a>
              <small><?= e((string) (($m['subject'] ?? '') ?: '(no subject)')) ?></small></span>
            <span class="dv-feed-end"><small><?= e(dt((string) ($m['created_at'] ?? ''), 'j M H:i')) ?></small><span class="dv-pill off"><?= e((string) ($m['status'] ?? '')) ?></span></span></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </section>
    <?php endif; ?>

    <?php if ($content !== null): ?>
    <section class="dv-card" data-reveal>
      <div class="dv-card-head"><div><h2>Website health</h2><p class="dv-muted">Dead links visitors hit, and what is live</p></div></div>
      <div class="dv-facts">
        <div><span>Active banner</span><b><?= !empty($content['banner']['title']) ? e((string) $content['banner']['title']) : '<i class="dv-none">None</i>' ?></b></div>
        <div><span>Redirects in force</span><b><?= (int) ($content['redirects'] ?? 0) ?></b></div>
      </div>
      <?php if (!empty($content['notFound'])): ?>
        <h3 class="dv-h3">Most-requested missing pages</h3>
        <ul class="dv-list">
          <?php foreach ($content['notFound'] as $nf): ?>
            <li><span class="mono"><?= e((string) ($nf['path'] ?? '')) ?></span><b><?= (int) ($nf['hits'] ?? 0) ?></b>
              <a class="dv-mini" href="<?= e(base_url('admin/cms/redirects/create?from_path=' . rawurlencode((string) ($nf['path'] ?? '')))) ?>">Redirect</a></li>
          <?php endforeach; ?>
        </ul>
        <p class="dv-link-row"><a href="<?= e(base_url('admin/cms/not-found')) ?>">Full 404 log <?= dv31_icon('arrow') ?></a></p>
      <?php else: ?>
        <p class="dv-muted">No missing-page requests logged.</p>
      <?php endif; ?>
      <p class="dv-quick">
        <a href="<?= e(base_url('admin/cms/slides')) ?>">Slider</a><a href="<?= e(base_url('admin/cms/banners')) ?>">Banners</a><a href="<?= e(base_url('admin/menus')) ?>">Menus</a><a href="<?= e(base_url('admin/cms/media')) ?>">Media</a>
      </p>
    </section>
    <?php endif; ?>

    <?php if ($system !== null): ?>
    <section class="dv-card" data-reveal>
      <div class="dv-card-head"><div><h2>System</h2><p class="dv-muted">Mail queue, backups and recent staff activity</p></div></div>
      <div class="dv-facts">
        <div><span>Email queue</span><b><?= (int) ($system['mailQueued'] ?? 0) ?> waiting<?= (int) ($system['mailFailed'] ?? 0) > 0 ? ' · <i class="dv-bad">' . (int) $system['mailFailed'] . ' failed</i>' : '' ?></b></div>
        <div><span>Last backup</span><b><?= !empty($system['lastBackup']['created_at']) ? e(dt((string) $system['lastBackup']['created_at'], 'j M Y H:i')) : '<i class="dv-none">Never</i>' ?></b></div>
      </div>
      <?php if (!empty($system['recentAudit'])): ?>
        <ul class="dv-timeline">
          <?php foreach ($system['recentAudit'] as $a): ?>
            <li><time><?= e(dt((string) ($a['created_at'] ?? ''), 'j M H:i')) ?></time>
              <span><b><?= e(trim((string) ($a['first_name'] ?? '') . ' ' . (string) ($a['last_name'] ?? '')) ?: 'System') ?></b> · <?= e(str_replace(['.', '_'], [' › ', ' '], (string) ($a['action'] ?? ''))) ?></span></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
      <p class="dv-link-row"><a href="<?= e(base_url('admin/audit')) ?>">Audit log <?= dv31_icon('arrow') ?></a> <a href="<?= e(base_url('admin/email-queue')) ?>">Email queue <?= dv31_icon('arrow') ?></a></p>
    </section>
    <?php endif; ?>
  </div>

  <p class="dv-stamp" aria-live="polite"><span class="dv-live"><i></i></span> <span data-updated>Updated just now</span></p>
</div>
<script src="<?= e(asset('assets/js/dashboard-v31.js')) ?>" defer></script>
