<?php
/**
 * Homepage V3.6 — everything after the hero slider.
 * No badges / label chips anywhere. Colourful, animated, scroll-driven.
 *
 * The four headline figures can still be overridden from the `site_settings` table:
 * home.stat_children, home.stat_homes, home.stat_rations, home.stat_meals (whole numbers).
 */
$__hx6stat = static function (string $key, int $default): int {
    try {
        $v = app(\App\Services\SettingsService::class)->get('home.' . $key);
        if ($v !== null && preg_match('/^\d{1,9}$/', trim($v)) === 1) {
            return (int) trim($v);
        }
    } catch (\Throwable $e) {
        // Settings unavailable: fall back to the published figure. Never break the homepage.
    }
    return $default;
};

$__stats = [
    ['value' => $__hx6stat('stat_children', 275),  'suffix' => '',  'label' => 'Children Supported',        'icon' => '<path d="M12 21s-7-4.6-9.3-9.1C1.2 8.9 2.8 5 6.4 5c2 0 3.4 1.1 4.2 2.4h2.8C14.2 6.1 15.6 5 17.6 5c3.6 0 5.2 3.9 3.7 6.9C19 16.4 12 21 12 21z"/>'],
    ['value' => $__hx6stat('stat_homes', 13),      'suffix' => '',  'label' => 'Homes for Children',        'icon' => '<path d="M3 11.5 12 4l9 7.5"/><path d="M5 10v10h14V10"/><path d="M10 20v-6h4v6"/>'],
    ['value' => $__hx6stat('stat_rations', 21000), 'suffix' => '+', 'label' => 'Ration Packs Distributed', 'icon' => '<path d="M21 8 12 3 3 8v8l9 5 9-5V8z"/><path d="M3 8l9 5 9-5"/><path d="M12 13v8"/>'],
    ['value' => $__hx6stat('stat_meals', 1500),    'suffix' => '+', 'label' => 'Meals Provided Daily',     'icon' => '<path d="M4 3v7a2 2 0 0 0 2 2v9"/><path d="M8 3v7"/><path d="M6 3v4"/><path d="M17 3c-2 1.5-3 4-3 7 0 1.5 1 2 3 2v9"/>'],
];

$__words = ['Almarah Foundation', 'Compassionate Haven', 'Almarah Grammar School'];
$__arrow = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>';

/** Renders one small statistic tile (number counts up when scrolled into view). */
$__tile = static function (string $target, string $suffix, string $label): string {
    return '<div class="hx6-tile"><b><span class="hx6-count" data-target="' . e($target) . '">0</span>' . e($suffix) . '</b><span>' . e($label) . '</span></div>';
};

$__ctaBg = asset('assets/images/homepage/558493899_756282497392438_8291981798387155468_n.jpg');
?>
<link rel="stylesheet" href="<?= e(asset('assets/css/home-v36.css')) ?>">

