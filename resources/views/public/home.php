<?php
/**
 * Home page: admin-managed hero slider (cms_slides) followed by the
 * migrated home sections (About, Programs, Impact, Gallery, CTA).
 *
 * @var list<array<string,mixed>> $slides
 */
$slides = is_array($slides ?? null) ? $slides : [];
?>
<?php if ($slides !== []): ?>
<section class="hero-slider" id="heroSlider" aria-roledescription="carousel" aria-label="Highlights" data-autoplay="1" data-interval="6000" tabindex="0">
    <?php foreach ($slides as $i => $slide):
        $hasText = trim((string) ($slide['title'] ?? '')) !== '' || trim((string) ($slide['subtitle'] ?? '')) !== '';
        $overlay = max(0, min(90, (int) ($slide['overlay_opacity'] ?? 0))) / 100;
        $mobile = (string) ($slide['mobile_image_path'] ?? '');
    ?>
    <div class="hero-slide slide-pos-<?= e((string) ($slide['text_position'] ?? 'left')) ?><?= $i === 0 ? ' is-active' : '' ?>"
         role="group" aria-roledescription="slide" aria-label="<?= $i + 1 ?> of <?= count($slides) ?>" aria-hidden="<?= $i === 0 ? 'false' : 'true' ?>"
         style="--overlay: <?= $overlay ?>">
        <picture>
            <?php if ($mobile !== ''): ?><source media="(max-width: 640px)" srcset="<?= e(asset($mobile)) ?>"><?php endif; ?>
            <?php if ($i === 0): ?>
            <img src="<?= e(asset((string) $slide['image_path'])) ?>" alt="<?= e((string) ($slide['image_alt'] ?? '')) ?>" fetchpriority="high" decoding="async" width="1920" height="800">
            <?php else: ?>
            <img src="<?= e(asset((string) $slide['image_path'])) ?>" alt="<?= e((string) ($slide['image_alt'] ?? '')) ?>" loading="lazy" decoding="async" width="1920" height="800">
            <?php endif; ?>
        </picture>
        <?php if ($overlay > 0): ?><div class="slide-overlay" aria-hidden="true"></div><?php endif; ?>
        <?php if ($hasText || !empty($slide['button_label'])): ?>
        <div class="slide-content">
            <div class="container">
                <div class="slide-box">
                    <?php if (!empty($slide['title'])): ?><h2><?= e((string) $slide['title']) ?></h2><?php endif; ?>
                    <?php if (!empty($slide['subtitle'])): ?><p><?= e((string) $slide['subtitle']) ?></p><?php endif; ?>
                    <?php if (!empty($slide['button_label']) && !empty($slide['button_url'])): ?>
                        <a class="btn btn-accent" href="<?= e((string) $slide['button_url']) ?>"><?= e((string) $slide['button_label']) ?></a>
                    <?php endif; ?>
                    <?php if (!empty($slide['button2_label']) && !empty($slide['button2_url'])): ?>
                        <a class="btn btn-outline-light" href="<?= e((string) $slide['button2_url']) ?>"><?= e((string) $slide['button2_label']) ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>

    <?php if (count($slides) > 1): ?>
    <button type="button" class="hero-arrow prev" aria-label="Previous slide"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 5l-7 7 7 7"/></svg></button>
    <button type="button" class="hero-arrow next" aria-label="Next slide"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 5l7 7-7 7"/></svg></button>
    <div class="hero-dots" role="tablist" aria-label="Choose slide">
        <?php foreach ($slides as $i => $slide): ?>
        <button type="button" role="tab" aria-selected="<?= $i === 0 ? 'true' : 'false' ?>" aria-label="Slide <?= $i + 1 ?>"></button>
        <?php endforeach; ?>
    </div>
    <div class="hero-progress" aria-hidden="true"><span></span></div>
    <?php endif; ?>
</section>
<?php endif; ?>

<?php require __DIR__ . '/pages/home-sections.php'; ?>
