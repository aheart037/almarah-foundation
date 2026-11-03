<?php
/**
 * Shared layout for programme pages (Parent the Orphan, Feed the Hungry,
 * Educate Pakistan). Content comes from the calling view as $prog:
 *
 *  tag, title, lead, hero_images[], stats[[num,label,suffix]], intro{eyebrow,title,text[],image,cta_label,cta_url},
 *  facts[] (optional), pillars{title,items[[image,title,text]]}, gallery{title,items[[image,caption]]} (optional),
 *  spotlight{eyebrow,title,text,image,bullets[]} (optional), cta{title,text,label,url}
 */
$prog = is_array($prog ?? null) ? $prog : [];
$donate = base_url('donate') . (!empty($prog['purpose']) ? '?purpose=' . rawurlencode((string) $prog['purpose']) : '');
?>
<section class="prog-hero">
    <div class="container">
        <div class="prog-hero-text">
            <span class="eyebrow"><?= e((string) ($prog['tag'] ?? 'Our Projects')) ?></span>
            <h1><?= e((string) $prog['title']) ?></h1>
            <p class="lead"><?= e((string) $prog['lead']) ?></p>
            <div class="prog-hero-actions">
                <a class="btn btn-gold" href="<?= e($donate) ?>"><?= e((string) ($prog['intro']['cta_label'] ?? 'Donate Now')) ?></a>
                <a class="btn btn-outline-light" href="#how">Learn more</a>
            </div>
        </div>
        <?php if (!empty($prog['hero_images'])): ?>
        <div class="prog-hero-images" aria-hidden="true">
            <?php foreach (array_slice($prog['hero_images'], 0, 3) as $i => $img): ?>
            <img src="<?= e(asset($img)) ?>" alt="" <?= $i === 0 ? 'fetchpriority="high"' : 'loading="lazy"' ?> decoding="async" width="420" height="520">
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php if (!empty($prog['stats'])): ?>
<section class="prog-stats">
    <div class="container">
        <div class="stats-row">
            <?php foreach ($prog['stats'] as [$num, $label, $suffix]): ?>
            <div class="stat-card" data-reveal>
                <div class="num"><span class="count" data-target="<?= (int) $num ?>"><?= number_format((int) $num) ?></span><?= e((string) $suffix) ?></div>
                <div class="lbl"><?= e((string) $label) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="section prog-intro" id="how">
    <div class="container prog-split">
        <div class="prog-split-media" data-reveal>
            <img src="<?= e(asset((string) $prog['intro']['image'])) ?>" alt="<?= e((string) $prog['title']) ?>" loading="lazy" decoding="async" width="720" height="560">
        </div>
        <div class="prog-split-text" data-reveal>
            <span class="eyebrow"><?= e((string) $prog['intro']['eyebrow']) ?></span>
            <h2><?= e((string) $prog['intro']['title']) ?></h2>
            <div class="divider"><span class="gem"></span></div>
            <?php foreach ((array) $prog['intro']['text'] as $p): ?><p><?= e((string) $p) ?></p><?php endforeach; ?>
            <?php if (!empty($prog['facts'])): ?>
            <ul class="prog-facts">
                <?php foreach ($prog['facts'] as $f): ?><li><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg><?= e((string) $f) ?></li><?php endforeach; ?>
            </ul>
            <?php endif; ?>
            <a class="btn btn-primary" href="<?= e($donate) ?>"><?= e((string) ($prog['intro']['cta_label'] ?? 'Donate Now')) ?></a>
        </div>
    </div>
</section>

<?php if (!empty($prog['pillars'])): ?>
<section class="section section-blush prog-pillars">
    <div class="container">
        <div class="section-head center">
            <h2><?= e((string) $prog['pillars']['title']) ?></h2>
            <div class="divider center"><span class="gem"></span></div>
        </div>
        <div class="pillar-grid">
            <?php foreach ($prog['pillars']['items'] as [$img, $title, $text]): ?>
            <article class="pillar-card" data-reveal>
                <div class="pillar-img"><img src="<?= e(asset($img)) ?>" alt="<?= e($title) ?>" loading="lazy" decoding="async" width="480" height="320"></div>
                <div class="pillar-body"><h3><?= e($title) ?></h3><?php if ($text !== ''): ?><p><?= e($text) ?></p><?php endif; ?></div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($prog['spotlight'])): $sp = $prog['spotlight']; ?>
<section class="section prog-spotlight">
    <div class="container prog-split reverse">
        <div class="prog-split-media" data-reveal>
            <img src="<?= e(asset((string) $sp['image'])) ?>" alt="<?= e((string) $sp['title']) ?>" loading="lazy" decoding="async" width="720" height="560">
        </div>
        <div class="prog-split-text" data-reveal>
            <span class="eyebrow"><?= e((string) $sp['eyebrow']) ?></span>
            <h2><?= e((string) $sp['title']) ?></h2>
            <div class="divider"><span class="gem"></span></div>
            <p><?= e((string) $sp['text']) ?></p>
            <?php if (!empty($sp['bullets'])): ?>
            <ul class="prog-facts">
                <?php foreach ($sp['bullets'] as $b): ?><li><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg><?= e((string) $b) ?></li><?php endforeach; ?>
            </ul>
            <?php endif; ?>
            <?php if (!empty($sp['link_url'])): ?><a class="btn btn-outline" href="<?= e((string) $sp['link_url']) ?>" <?= str_starts_with((string) $sp['link_url'], 'http') ? 'target="_blank" rel="noopener"' : '' ?>><?= e((string) ($sp['link_label'] ?? 'Explore more')) ?></a><?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($prog['gallery'])): ?>
<section class="section section-blush prog-gallery">
    <div class="container">
        <div class="section-head center">
            <h2><?= e((string) $prog['gallery']['title']) ?></h2>
            <div class="divider center"><span class="gem"></span></div>
        </div>
        <div class="gallery-grid">
            <?php foreach ($prog['gallery']['items'] as [$img, $caption]): ?>
            <a class="gallery-item" href="<?= e(asset($img)) ?>" data-lightbox="prog" data-caption="<?= e($caption) ?>">
                <img src="<?= e(asset($img)) ?>" alt="<?= e($caption) ?>" loading="lazy" decoding="async" width="400" height="300">
                <span class="gallery-caption"><?= e($caption) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="cta-band">
    <div class="container">
        <h2><?= e((string) $prog['cta']['title']) ?></h2>
        <p><?= e((string) $prog['cta']['text']) ?></p>
        <div class="cta-actions">
            <a class="btn btn-gold" href="<?= e($donate) ?>"><?= e((string) ($prog['cta']['label'] ?? 'Donate Now')) ?></a>
            <a class="btn btn-outline-light" href="<?= e(base_url('become-a-volunteer')) ?>">Become a Volunteer</a>
        </div>
    </div>
</section>
