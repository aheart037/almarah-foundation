<?php
/** Admin-authored page body (cms_pages, template "html"). @var array $page */
$page = is_array($page ?? null) ? $page : [];
?>
<?php if (!empty($page['hero_title']) || !empty($page['title'])): ?>
<section class="page-hero<?= !empty($page['hero_image']) ? ' has-image' : '' ?>"<?= !empty($page['hero_image']) ? ' style="--hero-img:url(' . e(asset((string) $page['hero_image'])) . ')"' : '' ?>>
    <div class="container">
        <h1><?= e((string) ($page['hero_title'] ?: $page['title'])) ?></h1>
        <?php if (!empty($page['hero_subtitle'])): ?><p><?= e((string) $page['hero_subtitle']) ?></p><?php endif; ?>
    </div>
</section>
<?php endif; ?>
<section class="section">
    <div class="container prose">
        <?= $page['body_html'] ?? '' ?>
    </div>
</section>
