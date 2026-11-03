<?php
/** Photo gallery — media_items (kind = photo), managed from Admin → Media. */
$photos = is_array($photos ?? null) ? $photos : [];
$albums = is_array($albums ?? null) ? $albums : [];
$album = (string) ($album ?? '');
?>
<section class="page-hero">
    <div class="container">
        <span class="eyebrow">Media</span>
        <h1>Photos</h1>
        <p>Moments from our homes, kitchens, classrooms and events.</p>
    </div>
</section>
<section class="section">
    <div class="container">
        <?php if ($albums !== []): ?>
        <nav class="filter-tabs" aria-label="Albums">
            <a class="tab<?= $album === '' ? ' is-active' : '' ?>" href="<?= e(base_url('photos')) ?>">All</a>
            <?php foreach ($albums as $a): ?>
            <a class="tab<?= $album === $a ? ' is-active' : '' ?>" href="<?= e(base_url('photos')) ?>?album=<?= rawurlencode($a) ?>"><?= e($a) ?></a>
            <?php endforeach; ?>
        </nav>
        <?php endif; ?>
        <?php if ($photos === []): ?>
            <p class="empty-state">New photos are on their way. Meanwhile, follow us on Instagram for daily updates.</p>
        <?php else: ?>
        <div class="gallery-grid">
            <?php foreach ($photos as $p): $src = (string) ($p['file_path'] ?: $p['external_url']); $thumb = (string) ($p['thumbnail_path'] ?: $src); ?>
            <a class="gallery-item" href="<?= e(str_starts_with($src, 'http') ? $src : asset($src)) ?>" data-lightbox="photos" data-caption="<?= e((string) $p['title']) ?>">
                <img src="<?= e(str_starts_with($thumb, 'http') ? $thumb : asset($thumb)) ?>" alt="<?= e((string) ($p['alt_text'] ?: $p['title'])) ?>" loading="lazy" decoding="async" width="400" height="300">
                <?php if (!empty($p['title'])): ?><span class="gallery-caption"><?= e((string) $p['title']) ?></span><?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <div class="social-cta">
            <a class="btn btn-outline" href="https://www.instagram.com/almarahfoundation" target="_blank" rel="noopener">Follow @almarahfoundation on Instagram</a>
        </div>
    </div>
</section>
