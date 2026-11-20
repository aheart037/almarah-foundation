<?php
/**
 * Floating bottom dock for the glass admin. Only lists destinations the signed-in role may open and
 * highlights the one that matches the current path.
 *
 * @var callable $can
 * @var string   $path  current request path, e.g. /admin/donations
 */
$svg = static function (string $d): string {
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $d . '</svg>';
};
$icons = [
    'home'  => '<path d="M3 9.5 12 3l9 6.5V20a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/>',
    'gift'  => '<rect x="3" y="8" width="18" height="4" rx="1"/><path d="M12 8v13M19 12v8a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1v-8"/><path d="M7.5 8a2.5 2.5 0 0 1 0-5C11 3 12 8 12 8s1-5 4.5-5a2.5 2.5 0 0 1 0 5"/>',
    'users' => '<path d="M17 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="10" cy="7" r="4"/><path d="M21 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8"/>',
    'inbox' => '<path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.5 5.1 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.5-6.9A2 2 0 0 0 16.7 4H7.3a2 2 0 0 0-1.8 1.1z"/>',
    'pulse' => '<path d="M22 12h-4l-3 9L9 3l-3 9H2"/>',
    'globe' => '<circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 0 1 0 20M12 2a15 15 0 0 0 0 20"/>',
];
$items = [['/admin', 'admin', 'Overview', 'home']];
if ($can('donations.view')) { $items[] = ['/admin/donations', 'admin/donations', 'Donations', 'gift']; }
if ($can('donors.view') || $can('donations.view')) { $items[] = ['/admin/donors', 'admin/donors', 'Donors', 'users']; }
if ($can('forms.view') || $can('volunteers.manage')) { $items[] = ['/admin/inbox', 'admin/inbox/' . ($can('forms.view') ? 'contact' : 'volunteers'), 'Inbox', 'inbox']; }
if ($can('reports.view')) { $items[] = ['/admin/reports', 'admin/reports', 'Reports', 'pulse']; }
if ($can('content.manage')) { $items[] = ['/admin/cms', 'admin/cms/slides', 'Website', 'globe']; }
?>
<nav class="gg-dock" aria-label="Quick destinations">
  <span class="gg-dock-indicator" aria-hidden="true"></span>
  <?php foreach ($items as [$prefix, $href, $label, $icon]):
      $prefixes = $prefix === '/admin/donations' ? ['/admin/donations', '/admin/inkind'] : [$prefix];
      $current = $prefix === '/admin' ? $path === '/admin' : array_reduce($prefixes, static fn (bool $c, string $x): bool => $c || $path === $x || str_starts_with($path, $x . '/'), false);
  ?>
    <a href="<?= e(base_url($href)) ?>" aria-label="<?= e($label) ?>"<?= $current ? ' aria-current="page"' : '' ?>>
      <?= $svg($icons[$icon]) ?><span class="gg-dock-text" aria-hidden="true"><?= e($label) ?></span><span class="gg-dock-short" aria-hidden="true"><?= e($label) ?></span>
    </a>
  <?php endforeach; ?>
</nav>
