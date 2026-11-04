<?php
/**
 * Unified admin overview. Blocks appear only for roles that may see them.
 *
 * @var array|null $finance  @var array|null $inbox  @var array|null $content  @var array|null $system
 */
require_once __DIR__ . '/../partials/charts.php';
$finance = $finance ?? null; $inbox = $inbox ?? null; $content = $content ?? null; $system = $system ?? null;
$campaignStats = $campaignStats ?? null;
$currency = (string) ($finance['currency'] ?? 'PKR');
$trendMinor = (int) array_sum(array_column($finance['trend'] ?? [], 'minor'));
$trendCount = (int) array_sum(array_column($finance['trend'] ?? [], 'count'));
?>
<div class="dash-head">
  <div>
    <h1>Admin overview</h1>
    <p class="sub">What needs attention today across donations, the inbox and the website.</p>
  </div>
  <div class="sr-actions">
    <?php if ($content !== null): ?><a class="btn btn-light" href="<?= e(base_url('admin/cms/pages/create')) ?>">New page</a><?php endif; ?>
    <?php if ($finance !== null): ?><a class="btn btn-brand" href="<?= e(base_url('admin/donations')) ?>">Donations</a><?php endif; ?>
  </div>
</div>

<?php if ($system !== null && empty($system['mailConfigured'])): ?>
  <div class="alert alert-warning" style="margin-bottom:18px">
    <div><b>Email is not configured.</b> Donation receipts and form notifications cannot be sent until an authorized administrator configures SMTP<?php if ($can('settings.manage')): ?> under <a href="<?= e(base_url('admin/settings')) ?>">Settings</a><?php endif; ?>.</div>
  </div>
<?php endif; ?>

