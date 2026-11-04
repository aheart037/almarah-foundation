<?php
/**
 * Homepage closing hero (v3.1). Rendered immediately before the site footer.
 * Uses a real Almarah Foundation photograph already in the repository.
 */
$__bg = asset('assets/images/homepage/558493899_756282497392438_8291981798387155468_n.jpg');
?>
<section class="hv-cta" aria-labelledby="hvCtaTitle" style="--hv-cta-bg: url('<?= e($__bg) ?>')">
    <div class="hv-cta-overlay" aria-hidden="true"></div>
    <div class="hv-cta-inner">
        <span class="hv-cta-eyebrow">Join Our Mission</span>
        <h2 id="hvCtaTitle">Together, We Can Create a <em>Lasting Impact</em></h2>
        <p>Every contribution helps us support communities, empower individuals, and create meaningful opportunities for a better future.</p>
        <div class="hv-cta-actions">
            <a class="hv-btn hv-btn-gold" href="<?= e(base_url('donate')) ?>">
                Donate Now
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s-7-4.6-9.3-9.1C1.2 8.9 2.8 5 6.4 5c2 0 3.4 1.1 4.2 2.4h2.8C14.2 6.1 15.6 5 17.6 5c3.6 0 5.2 3.9 3.7 6.9C19 16.4 12 21 12 21z"/></svg>
            </a>
            <a class="hv-btn hv-btn-ghost" href="<?= e(base_url('projects')) ?>">
                Explore Our Projects
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
            </a>
        </div>
    </div>
</section>
