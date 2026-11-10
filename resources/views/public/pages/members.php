<?php
/**
 * Board Members page — fully database-driven (V3.6).
 *
 * Content source: Admin → Board & Team.
 *   - every card (name, role, photo, bio) = team_profiles
 *   - banner and category headings        = team_sections
 *
 * @var array<string,list<array<string,mixed>>> $teamGroups   published profiles keyed by category
 * @var array<string,array<string,mixed>>       $teamSections admin-editable headings keyed by section
 */

$teamGroups = is_array($teamGroups ?? null) ? $teamGroups : [];
$teamSections = is_array($teamSections ?? null) ? $teamSections : [];

// Built-in fallbacks: used only for a heading the admin has not defined.
$defaults = [
    'hero'       => ['tag' => 'Our People', 'title' => 'Our Distinguished', 'title_accent' => 'Leadership', 'subtitle' => 'Board of Directors · Honorary Members · Ambassadors', 'sort_order' => 0],
    'board'      => ['tag' => 'Leadership & Governance', 'title' => 'Board of', 'title_accent' => 'Directors', 'subtitle' => "Visionary leaders steering Almarah Foundation's mission forward", 'sort_order' => 10],
    'honorary'   => ['tag' => 'Distinguished Patrons', 'title' => 'Honorary', 'title_accent' => 'Members', 'subtitle' => "Distinguished leaders shaping Pakistan's future", 'sort_order' => 20],
    'advisor'    => ['tag' => 'Guidance & Counsel', 'title' => 'Advisory', 'title_accent' => 'Board', 'subtitle' => 'Experienced advisors guiding our work', 'sort_order' => 30],
    'management' => ['tag' => 'Day-to-Day Leadership', 'title' => 'Management', 'title_accent' => 'Team', 'subtitle' => 'The team that runs Almarah Foundation every day', 'sort_order' => 40],
    'ambassador' => ['tag' => 'Faces of Almarah', 'title' => 'Brand & Goodwill', 'title_accent' => 'Ambassadors', 'subtitle' => 'Celebrated personalities championing our cause', 'sort_order' => 50],
];

$section = static function (string $key) use ($teamSections, $defaults): array {
    $row = $teamSections[$key] ?? null;
    return is_array($row) ? $row + $defaults[$key] : $defaults[$key];
};

// Sections to render, in admin order. Hidden sections are skipped; so are empty ones.
$order = [];
foreach (['board', 'honorary', 'advisor', 'management', 'ambassador'] as $key) {
    $sec = $section($key);
    if (($sec['status'] ?? 'published') === 'hidden' || empty($teamGroups[$key])) {
        continue;
    }
    $order[$key] = (int) $sec['sort_order'];
}
asort($order);

$photo = static function (array $p): string {
    $path = trim((string) ($p['photo_path'] ?? ''));
    return $path !== '' ? asset($path) : '';
};
$bio = static fn (array $p): string => nl2br(e(trim((string) ($p['bio'] ?? ''))));
$linkedin = static function (array $p): string {
    $url = trim((string) ($p['linkedin_url'] ?? ''));
    if ($url === '' || !preg_match('~^https?://~i', $url)) {
        return '';
    }
    return '<a href="' . e($url) . '" target="_blank" rel="noopener noreferrer" style="display:inline-block;margin-top:8px;font-size:.78rem;font-weight:600">LinkedIn</a>';
};
$placeholder = static fn (string $name): string =>
    '<div aria-hidden="true" style="display:flex;align-items:center;justify-content:center;width:100%;height:100%;min-height:280px;background:linear-gradient(135deg,#2a1119 0%,#6b0f35 100%);color:#fff;font-size:3rem;font-weight:700;letter-spacing:.05em">' . e(initials($name)) . '</div>';

$hero = $section('hero');
?>
<section class="page-hero">
    <div class="hero-inner">
        <?php if (trim((string) $hero['tag']) !== ''): ?><span class="hx-eyebrow"><?= e((string) $hero['tag']) ?></span><?php endif; ?>
        <h1><?= e((string) $hero['title']) ?><?php if (trim((string) $hero['title_accent']) !== ''): ?><em><?= e((string) $hero['title_accent']) ?></em><?php endif; ?></h1>
        <?php if (trim((string) $hero['subtitle']) !== ''): ?><p class="hero-sub"><?= e((string) $hero['subtitle']) ?></p><?php endif; ?>
        <div class="hero-ornament">
            <div class="bar"></div>
            <div class="diamond"></div>
            <div class="bar r"></div>
        </div>
    </div>
</section>

<?php if ($order === []): ?>
<section>
    <div class="section-wrap">
        <div class="section-header">
            <h2>Our <span class="accent">People</span></h2>
            <p class="section-sub">Our leadership team will be listed here soon.</p>
        </div>
    </div>
