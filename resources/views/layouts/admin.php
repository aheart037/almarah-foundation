<?php
/**
 * Unified admin layout for almarah.org.
 *
 * Navigation is filtered by permission ($can closure shared by Controller),
 * but every route is also guarded server-side by PermissionMiddleware — the
 * menu is a convenience, not the security boundary.
 *
 * @var string   $content
 * @var array|null $authUser
 * @var string   $pageTitle
 * @var callable $can
 */

use App\Core\Config;
use App\Http\Controllers\Admin\InboxController;

$appName = (string) Config::get('app.name', 'Almarah Foundation');
$title = isset($pageTitle) && $pageTitle !== '' ? $pageTitle . ' | Admin' : 'Admin';
// Request::path() (shared by index.php) strips the installation folder and
// normalises trailing slashes; raw REQUEST_URI does neither on subfolder hosts.
$path = (string) ($currentPath ?? \App\Core\BasePath::strip('/' . trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', '/')));
$is = static function (string $prefix) use ($path): string {
    return $path === $prefix || ($prefix !== '/admin' && str_starts_with($path, $prefix . '/')) ? 'active' : '';
};
$can = isset($can) && is_callable($can) ? $can : static fn (string $p): bool => false;
$adminRole = in_array('admin', $currentUserRoles ?? [], true) || in_array('super_admin', $currentUserRoles ?? [], true);
$fullName = trim((string) ($authUser['first_name'] ?? '') . ' ' . (string) ($authUser['last_name'] ?? ''));

$inboxOpen = 0;
try {
    $inboxOpen = array_sum(InboxController::openCounts(\App\Core\App::instance()->make(\App\Core\Database::class), \App\Core\App::instance()->make(\App\Services\AuthService::class)));
} catch (\Throwable) {
    $inboxOpen = 0;
}

$icon = static function (string $name): string {
    $paths = [
        'home'     => '<path d="M3 9.5L12 3l9 6.5V20a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/>',
        'inbox'    => '<path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.5 5.1 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.5-6.9A2 2 0 0 0 16.7 4H7.3a2 2 0 0 0-1.8 1.1z"/>',
        'pages'    => '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>',
        'slides'   => '<rect x="2" y="4" width="20" height="14" rx="2"/><path d="M8 21h8M12 18v3"/>',
        'banner'   => '<path d="M4 4h16v6H4z"/><path d="M4 14h16M4 18h10"/>',
        'menu'     => '<path d="M4 6h16M4 12h16M4 18h10"/>',
        'media'    => '<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/>',
        'team'     => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8"/>',
        'cert'     => '<circle cx="12" cy="8" r="6"/><path d="M15.5 13 17 22l-5-3-5 3 1.5-9"/>',
        'report'   => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M8 13h8M8 17h8"/>',
        'seo'      => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
        'heart'    => '<path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1L12 21.2l7.7-7.8 1.1-1a5.5 5.5 0 0 0 0-7.8z"/>',
        'bank'     => '<path d="M3 10h18L12 3zM5 10v8M9 10v8M15 10v8M19 10v8M3 21h18"/>',
        'purpose'  => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="4"/><circle cx="12" cy="12" r="1"/>',
        'callback' => '<path d="M23 4v6h-6M1 20v-6h6"/><path d="M3.5 9a9 9 0 0 1 14.9-3.4L23 10M1 14l4.6 4.4A9 9 0 0 0 20.5 15"/>',
        'users'    => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        'mail'     => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="M2 7l10 6 10-6"/>',
        'settings' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.6 1.6 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.6 1.6 0 0 0-2.7 1.1V21a2 2 0 1 1-4 0v-.1A1.6 1.6 0 0 0 7.5 19l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1A1.6 1.6 0 0 0 3.6 13H3a2 2 0 1 1 0-4h.1A1.6 1.6 0 0 0 4.7 6.5l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1A1.6 1.6 0 0 0 10 3.6V3a2 2 0 1 1 4 0v.1a1.6 1.6 0 0 0 2.5 1.1l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1A1.6 1.6 0 0 0 20.4 10H21a2 2 0 1 1 0 4h-.1a1.6 1.6 0 0 0-1.5 1z"/>',
        'audit'    => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/>',
        'jobs'     => '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2M2 13h20"/>',
        'back'     => '<path d="M19 12H5M11 18l-6-6 6-6"/>',
        'chart'    => '<path d="M3 3v18h18"/><path d="M7 14l4-4 4 3 5-6"/>',
        'backup'   => '<ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v14c0 1.7 3.6 3 8 3s8-1.3 8-3V5"/><path d="M4 12c0 1.7 3.6 3 8 3s8-1.3 8-3"/>',
    ];
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? '') . '</svg>';
};
$link = static function (string $href, string $label, string $iconName, string $active, ?int $badge = null) use ($icon): string {
    return '<a href="' . e(base_url($href)) . '" class="' . $active . '"' . ($active !== '' ? ' aria-current="page"' : '') . '>' . $icon($iconName) . ' ' . e($label)
        . ($badge ? '<span class="nav-badge">' . (int) $badge . '</span>' : '') . '</a>';
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> | <?= e($appName) ?></title>
<meta name="robots" content="noindex,nofollow">
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">
<link rel="stylesheet" href="<?= e(asset('assets/css/style.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('assets/css/admin.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('assets/css/reports.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('assets/css/admin-v31.css')) ?>">
<?php require __DIR__ . '/../partials/favicon.php'; ?>
</head>
<body class="admin-body">

<header class="site-header admin-topbar" id="site-header">
  <div class="wrap header-inner" style="display:flex;align-items:center;gap:16px;min-height:64px">
    <a class="brand-lockup" href="<?= e(base_url($can('dashboard.view') ? 'admin' : '/')) ?>" aria-label="<?= $can('dashboard.view') ? 'Admin home' : 'Site home' ?>" style="display:flex;align-items:center;gap:10px;text-decoration:none">
      <img src="<?= e(asset('assets/images/logo/header-logo-01.png')) ?>" alt="" width="120" height="43" style="height:40px;width:auto">
      <span style="font-weight:700;color:#6b0f35;letter-spacing:.04em;font-size:13px;text-transform:uppercase">Admin</span>
    </a>
    <div style="margin-left:auto;display:flex;align-items:center;gap:10px">
      <a class="btn btn-light btn-sm" href="<?= e(base_url('/')) ?>" target="_blank" rel="noopener">View site</a>
      <form class="inline-form" method="post" action="<?= e(base_url('logout')) ?>">
        <?= csrf_field() ?>
        <button class="btn btn-sm" type="submit">Sign out</button>
      </form>
      <button class="btn btn-light btn-sm admin-side-toggle" type="button" aria-expanded="false" aria-controls="adminSide" data-side-toggle>Menu</button>
    </div>
  </div>
</header>

<div class="wrap">
<?php require __DIR__ . '/../partials/alerts.php'; ?>

  <div class="dash-shell">
    <button class="admin-side-backdrop" type="button" aria-label="Close navigation" data-side-close tabindex="-1" hidden></button>
    <aside class="dash-side" id="adminSide">
      <div class="dash-side-head" style="background:linear-gradient(135deg,#2a1119 0%,#6b0f35 100%)">
        <span class="avatar"><?= e(initials($fullName !== '' ? $fullName : 'Admin')) ?></span>
        <b><?= e($fullName !== '' ? $fullName : 'Administrator') ?></b>
        <span><?= e(implode(', ', array_map(static fn (string $r): string => ucwords(str_replace('_', ' ', $r)), $currentUserRoles ?? []))) ?></span>
      </div>
      <nav class="dash-nav" aria-label="Admin">
        <?php if ($can('dashboard.view')): ?>
          <div class="nav-group">Workspace</div>
          <?= $link('admin', 'Overview', 'home', $is('/admin')) ?>
        <?php endif; ?>
        <?php if ($can('forms.view') || $can('volunteers.manage')): ?>
          <?= $link('admin/inbox/' . ($can('forms.view') ? 'contact' : 'volunteers'), 'Inbox', 'inbox', $is('/admin/inbox'), $inboxOpen ?: null) ?>
        <?php endif; ?>

        <?php if ($can('content.manage')): ?>
          <div class="nav-group">Website</div>
          <?php if ($adminRole): ?><?= $link('admin/campaigns', 'Campaigns', 'purpose', $is('/admin/campaigns')) ?><?php endif; ?>
          <?= $link('admin/cms/pages', 'Pages', 'pages', $is('/admin/cms/pages')) ?>
          <?= $link('admin/cms/slides', 'Homepage slider', 'slides', $is('/admin/cms/slides')) ?>
          <?= $link('admin/cms/banners', 'Banners', 'banner', $is('/admin/cms/banners')) ?>
          <?= $link('admin/menus', 'Menus', 'menu', $is('/admin/menus')) ?>
          <?= $link('admin/cms/media', 'Photos & videos', 'media', $is('/admin/cms/media')) ?>
          <?= $link('admin/cms/team', 'Board & team', 'team', $is('/admin/cms/team')) ?>
          <?= $link('admin/cms/partners', 'MOUs & certificates', 'cert', $is('/admin/cms/partners')) ?>
          <?= $link('admin/cms/reports', 'Financial reports', 'report', $is('/admin/cms/reports')) ?>
          <?= $link('admin/cms/purposes', 'Donation purposes', 'purpose', $is('/admin/cms/purposes')) ?>
          <?= $link('admin/cms/seo', 'SEO', 'seo', $is('/admin/cms/seo') ?: $is('/admin/cms/redirects') ?: $is('/admin/cms/not-found')) ?>
          <?= $link('admin/content', 'Legal text & FAQs', 'pages', $is('/admin/content')) ?>
        <?php endif; ?>

        <?php if ($can('volunteers.manage')): ?>
          <div class="nav-group">People</div>
          <?= $link('admin/cms/jobs', 'Job openings', 'jobs', $is('/admin/cms/jobs')) ?>
        <?php endif; ?>

        <?php if ($can('donations.view') || $can('donors.view') || $can('finance.manage') || $can('logs.view')): ?>
          <div class="nav-group">Finance</div>
          <?php if ($can('donations.view')): ?>
            <?= $link('admin/donations', 'Donations', 'heart', $is('/admin/donations')) ?>
            <?php endif; ?>
          <?php if ($can('donors.view') || $can('donations.view')): ?><?= $link('admin/donors', 'Donors', 'users', $is('/admin/donors')) ?>
          <?php endif; ?>
          <?php if ($can('finance.manage')): ?>
            <?= $link('admin/finance', 'Finance desk', 'chart', $is('/admin/finance')) ?>
            <?= $link('admin/cms/banks', 'Bank accounts', 'bank', $is('/admin/cms/banks')) ?>
          <?php endif; ?>
          <?php if ($can('logs.view')): ?>
            <?= $link('admin/payment-callbacks', 'Payment callbacks', 'callback', $is('/admin/payment-callbacks')) ?>
          <?php endif; ?>
        <?php endif; ?>

        <?php if ($can('reports.view')): ?>
          <div class="nav-group">Insights</div>
          <?php $reportsOpen = $is('/admin/reports') !== ''; ?>
          <details class="nav-details" <?= $reportsOpen ? 'open' : '' ?>>
            <summary class="<?= $reportsOpen ? 'active' : '' ?>" <?= $reportsOpen ? 'aria-current="location"' : '' ?>><?= $icon('report') ?> Reports</summary>
            <?php foreach (\App\Services\ReportService::catalog() as $reportSlug => $reportItem):
                $reportHref = $reportSlug === 'overview' ? 'admin/reports' : 'admin/reports/' . $reportSlug;
                $reportPath = '/admin/reports/' . $reportSlug;
                $reportOn = $reportSlug === 'overview'
                    ? ($path === '/admin/reports' || $is($reportPath) !== '')
                    : $is($reportPath) !== ''; ?>
              <a href="<?= e(base_url($reportHref)) ?>" class="nav-sub <?= $reportOn ? 'active' : '' ?>" <?= $reportOn ? 'aria-current="page"' : '' ?>><?= e($reportItem['title']) ?></a>
            <?php endforeach; ?>
          </details>
        <?php endif; ?>

        <?php if ($can('grants.view')): ?><?= $link('admin/grants', 'Grants', 'report', $is('/admin/grants')) ?><?php endif; ?>

        <?php if ($can('users.view') || $can('users.manage') || $can('roles.view') || $can('roles.manage') || $can('settings.manage') || $can('logs.view') || $can('backups.manage')): ?>
          <div class="nav-group">System</div>
          <?php if ($can('backups.manage')): ?><?= $link('admin/backups', 'Backups', 'backup', $is('/admin/backups')) ?><?php endif; ?>
          <?php if ($can('roles.view') || $can('roles.manage')): ?><?= $link('admin/roles', 'Roles', 'users', $is('/admin/roles')) ?><?php endif; ?>
          <?php if ($can('users.view') || $can('users.manage')): ?><?= $link('admin/users', 'Users', 'users', $is('/admin/users')) ?><?php endif; ?>
          <?php if ($can('settings.manage')): ?>
            <?= $link('admin/email-templates', 'Emails', 'mail', $is('/admin/email-templates')) ?>
            <?= $link('admin/settings', 'Settings', 'settings', $is('/admin/settings')) ?>
          <?php elseif ($can('logs.view')): ?>
            <?= $link('admin/email-queue', 'Email queue', 'mail', $is('/admin/email-queue')) ?>
          <?php endif; ?>
          <?php if ($can('logs.view')): ?><?= $link('admin/audit', 'Audit log', 'audit', $is('/admin/audit')) ?><?php endif; ?>
        <?php endif; ?>
        <hr>
        <a href="<?= e(base_url('/')) ?>"><?= $icon('back') ?> Back to the site</a>
      </nav>
    </aside>

    <div class="dash-main">
<?= $content ?>
    </div>
  </div>
</div>

<footer class="wrap muted" style="padding:24px 0;font-size:12.5px">&copy; <?= date('Y') ?> <?= e($appName) ?> · Admin panel</footer>

<script src="<?= e(asset('assets/js/app.js')) ?>" defer></script>
<script src="<?= e(asset('assets/js/admin.js')) ?>" defer></script>
</body>
</html>