<?php if ($finance !== null || $inbox !== null): ?>
<section class="panel admin-tasks" aria-labelledby="tasksHeading">
  <div class="panel-head"><div><h2 id="tasksHeading">Needs your attention</h2><p class="panel-sub">Live counts from the records your role can access.</p></div></div>
  <div class="admin-task-list">
    <?php if ($finance !== null): ?>
      <a href="<?= e(base_url('admin/donations')) ?>"><strong><?= (int) $finance['awaitingReview'] ?> pending verification</strong><span>Review submitted gifts →</span></a>
    <?php endif; ?>
    <?php foreach ($inbox['boxes'] ?? [] as $key => $box): ?>
      <?php if ($key === 'newsletter' || (int) $box['open'] === 0) continue; ?>
      <a href="<?= e(base_url('admin/inbox/' . $key)) ?>"><strong><?= (int) $box['open'] ?> <?= e(strtolower((string) $box['label'])) ?></strong><span>Open queue →</span></a>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<div class="stat-cards">
  <?php if ($finance !== null): ?>
    <div class="stat-card brand">
      <div class="k">Verified · last 30 days</div>
      <div class="v"><?= e(money_short($trendMinor, $currency)) ?></div>
      <div class="d"><?= e(number_format($trendCount)) ?> verified gift<?= $trendCount === 1 ? '' : 's' ?> <?= ch_delta($trendMinor, (int) ($finance['trendPrevMinor'] ?? 0)) ?></div>
    </div>
    <div class="stat-card">
      <div class="k">Verified · this month</div>
      <div class="v"><?= e(money_short((int) ($finance['month']['minor'] ?? 0), $currency)) ?></div>
      <div class="d"><?= e(number_format((int) ($finance['month']['cnt'] ?? 0))) ?> donations</div>
    </div>
    <div class="stat-card">
      <div class="k">Verified donations</div>
      <div class="v"><?= e(money_short((int) ($finance['verified']['minor'] ?? 0), $currency)) ?></div>
      <div class="d"><?= number_format((int) ($finance['verified']['cnt'] ?? 0)) ?> completed, staff-verified · <?= e($currency) ?></div>
    </div>
    <div class="stat-card">
      <div class="k">Pending verification</div>
      <div class="v"><?= number_format((int) $finance['awaitingReview']) ?></div>
      <div class="d">Submitted gifts awaiting staff review</div>
    </div>
    <div class="stat-card">
      <div class="k">Donors</div>
      <div class="v"><?= number_format((int) $finance['donors']) ?></div>
      <div class="d">Donor records</div>
    </div>
    <div class="stat-card">
      <div class="k">Awaiting payment</div>
      <div class="v"><?= e((string) ($finance['pending'] ?? 0)) ?></div>
      <div class="d">pending or processing · <?= (int) ($finance['failed'] ?? 0) ?> failed in 30 days</div>
    </div>
  <?php endif; ?>
  <?php if ($campaignStats !== null): ?>
    <div class="stat-card"><div class="k">Active campaigns</div><div class="v"><?= number_format((int) $campaignStats['active']) ?></div><div class="d">Active within scheduled dates</div></div>
    <div class="stat-card"><div class="k">Fundraisers</div><div class="v"><?= number_format((int) $campaignStats['fundraisers']) ?></div><div class="d">Published and approved</div></div>
  <?php endif; ?>
  <?php if ($inbox !== null): ?>
    <?php if (isset($inbox['boxes']['volunteers'])): ?>
      <div class="stat-card"><div class="k">Volunteer applications</div><div class="v"><?= number_format((int) $inbox['boxes']['volunteers']['total']) ?></div><div class="d"><?= (int) $inbox['boxes']['volunteers']['open'] ?> new · <a href="<?= e(base_url('admin/inbox/volunteers')) ?>">Review</a></div></div>
    <?php endif; ?>
    <?php if (isset($inbox['boxes']['contact'])): ?>
      <div class="stat-card"><div class="k">Contact inquiries</div><div class="v"><?= number_format((int) $inbox['boxes']['contact']['total']) ?></div><div class="d"><?= (int) $inbox['boxes']['contact']['open'] ?> new · <a href="<?= e(base_url('admin/inbox/contact')) ?>">Open inbox</a></div></div>
    <?php endif; ?>
    <?php $open = array_sum(array_column($inbox['boxes'], 'open')); $week = array_sum(array_column($inbox['boxes'], 'week')); ?>
    <div class="stat-card">
      <div class="k">Inbox</div>
      <div class="v"><?= (int) $open ?> <small>to action</small></div>
      <div class="d"><?= (int) $week ?> new this week</div>
    </div>
  <?php endif; ?>
  <?php if ($content !== null): ?>
    <div class="stat-card">
      <div class="k">Website</div>
      <div class="v"><?= (int) $content['pages'] ?> <small>pages</small></div>
      <div class="d"><?= (int) $content['drafts'] ?> draft<?= (int) $content['drafts'] === 1 ? '' : 's' ?> · <?= (int) $content['slides'] ?> active slides · <?= (int) $content['media'] ?> media items</div>
    </div>
  <?php endif; ?>
</div>

<?php if ($finance !== null): ?>
<div class="grid-chart">
  <section class="panel">
    <div class="panel-head">
      <div><h2>Verified donations — last 30 days</h2><p class="panel-sub">Daily totals; hover a point for the day's figures.</p></div>
    </div>
    <?= ch_area($finance['trend'] ?? [], 'admin', $currency, 'Verified donations per day, last 30 days') ?>
  </section>
  <section class="panel">
    <h2>By purpose (90 days)</h2>
    <p class="panel-sub">Where donors chose to give.</p>
    <?php
      $rows = []; $max = 0;
      foreach ($finance['byPurpose'] ?? [] as $r) { $max = max($max, (int) $r['minor']); }
      foreach ($finance['byPurpose'] ?? [] as $r) { $rows[] = ['label' => (string) $r['name'], 'right' => money_short((int) $r['minor'], $currency), 'pct' => $max > 0 ? (int) $r['minor'] / $max * 100 : 0, 'sub' => (int) $r['cnt'] . ' gifts']; }
    ?>
    <?= ch_hbars($rows, 'Purpose totals appear once donations are verified.') ?>
    <?php if ($content !== null): ?><p class="mt-2"><a class="link-arrow" href="<?= e(base_url('admin/cms/purposes')) ?>">Manage purposes</a></p><?php endif; ?>
  </section>
</div>
<?php endif; ?>

<?php if ($finance !== null): ?>
<section class="panel">
  <div class="panel-head"><div><h2>Monthly verified donations</h2><p class="panel-sub">Last 12 calendar months · <?= e($currency) ?> · completed, staff-verified gifts only. Hover for counts and amounts.</p></div></div>
  <?= ch_area($finance['monthlyTrend'], 'admin-monthly', $currency, 'Verified donations per month, last 12 months') ?>