<div class="hx6" id="hx6">

    <!-- ═════════ IMPACT ═════════ -->
    <section class="hx6-impact" id="hx6Impact" aria-label="Almarah Foundation at a glance">
        <span class="hx6-blob b1" aria-hidden="true"></span>
        <span class="hx6-blob b2" aria-hidden="true"></span>
        <span class="hx6-blob b3" aria-hidden="true"></span>
        <div class="hx6-wrap">
            <div class="hx6-stats">
                <?php foreach ($__stats as $i => $s): ?>
                <div class="hx6-stat hx6-rv" style="--d:<?= (int) $i * 110 ?>ms">
                    <div class="hx6-stat-ic" aria-hidden="true"><svg viewBox="0 0 24 24"><?= $s['icon'] ?></svg></div>
                    <div class="hx6-stat-v"><span class="hx6-count" data-target="<?= (int) $s['value'] ?>"><?= number_format((int) $s['value']) ?></span><?= e($s['suffix']) ?></div>
                    <div class="hx6-stat-l"><?= e($s['label']) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ═════════ MARQUEE ═════════ -->
    <div class="hx6-marq" aria-hidden="true">
        <div class="hx6-marq-track">
            <?php for ($r = 0; $r < 2; $r++): foreach ($__words as $w): ?><span><?= e($w) ?><i>✦</i></span><?php endforeach; endfor; ?>
        </div>
    </div>

    <!-- ═════════ ABOUT ═════════ -->
    <section class="hx6-about" id="hx6About">
        <div class="hx6-wrap">
            <div class="hx6-about-img hx6-rv l">
                <span class="hx6-ring" aria-hidden="true"></span>
                <div class="hx6-about-photo" data-par="6">
                    <img loading="lazy" decoding="async"
                         src="<?= e(asset('assets/images/homepage/About-Us-rkzrl0xnoqpjqnb41bv8n53ozept5hbagvbmbh3row.jpg')) ?>"
                         alt="About Almarah Foundation — Children in Care">
                </div>
                <span class="hx6-dot d1" data-par="22" aria-hidden="true"></span>
                <span class="hx6-dot d2" data-par="-16" aria-hidden="true"></span>
                <span class="hx6-dot d3" data-par="12" aria-hidden="true"></span>
            </div>

            <div class="hx6-about-body">
                <h2 class="hx6-rv" style="--d:80ms">Every Child Deserves a Place to Call <em class="hx6-grad">"Apna Ghar"</em></h2>
                <div class="hx6-bar hx6-rv" style="--d:160ms"></div>
                <p class="hx6-about-text hx6-rv" style="--d:220ms">
                    At Almarah Foundation, we believe that a child's beginning shouldn't determine their end. Founded on June 4th, 2021, we believe in lasting transformation. Our purpose is to walk alongside every child who passes through our doors — offering a safe atmosphere where children can simply be children, the tools and knowledge they need to break cycles and dream bigger, and lasting empowerment to lead, thrive, and shape a brighter future. Kindness is our foundation; dedication is our heartbeat. Together, we aren't just changing lives — we are rewriting stories.
                </p>

                <div class="hx6-vm">
                    <div class="hx6-vm-card hx6-rv" style="--d:300ms;--c1:#F2C200;--c2:#C9A000">
                        <div class="hx6-vm-ic"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg></div>
                        <h4>Our Vision</h4>
                        <p>A compassionate and inclusive society where every vulnerable child and family thrives with dignity and the opportunity to fulfill their potential.</p>
                    </div>
                    <div class="hx6-vm-card hx6-rv" style="--d:420ms;--c1:#A92D63;--c2:#6B0F35">
                        <div class="hx6-vm-ic"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div>
                        <h4>Our Mission</h4>
                        <p>Alleviating hunger, nurturing orphaned children in home-like environments, delivering accessible education and healthcare, and strengthening families through empowerment.</p>
                    </div>
                </div>

                <a class="hx6-btn hx6-btn-p hx6-rv" style="--d:520ms" href="<?= e(base_url('about')) ?>">Discover Our Story <?= $__arrow ?></a>
            </div>
        </div>
    </section>

    <!-- ═════════ PROGRAMS ═════════ -->
    <section class="hx6-prog" id="hx6Prog">
        <div class="hx6-wrap">
            <div class="hx6-head">
                <h2 class="hx6-rv">Projects and <em class="hx6-grad" style="font-style:normal">Initiatives</em></h2>
                <div class="hx6-bar hx6-rv" style="--d:100ms"></div>
                <p class="hx6-rv" style="--d:160ms">From feeding families to educating children and providing safe homes, every program at Almarah Foundation is designed to create lasting change for those who need it most.</p>
            </div>

            <!-- Parent the Orphan -->
            <div class="hx6-feat">
                <div class="hx6-feat-img hx6-rv l" data-par="-8">
                    <img loading="lazy" decoding="async" src="<?= e(asset('assets/images/homepage/Parent-the-Orphan.jpg')) ?>" alt="Parent the Orphan — Almarah Foundation">
                </div>
                <div class="hx6-feat-body hx6-rv r">
                    <h3>Parent the Orphan</h3>
                    <p>Our flagship program provides orphaned and vulnerable children with a safe, nurturing home environment that meets internationally recognized standards of care. We attend to every child's health, hygiene, mental well-being, education, and recreation.</p>
                    <div class="hx6-tiles">
                        <?= $__tile('275', '+', 'Children in Care') ?>
                        <?= $__tile('13', '', 'Care Homes') ?>
                        <?= $__tile('4.6', 'M', 'Orphans in Pakistan') ?>
                    </div>
                    <a class="hx6-btn hx6-btn-p" href="<?= e(base_url('parent-the-orphan')) ?>">Learn More <?= $__arrow ?></a>
                </div>
            </div>

            <!-- Feed the Hungry -->
            <div class="hx6-feat two">
                <div class="hx6-feat-img hx6-rv r" data-par="-8">
                    <img loading="lazy" decoding="async" src="<?= e(asset('assets/images/homepage/Feed-the-Hungry-1.jpg')) ?>" alt="Feed the Hungry — Almarah Foundation">
                </div>
                <div class="hx6-feat-body hx6-rv l">
                    <h3>Feed the Hungry</h3>
                    <p>We provide daily meals and monthly food rations to individuals and families from lower-income sectors, addressing the most fundamental human need, nourishment, while also offering financial assistance to those who need it most.</p>
                    <div class="hx6-tiles">
                        <?= $__tile('852', '+', 'Daily Breakfasts') ?>
                        <?= $__tile('425', '+', 'Daily Lunches') ?>
                        <?= $__tile('76', '+', 'Ration Packs / Mo') ?>
                    </div>
                    <a class="hx6-btn hx6-btn-p" style="background:linear-gradient(110deg,#C9A000,#F2C200 55%,#FFD740);color:#1A0810;box-shadow:0 14px 34px rgba(242,194,0,.4)" href="<?= e(base_url('feed-the-hungry-2')) ?>">Learn More <?= $__arrow ?></a>
                </div>
            </div>

            <!-- Three more programs -->
            <div class="hx6-cards">
                <a class="hx6-card hx6-tilt hx6-rv" style="--d:0ms" href="<?= e(base_url('educate-pakistan')) ?>">
                    <div class="hx6-card-img"><img loading="lazy" decoding="async" src="<?= e(asset('assets/images/homepage/Educate-Pakistan.jpg')) ?>" alt="Educate Pakistan — Almarah Grammar School"></div>
                    <div class="hx6-card-body">
                        <h3>Educate Pakistan</h3>
                        <p>Almarah Grammar School on Canal Road, Lahore offers Playgroup through High School education, empowering underprivileged children to break the cycle of poverty.</p>
                        <div class="hx6-tiles"><?= $__tile('300', '+', 'Students') ?><?= $__tile('23', '', 'Teachers') ?></div>
                        <span class="hx6-card-link">Explore Program <?= $__arrow ?></span>
                    </div>
                </a>

                <a class="hx6-card hx6-tilt hx6-rv" style="--d:140ms" href="<?= e(base_url('izat-ki-roti')) ?>">
                    <div class="hx6-card-img"><img loading="lazy" decoding="async" src="<?= e(asset('assets/images/projects/DSC07430-scaled.jpg')) ?>" alt="Izzat ki Roti — Dignified Food Initiative"></div>
                    <div class="hx6-card-body">
                        <h3>Izzat ki Roti</h3>
                        <p>A dignified food initiative that allows individuals to receive a meal without the stigma of charity. We preserve the self-respect of every person we serve.</p>
                        <div class="hx6-tiles"><?= $__tile('100', '', 'Tandoors Active') ?><?= $__tile('100', '%', 'Dignity Kept') ?></div>
                        <span class="hx6-card-link">Explore Program <?= $__arrow ?></span>
                    </div>
                </a>

                <a class="hx6-card hx6-tilt hx6-rv" style="--d:280ms" href="https://almarahhaven.org/">
                    <div class="hx6-card-img"><img loading="lazy" decoding="async" src="<?= e(asset('assets/images/homepage/comp.jpeg')) ?>" alt="Compassionate Haven — Special Needs Home"></div>
                    <div class="hx6-card-body">
                        <h3>Compassionate Haven</h3>
                        <p>A dedicated home for children with special needs offering sensory-friendly spaces, therapy rooms, and specialized learning tools to help every child thrive.</p>
                        <div class="hx6-tiles"><?= $__tile('1', '', 'Dedicated Home') ?><?= $__tile('24', '/7', 'Care Support') ?></div>
                        <span class="hx6-card-link">Explore Program <?= $__arrow ?></span>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <!-- ═════════ CALL TO ACTION ═════════ -->
    <section class="hx6-cta" aria-labelledby="hx6CtaTitle" style="--hx6-bg:url('<?= e($__ctaBg) ?>')">
        <span class="hx6-blob b1" aria-hidden="true"></span>
        <span class="hx6-blob b2" aria-hidden="true"></span>
        <?php foreach ([[8, 26, 14], [22, 20, 19], [38, 34, 12], [57, 22, 17], [73, 30, 21], [88, 18, 15]] as [$left, $size, $dur]): ?>
        <svg class="hx6-heart" viewBox="0 0 24 24" aria-hidden="true" style="left:<?= (int) $left ?>%;width:<?= (int) $size ?>px;height:<?= (int) $size ?>px;animation-duration:<?= (int) $dur ?>s;animation-delay:-<?= (int) $left % 9 ?>s"><path d="M12 21s-7-4.6-9.3-9.1C1.2 8.9 2.8 5 6.4 5c2 0 3.4 1.1 4.2 2.4h2.8C14.2 6.1 15.6 5 17.6 5c3.6 0 5.2 3.9 3.7 6.9C19 16.4 12 21 12 21z"/></svg>
        <?php endforeach; ?>
        <div class="hx6-wrap">
            <h2 id="hx6CtaTitle" class="hx6-rv">Together, We Can Create a <em>Lasting Impact</em></h2>
            <p class="hx6-rv" style="--d:120ms">Every contribution helps us support communities, empower individuals, and create meaningful opportunities for a better future.</p>
            <div class="hx6-cta-act hx6-rv" style="--d:240ms">
                <a class="hx6-btn hx6-btn-gold" href="<?= e(base_url('donate')) ?>">Donate Now
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s-7-4.6-9.3-9.1C1.2 8.9 2.8 5 6.4 5c2 0 3.4 1.1 4.2 2.4h2.8C14.2 6.1 15.6 5 17.6 5c3.6 0 5.2 3.9 3.7 6.9C19 16.4 12 21 12 21z"/></svg>
                </a>
                <a class="hx6-btn hx6-btn-ghost" href="<?= e(base_url('projects')) ?>">Explore Our Projects <?= $__arrow ?></a>
            </div>
        </div>
    </section>
