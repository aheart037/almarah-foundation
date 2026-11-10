<?php
/**
 * Certificates & Registrations page — records are database-driven (V3.6).
 * Content source: Admin → MOUs & Certificates (types "Certificate" and "Registration").
 *
 * @var list<array<string,mixed>> $certificates published records, already in admin order
 */
$certificates = is_array($certificates ?? null) ? $certificates : [];
?>
<!-- HERO -->
<section class="lc-hero">
    <div class="lc-hero-circle-tl"></div>
    <div class="lc-hero-circle-br"></div>
    <div class="lc-hero-inner">
        <span class="hx-eyebrow">Compliance &amp; Trust</span>
        <h1>Licenses, Certificates <em>& Registrations</em></h1>
        <p class="lc-hero-sub">
            Almarah Foundation is fully committed to transparency, compliance, and accountability. We operate under all required legal registrations and maintain up-to-date licenses and certifications issued by relevant authorities.
        </p>
        <div class="lc-hero-badges">
            <div class="lc-hero-badge gold">
                <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                Fully Compliant
            </div>
            <div class="lc-hero-badge">
                <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                Government Registered
            </div>
            <div class="lc-hero-badge">
                <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Publicly Available
            </div>
        </div>
    </div>
</section>

<!-- CERTIFICATES GRID -->
<section class="lc-main">
    <div class="lc-wrap">

        <div class="lc-section-header">
            <div class="lc-section-tag">Official Documents</div>
            <h2 class="lc-section-title">Our <span class="accent">Registrations</span> and Certificates</h2>
            <p class="lc-section-desc">
                These documents reflect our dedication to maintaining high standards in our operations and ensuring trust with our partners, donors, and the communities we serve.
            </p>
        </div>

        <div class="lc-grid">
<?php if ($certificates === []): ?>
            <p class="lc-section-desc">Our certificates and registrations will be listed here soon.</p>
<?php endif; ?>
<?php foreach ($certificates as $c):
    $doc = trim((string) ($c['document_path'] ?? ''));
    $link = trim((string) ($c['external_url'] ?? ''));
    $href = $doc !== '' ? asset($doc) : (preg_match('~^https?://~i', $link) ? $link : '');
    $action = $doc !== '' ? 'View PDF' : ($href !== '' ? 'View online' : '');
    $expires = trim((string) ($c['expires_on'] ?? ''));
    $tag = $href !== '' ? 'a' : 'div';
    ?>

            <<?= $tag ?><?php if ($href !== ''): ?> href="<?= e($href) ?>" target="_blank" rel="noopener noreferrer"<?php endif; ?> class="lc-card">
                <div class="lc-card-icon-wrap">
                    <svg viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                        <polyline points="10 9 9 9 8 9"/>
                    </svg>
                </div>
                <div class="lc-card-info">
                    <div class="lc-card-title"><?= e((string) $c['name']) ?></div>
                    <?php if ($action !== '' || $expires !== ''): ?>
                    <div class="lc-card-meta">
                        <?php if ($action !== ''): ?>
                        <span class="lc-card-type">
                            <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            <?= e($action) ?>
                        </span>
                        <?php endif; ?>
                        <?php if ($expires !== ''): ?>
                        <span class="lc-card-type">Valid until <?= e(dt($expires, 'j M Y')) ?></span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php if ($href !== ''): ?>
                <div class="lc-card-arrow">
                    <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                </div>
                <?php endif; ?>
            </<?= $tag ?>>
<?php endforeach; ?>

        </div>
    </div>
</section>
