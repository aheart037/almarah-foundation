<?php
/** Videos — media_items (kind = video, external_url = YouTube link), managed from Admin → Media. */
$videos = is_array($videos ?? null) ? $videos : [];
$ytId = static function (string $url): string {
    if (preg_match('~(?:youtu\.be/|v=|/embed/|/shorts/)([A-Za-z0-9_-]{6,})~', $url, $m)) {
        return $m[1];
    }
    return '';
};
?>
<section class="page-hero">
    <div class="container">
        <span class="eyebrow">Media</span>
        <h1>Videos</h1>
        <p>Stories from the children, families and volunteers of Almarah Foundation.</p>
    </div>
</section>
<section class="section">
    <div class="container">
        <?php if ($videos === []): ?>
            <p class="empty-state">Our latest videos are published on YouTube.</p>
        <?php else: ?>
        <div class="video-grid">
            <?php foreach ($videos as $v): $id = $ytId((string) $v['external_url']); if ($id === '') { continue; } ?>
            <article class="video-card">
                <div class="video-embed" data-video-id="<?= e($id) ?>" data-title="<?= e((string) $v['title']) ?>" role="button" tabindex="0" aria-label="Play: <?= e((string) $v['title']) ?>">
                    <img src="<?= e(!empty($v['thumbnail_path']) ? asset((string) $v['thumbnail_path']) : 'https://i.ytimg.com/vi/' . $id . '/hqdefault.jpg') ?>" alt="" loading="lazy" decoding="async" width="480" height="360">
                    <span class="play" aria-hidden="true"></span>
                </div>
                <h3><?= e((string) $v['title']) ?></h3>
                <?php if (!empty($v['description'])): ?><p><?= e((string) $v['description']) ?></p><?php endif; ?>
            </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <div class="social-cta">
            <a class="btn btn-primary" href="https://www.youtube.com/channel/UC8IcvYyxG4TeZOan42VDj1A/" target="_blank" rel="noopener">Watch on our YouTube channel</a>
        </div>
    </div>
</section>