</section>
<?php endif; ?>

<div class="grid-2">
  <?php if ($inbox !== null): ?>
  <section class="panel">
    <div class="panel-head"><div><h2>Inbox</h2><p class="panel-sub">Form submissions waiting for a reply or review.</p></div></div>
    <div class="table-wrap"><table class="data-table">
      <thead><tr><th>Box</th><th class="num">To action</th><th class="num">This week</th><th class="num">All time</th></tr></thead>
      <tbody>
      <?php foreach ($inbox['boxes'] as $key => $b): ?>
        <tr><td><a href="<?= e(base_url('admin/inbox/' . $key)) ?>"><?= e($b['label']) ?></a></td>
            <td class="num"><?= $b['open'] > 0 ? '<b>' . (int) $b['open'] . '</b>' : '0' ?></td>
            <td class="num"><?= (int) $b['week'] ?></td><td class="num"><?= number_format((int) $b['total']) ?></td></tr>
      <?php endforeach; ?>
      </tbody></table></div>
    <?php if (!empty($inbox['latestContact'])): ?>
      <h3 style="margin-top:18px;font-size:15px">Latest messages</h3>
      <ul class="donations-list">
        <?php foreach ($inbox['latestContact'] as $m): ?>
          <li><a href="<?= e(base_url('admin/inbox/contact/' . (int) $m['id'])) ?>"><b><?= e((string) $m['name']) ?></b> — <?= e((string) ($m['subject'] ?: '(no subject)')) ?></a>
              <span class="d-when"><?= e(dt((string) $m['created_at'], 'j M H:i')) ?> · <?= e((string) $m['status']) ?></span></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </section>
  <?php endif; ?>

  <?php if ($finance !== null): ?>
  <section class="panel">
    <div class="panel-head"><div><h2>Recent donations</h2></div><a class="link-arrow" href="<?= e(base_url('admin/donations')) ?>">View all</a></div>
    <?php if (!empty($finance['recent'])): ?><label class="admin-search-label" for="recentSearch">Find a recent transaction</label><input class="input admin-search" id="recentSearch" type="search" placeholder="Search reference, donor, gateway or status" data-table-search="recentTransactions" aria-controls="recentTransactions"><?php endif; ?>
    <?php if (empty($finance['recent'])): ?>
      <div class="empty-state"><h3>No donations yet</h3><p>Gifts made through the Donate page will be listed here.</p></div>
    <?php else: ?>
      <div class="table-wrap"><table class="data-table" id="recentTransactions">
        <thead><tr><th>Reference</th><th>Donor</th><th>Gateway</th><th>Status</th><th class="num">Amount</th></tr></thead>
        <tbody>
        <?php foreach ($finance['recent'] as $d): ?>
          <tr><td class="mono"><a href="<?= e(base_url('admin/donations/' . (int) $d['id'])) ?>"><?= e((string) $d['public_reference']) ?></a></td>
              <td><?= !empty($d['anonymous']) ? '<span class="muted">Anonymous</span>' : e((string) ($d['donor_name'] ?? '—')) ?></td>
              <td><?= e(strtoupper((string) ($d['gateway_code'] ?? '—'))) ?></td>
              <td><span class="<?= e(status_badge_class((string) $d['status'])) ?>"><?= e((string) $d['status']) ?></span></td>
              <td class="num"><?= e(money((int) $d['amount_minor'], (string) ($d['currency'] ?? 'PKR'))) ?></td></tr>
        <?php endforeach; ?>
        </tbody></table></div>
      <p class="admin-search-empty" data-search-empty="recentTransactions" role="status" hidden>No recent transactions match your search.</p>
    <?php endif; ?>
    <?php if (!empty($finance['gatewayHealth'])): ?>
      <p class="muted" style="font-size:13px;margin-top:12px">Gateways:
        <?php foreach ($finance['gatewayHealth'] as $code => $g): ?>
          <span class="badge <?= !empty($g['enabled']) && !empty($g['configured']) ? 'badge-green' : (!empty($g['enabled']) ? 'badge-amber' : 'badge-grey') ?>"><?= e(strtoupper((string) $code)) ?></span>
        <?php endforeach; ?>
      </p>
    <?php endif; ?>
  </section>
  <?php endif; ?>

  <?php if ($content !== null): ?>
  <section class="panel">
    <div class="panel-head"><div><h2>Website health</h2><p class="panel-sub">Dead links visitors hit, and what is live.</p></div></div>
    <div class="detail-grid" style="margin-bottom:14px">
      <div><div class="k">Active banner</div><div class="v"><?= !empty($content['banner']['title']) ? e((string) $content['banner']['title']) : '<span class="muted">None</span>' ?></div></div>
      <div><div class="k">Redirects in force</div><div class="v"><?= (int) $content['redirects'] ?></div></div>
    </div>
    <?php if (!empty($content['notFound'])): ?>
      <h3 style="font-size:15px">Most-requested missing pages</h3>
      <div class="table-wrap"><table class="data-table">
        <tbody>
        <?php foreach ($content['notFound'] as $nf): ?>
          <tr><td class="mono wrap"><?= e((string) $nf['path']) ?></td><td class="num"><?= (int) $nf['hits'] ?></td>
              <td style="text-align:right"><a class="btn btn-light btn-sm" href="<?= e(base_url('admin/cms/redirects/create?from_path=' . rawurlencode((string) $nf['path']))) ?>">Redirect</a></td></tr>
        <?php endforeach; ?>
        </tbody></table></div>
      <p class="mt-2"><a class="link-arrow" href="<?= e(base_url('admin/cms/not-found')) ?>">Full 404 log</a></p>
    <?php else: ?>
      <p class="muted">No missing-page requests logged. </p>
    <?php endif; ?>
    <p class="mt-2">
      <a class="btn btn-light btn-sm" href="<?= e(base_url('admin/cms/slides')) ?>">Slider</a>
      <a class="btn btn-light btn-sm" href="<?= e(base_url('admin/cms/banners')) ?>">Banners</a>
      <a class="btn btn-light btn-sm" href="<?= e(base_url('admin/menus')) ?>">Menus</a>
      <a class="btn btn-light btn-sm" href="<?= e(base_url('admin/cms/media')) ?>">Media</a>
    </p>
  </section>
  <?php endif; ?>

  <?php if ($system !== null): ?>
  <section class="panel">
    <div class="panel-head"><div><h2>System</h2><p class="panel-sub">Mail queue, payment callbacks and recent staff activity.</p></div></div>
    <div class="detail-grid" style="margin-bottom:14px">
      <div><div class="k">Email queue</div><div class="v"><?= (int) $system['mailQueued'] ?> waiting<?= (int) $system['mailFailed'] > 0 ? ' · <b style="color:#a12d2d">' . (int) $system['mailFailed'] . ' failed</b>' : '' ?></div></div>
      <div><div class="k">Last backup</div><div class="v"><?= !empty($system['lastBackup']['created_at']) ? e(dt((string) $system['lastBackup']['created_at'], 'j M Y H:i')) : '<span class="muted">Never</span>' ?></div></div>
    </div>
    <?php if (!empty($system['recentAudit'])): ?>
      <ul class="timeline">
        <?php foreach ($system['recentAudit'] as $a): ?>
          <li><span class="when"><?= e(dt((string) $a['created_at'], 'j M H:i')) ?></span>
              <span class="what"><b><?= e(trim((string) ($a['first_name'] ?? '') . ' ' . (string) ($a['last_name'] ?? '')) ?: 'System') ?></b> · <?= e(str_replace(['.', '_'], [' › ', ' '], (string) $a['action'])) ?><?= !empty($a['entity_id']) ? ' <span class="muted">#' . e((string) $a['entity_id']) . '</span>' : '' ?></span></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
    <p class="mt-2"><a class="link-arrow" href="<?= e(base_url('admin/audit')) ?>">Audit log</a> &nbsp; <a class="link-arrow" href="<?= e(base_url('admin/email-queue')) ?>">Email queue</a> &nbsp; <a class="link-arrow" href="<?= e(base_url('admin/payment-callbacks')) ?>">Callbacks</a></p>
  </section>
  <?php endif; ?>
</div>
