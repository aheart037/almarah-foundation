<?php
/**
 * Shared page hero (v3.2) — used by pages that previously had no hero banner
 * (CEO's Message, Chairperson's Message, E-Book). Styling lives in
 * assets/css/hero-v32.css (.hx-hero).
 *
 * Expects a local $hero array:
 *   eyebrow  string        small chip above the title
 *   title    string        plain part of the title
 *   accent   string        gold gradient part of the title (optional)
 *   sub      string        supporting paragraph (optional)
 *   chips    string[]      glass pills under the paragraph (optional)
 *   next     string        CSS colour of the section below (optional, default blush)
 */
$hero = is_array($hero ?? null) ? $hero : [];
$__hxNext = (string) ($hero['next'] ?? '');
?>
<section class="hx-hero"<?= $__hxNext !== '' ? ' style="--hx-next:' . e($__hxNext) . '"' : '' ?>>
    <div class="hx-inner">
        <?php if (!empty($hero['eyebrow'])): ?><span class="hx-eyebrow"><?= e((string) $hero['eyebrow']) ?></span><?php endif; ?>
        <h1><?= e((string) ($hero['title'] ?? '')) ?><?php if (!empty($hero['accent'])): ?> <em><?= e((string) $hero['accent']) ?></em><?php endif; ?></h1>
        <?php if (!empty($hero['sub'])): ?><p class="hx-sub"><?= e((string) $hero['sub']) ?></p><?php endif; ?>
        <?php if (!empty($hero['chips']) && is_array($hero['chips'])): ?>
        <div class="hx-chips">
            <?php foreach ($hero['chips'] as $i => $chip): ?>
            <span class="hx-chip<?= $i === 0 ? ' gold' : '' ?>"><?= e((string) $chip) ?></span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>