</div>

<script>
/* Homepage V3.6 effects: scroll reveals, count-up numbers, card tilt, gentle parallax.
   Everything degrades gracefully: with JavaScript off (or reduced motion) all content is visible,
   and every number is already printed in the HTML or shown by the final value below. */
(function () {
    var root = document.getElementById('hx6');
    if (!root) { return; }
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var fmt = function (n, dec) { return dec ? n.toFixed(1) : Math.round(n).toLocaleString('en-US'); };

    // Counters (target is also the final text, so nothing is lost if animation is skipped).
    var run = function (el) {
        var t = parseFloat(el.getAttribute('data-target'));
        if (!isFinite(t)) { return; }
        var dec = t % 1 !== 0;
        if (reduce) { el.textContent = fmt(t, dec); return; }
        var start = null, dur = 2200;
        var step = function (ts) {
            if (start === null) { start = ts; }
            var p = Math.min(1, (ts - start) / dur);
            el.textContent = fmt(t * (1 - Math.pow(1 - p, 4)), dec);
            if (p < 1) { requestAnimationFrame(step); } else { el.textContent = fmt(t, dec); }
        };
        requestAnimationFrame(step);
    };
    var counters = [].slice.call(root.querySelectorAll('.hx6-count'));
    counters.forEach(function (el) {
        // Pre-fill non-animated final values for no-IntersectionObserver browsers.
        var t = parseFloat(el.getAttribute('data-target'));
        if (isFinite(t)) { el.textContent = fmt(t, t % 1 !== 0); }
    });

    if (!('IntersectionObserver' in window)) { return; }
    root.classList.add('hx6-js');

    var seen = new IntersectionObserver(function (entries) {
        entries.forEach(function (en) {
            if (!en.isIntersecting) { return; }
            var el = en.target;
            el.classList.add('is-in');
            seen.unobserve(el);
            // Once the entrance has finished, hand the element back to its normal hover/tilt styles.
            var wait = 1300 + (parseInt(el.style.getPropertyValue('--d'), 10) || 0);
            setTimeout(function () { el.classList.remove('hx6-rv'); el.style.removeProperty('--d'); }, wait);
            [].forEach.call(en.target.querySelectorAll('.hx6-count'), run);
            if (en.target.classList.contains('hx6-count')) { run(en.target); }
        });
    }, { threshold: 0.18, rootMargin: '0px 0px -40px 0px' });
    [].forEach.call(root.querySelectorAll('.hx6-rv'), function (el) { seen.observe(el); });

    // Counters that are not inside a reveal block.
    var cio = new IntersectionObserver(function (entries) {
        entries.forEach(function (en) {
            if (en.isIntersecting) { run(en.target); cio.unobserve(en.target); }
        });
    }, { threshold: 0.4 });
    counters.forEach(function (el) {
        if (!el.closest('.hx6-rv')) { cio.observe(el); }
    });

    if (reduce) { return; }

    // 3D tilt + light glare on the program cards (mouse / pen only).
    [].forEach.call(root.querySelectorAll('.hx6-tilt'), function (card) {
        card.addEventListener('pointermove', function (e) {
            if (e.pointerType === 'touch') { return; }
            var r = card.getBoundingClientRect();
            var x = (e.clientX - r.left) / r.width, y = (e.clientY - r.top) / r.height;
            card.style.setProperty('--ry', ((x - 0.5) * 9).toFixed(2) + 'deg');
            card.style.setProperty('--rx', ((0.5 - y) * 9).toFixed(2) + 'deg');
            card.style.setProperty('--mx', (x * 100).toFixed(1) + '%');
            card.style.setProperty('--my', (y * 100).toFixed(1) + '%');
        });
        card.addEventListener('pointerleave', function () {
            card.style.setProperty('--rx', '0deg');
            card.style.setProperty('--ry', '0deg');
        });
    });

    // Gentle scroll parallax on marked elements.
    var par = [].slice.call(root.querySelectorAll('[data-par]'));
    var ticking = false;
    var update = function () {
        ticking = false;
        var vh = window.innerHeight;
        par.forEach(function (el) {
            var r = el.getBoundingClientRect();
            if (r.bottom < -80 || r.top > vh + 80) { return; }
            var k = ((r.top + r.height / 2) - vh / 2) / vh;       // -0.5 … 0.5 around the viewport centre
            var d = parseFloat(el.getAttribute('data-par')) || 0;
            el.style.translate = '0 ' + (k * d * -2).toFixed(1) + 'px';
        });
    };
    window.addEventListener('scroll', function () {
        if (!ticking) { ticking = true; requestAnimationFrame(update); }
    }, { passive: true });
    update();
})();
</script>
