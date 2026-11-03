<?php
/**
 * Site header: logo, two-level navigation, CTA buttons, mobile drawer.
 * Variables come from layouts/public.php.
 *
 * @var array  $headerNav
 * @var string $headerLogoUrl
 * @var string $fundraiseUrl
 * @var string $currentPath
 * @var array|null $topBanner
 */
$navService = $nav ?? null;
$isActiveItem = static function (array $item) use ($navService, $currentPath): bool {
    if ($navService !== null && $navService->isActive($item, $currentPath)) {
        return true;
    }
    foreach ($item['children'] ?? [] as $child) {
        if ($navService !== null && $navService->isActive($child, $currentPath)) {
            return true;
        }
    }
    return false;
};
?>
<?php if (!empty($topBanner)): ?>
<div class="top-banner" style="<?= !empty($topBanner['bg_color']) ? 'background:' . e((string) $topBanner['bg_color']) : '' ?>">
  <div class="container">
    <strong><?= e((string) $topBanner['title']) ?></strong>
    <?php if (!empty($topBanner['body'])): ?><span><?= e((string) $topBanner['body']) ?></span><?php endif; ?>
    <?php if (!empty($topBanner['link_url'])): ?>
      <a href="<?= e(preg_match('~^https?://~i', (string) $topBanner['link_url']) ? (string) $topBanner['link_url'] : base_url(ltrim((string) $topBanner['link_url'], '/'))) ?>"><?= e((string) ($topBanner['link_label'] ?: 'Learn more')) ?></a>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>
<header class="site-header" id="siteHeader">
  <div class="container site-header-inner">
    <a class="brand" href="<?= e(base_url()) ?>/" aria-label="<?= e($appName) ?> — Home">
      <img src="<?= e($headerLogoUrl) ?>" alt="<?= e($appName) ?>" width="160" height="57" decoding="async" fetchpriority="high">
    </a>

    <nav class="main-nav" id="mainNav" aria-label="Main navigation">
      <ul class="nav-list">
        <?php foreach ($headerNav as $i => $item): ?>
          <?php $hasChildren = !empty($item['children']); $active = $isActiveItem($item); ?>
          <li class="nav-item<?= $hasChildren ? ' has-children' : '' ?><?= $active ? ' is-active' : '' ?>">
            <?php if ($hasChildren): ?>
              <a class="nav-link" href="<?= e($item['href']) ?>"<?= $item['href'] === '#' ? ' role="button"' : '' ?> aria-haspopup="true" aria-expanded="false" aria-controls="submenu-<?= $i ?>"<?= $active ? ' aria-current="true"' : '' ?>>
                <?= e($item['label']) ?>
                <svg class="caret" viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
              </a>
              <button class="submenu-toggle" type="button" aria-label="Toggle <?= e($item['label']) ?> submenu" aria-expanded="false" aria-controls="submenu-<?= $i ?>">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
              </button>
              <ul class="submenu" id="submenu-<?= $i ?>">
                <?php foreach ($item['children'] as $child): ?>
                  <li><a href="<?= e($child['href']) ?>"<?= $child['newtab'] ? ' target="_blank" rel="noopener"' : '' ?><?= $navService !== null && $navService->isActive($child, $currentPath) ? ' aria-current="page" class="is-active"' : '' ?>><?= e($child['label']) ?></a></li>
                <?php endforeach; ?>
              </ul>
            <?php else: ?>
              <a class="nav-link" href="<?= e($item['href']) ?>"<?= $item['newtab'] ? ' target="_blank" rel="noopener"' : '' ?><?= $active ? ' aria-current="page"' : '' ?>><?= e($item['label']) ?></a>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
      <div class="nav-ctas nav-ctas-mobile">
        <a class="btn btn-outline" href="<?= e($fundraiseUrl) ?>" target="_blank" rel="noopener">Start Fundraising</a>
        <a class="btn btn-gold" href="<?= e(base_url('donate')) ?>">Donate Now</a>
      </div>
    </nav>

    <div class="header-actions">
      <a class="btn btn-outline hide-sm" href="<?= e($fundraiseUrl) ?>" target="_blank" rel="noopener">Start Fundraising</a>
      <a class="btn btn-gold" href="<?= e(base_url('donate')) ?>">Donate Now</a>
      <button class="nav-toggle" id="navToggle" type="button" aria-label="Open menu" aria-expanded="false" aria-controls="mainNav">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>
  <div class="nav-backdrop" id="navBackdrop" hidden></div>
</header>
