<?php
/**
 * Homepage statistics strip (v3.1). Rendered immediately after the hero slider.
 *
 * Figures are the organisation's own published numbers (same as almarah.org).
 * Each one can be overridden without a code change by adding a row to the
 * `site_settings` table (setting_key / setting_value):
 *   home.stat_children, home.stat_homes, home.stat_rations, home.stat_meals
 * Values must be plain whole numbers; anything else falls back to the default.
 */
$__stat = static function (string $key, int $default): int {
    try {
        $v = app(\App\Services\SettingsService::class)->get('home.' . $key);
        if ($v !== null && preg_match('/^\d{1,9}$/', trim($v)) === 1) {
            return (int) trim($v);
        }
    } catch (\Throwable $e) {
        // Settings unavailable: use the published default. Never break the homepage.
    }
    return $default;
};

$__stats = [
    ['key' => 'stat_children', 'value' => $__stat('stat_children', 275),   'suffix' => '',  'label' => 'Children Supported',        'icon' => 'heart'],
    ['key' => 'stat_homes',    'value' => $__stat('stat_homes', 13),        'suffix' => '',  'label' => 'Homes for Children',        'icon' => 'home'],
    ['key' => 'stat_rations',  'value' => $__stat('stat_rations', 21000),   'suffix' => '+', 'label' => 'Ration Packs Distributed', 'icon' => 'box'],
    ['key' => 'stat_meals',    'value' => $__stat('stat_meals', 1500),      'suffix' => '+', 'label' => 'Meals Provided Daily',     'icon' => 'meal'],
];
$__icons = [
    'heart' => '<path d="M12 21s-7-4.6-9.3-9.1C1.2 8.9 2.8 5 6.4 5c2 0 3.4 1.1 4.2 2.4h2.8C14.2 6.1 15.6 5 17.6 5c3.6 0 5.2 3.9 3.7 6.9C19 16.4 12 21 12 21z"/>',
    'home'  => '<path d="M3 11.5 12 4l9 7.5"/><path d="M5 10v10h14V10"/><path d="M10 20v-6h4v6"/>',
    'box'   => '<path d="M21 8 12 3 3 8v8l9 5 9-5V8z"/><path d="M3 8l9 5 9-5"/><path d="M12 13v8"/>',
    'meal'  => '<path d="M4 3v7a2 2 0 0 0 2 2v9"/><path d="M8 3v7"/><path d="M6 3v4"/><path d="M17 3c-2 1.5-3 4-3 7 0 1.5 1 2 3 2v9"/>',
];
?>
<section class="hv-stats" id="homeStats" aria-label="Almarah Foundation at a glance">
    <div class="hv-stats-inner">
        <?php foreach ($__stats as $i => $s): ?>
        <div class="hv-stat" style="--d: <?= (int) $i * 90 ?>ms">
            <span class="hv-stat-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><?= $__icons[$s['icon']] ?></svg>
            </span>
            <div class="hv-stat-value">
                <span class="hv-count" data-target="<?= (int) $s['value'] ?>"><?= number_format((int) $s['value']) ?></span><?php if ($s['suffix'] !== ''): ?><span class="hv-suffix"><?= e($s['suffix']) ?></span><?php endif; ?>
            </div>
            <div class="hv-stat-label"><?= e($s['label']) ?></div>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<script>
/* Counts up once when the strip scrolls into view. The real number is already
   in the HTML, so with JavaScript off or reduced motion on, nothing is lost. */
(function () {
    var root = document.getElementById('homeStats');
    if (!root || !('IntersectionObserver' in window)) { return; }
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) { return; }
    var nums = root.querySelectorAll('.hv-count');
    var fmt = function (n) { return n.toLocaleString('en-US'); };
    var run = function (el) {
        var target = parseInt(el.getAttribute('data-target'), 10);
        if (!isFinite(target)) { return; }
        var start = null, dur = 1600;
        var step = function (t) {
            if (start === null) { start = t; }
            var p = Math.min(1, (t - start) / dur);
            el.textContent = fmt(Math.round(target * (1 - Math.pow(1 - p, 3))));
            if (p < 1) { requestAnimationFrame(step); } else { el.textContent = fmt(target); }
        };
        el.textContent = '0';
        requestAnimationFrame(step);
    };
    var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) {
            if (e.isIntersecting) {
                root.classList.add('is-in');
                nums.forEach(run);
                io.disconnect();
            }
        });
    }, { threshold: 0.25 });
    io.observe(root);
})();
</script>