</section>
<?php endif; ?>

<?php foreach (array_keys($order) as $key):
    $sec = $section($key);
    $members = $teamGroups[$key];
    $isHonorary = $key === 'honorary';
    $isAmbassador = $key === 'ambassador';
    $sectionClass = $isHonorary ? ' class="honorary-section"' : ($isAmbassador ? ' class="amb-section"' : '');
    ?>
<section<?= $sectionClass ?>>
    <div class="section-wrap">
        <div class="section-header">
            <?php if (trim((string) $sec['tag']) !== ''): ?><div class="s-tag"><?= e((string) $sec['tag']) ?></div><?php endif; ?>
            <h2><?= e((string) $sec['title']) ?><?php if (trim((string) $sec['title_accent']) !== ''): ?> <span class="accent"<?= $isHonorary ? ' style="color:var(--yellow)"' : '' ?>><?= e((string) $sec['title_accent']) ?></span><?php endif; ?></h2>
            <div class="divider"><div class="gem"></div></div>
            <?php if (trim((string) $sec['subtitle']) !== ''): ?><p class="section-sub"><?= e((string) $sec['subtitle']) ?></p><?php endif; ?>
        </div>

        <?php if ($isHonorary): ?>
        <div class="honorary-grid">
            <?php foreach ($members as $p): $src = $photo($p); ?>
            <div class="hon-card">
                <div class="hon-photo-wrap">
                    <?php if ($src !== ''): ?>
                        <img loading="lazy" decoding="async" src="<?= e($src) ?>" alt="<?= e((string) $p['name']) ?>">
                    <?php else: ?>
                        <?= $placeholder((string) $p['name']) ?>
                    <?php endif; ?>
                    <div class="hon-photo-overlay"></div>
                </div>
                <div class="hon-body">
                    <div class="hon-name"><?= e((string) $p['name']) ?></div>
                    <?php if (trim((string) ($p['designation'] ?? '')) !== ''): ?>
                        <div style="font-size:.7rem;letter-spacing:1px;text-transform:uppercase;color:var(--yellow);margin:2px 0 6px"><?= e((string) $p['designation']) ?></div>
                    <?php endif; ?>
                    <div class="hon-divider"></div>
                    <p class="hon-bio"><?= $bio($p) ?></p>
                    <?= $linkedin($p) ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php elseif ($isAmbassador): ?>
        <div class="amb-grid">
            <?php foreach ($members as $p):
                $src = $photo($p);
                $label = trim((string) ($p['designation'] ?? ''));
                $isBrand = $label !== '' && stripos($label, 'brand') !== false;
                if ($label === '') {
                    $label = 'Goodwill Ambassador';
                }
                ?>
            <div class="amb-card<?= $isBrand ? ' featured' : '' ?>">
                <div class="amb-photo">
                    <?php if ($src !== ''): ?>
                        <img loading="lazy" decoding="async" src="<?= e($src) ?>" alt="<?= e((string) $p['name']) ?>">
                    <?php else: ?>
                        <?= $placeholder((string) $p['name']) ?>
                    <?php endif; ?>
                    <div class="amb-photo-overlay"></div>
                    <span class="amb-type-pill <?= $isBrand ? 'brand' : 'goodwill' ?>"><?= $isBrand ? '★ ' : '' ?><?= e($label) ?></span>
                </div>
                <div class="amb-info">
                    <div class="amb-label"><?= e($label) ?></div>
                    <div class="amb-name"><?= e((string) $p['name']) ?></div>
                    <div class="amb-accent"></div>
                    <p class="amb-bio"><?= $bio($p) ?></p>
                    <?= $linkedin($p) ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php else: /* board, advisory board and management share the board-card layout */ ?>
        <div class="board-grid">
            <?php foreach ($members as $p):
                $src = $photo($p);
                $role = trim((string) ($p['designation'] ?? ''));
                if ($role === '' && $key === 'board') {
                    $role = 'Board Member';
                }
                ?>
            <div class="board-card">
                <div class="board-photo-wrap">
                    <?php if ($src !== ''): ?>
                        <img loading="lazy" decoding="async" src="<?= e($src) ?>" alt="<?= e((string) $p['name']) ?>">
                    <?php else: ?>
                        <?= $placeholder((string) $p['name']) ?>
                    <?php endif; ?>
                    <div class="board-photo-overlay"></div>
                    <div class="board-photo-name">
                        <?php if ($role !== ''): ?><div class="board-photo-role"><?= e($role) ?></div><?php endif; ?>
                        <h3><?= e((string) $p['name']) ?></h3>
                    </div>
                </div>
                <div class="board-info">
                    <div class="board-accent"></div>
                    <p class="board-bio"><?= $bio($p) ?></p>
                    <?= $linkedin($p) ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php endforeach; ?>
