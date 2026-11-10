<?php
/**
 * Memorandum of Understanding page — records are database-driven (V3.6).
 * Content source: Admin → MOUs & Certificates (type "Memorandum of Understanding").
 *
 * @var list<array<string,mixed>> $mous published MOU records, already in admin order
 */
$mous = is_array($mous ?? null) ? $mous : [];
$mouCount = count($mous);

// Admin-entered write-up: keep basic formatting only and give every paragraph the page style.
$mouBody = static function (array $m): string {
    $html = trim((string) ($m['body_html'] ?? ''));
    if ($html === '') {
        $plain = trim((string) ($m['description'] ?? ''));
        return $plain === '' ? '' : '<p class="mou-entry-body">' . nl2br(e($plain)) . '</p>';
    }
    $html = strip_tags($html, '<p><br><strong><b><em><i><a><ul><ol><li>');
    if (stripos($html, '<p') === false) {
        return '<p class="mou-entry-body">' . nl2br($html) . '</p>';
    }
    return (string) preg_replace('~<p\b[^>]*>~i', '<p class="mou-entry-body">', $html);
};
$mouDate = static function (array $m): string {
    $label = trim((string) ($m['date_label'] ?? ''));
    if ($label !== '') {
        return $label;
    }
    $issued = trim((string) ($m['issued_on'] ?? ''));
    return $issued !== '' ? dt($issued, 'F Y') : '';
};
?>
<div class="mou-page">

<!-- ═══════════ HERO ═══════════ -->
<section class="mou-hero">
  <div class="mou-hero-ring"></div>
  <div class="mou-hero-circle-tl"></div>
  <div class="mou-hero-circle-br"></div>
  <div class="mou-hero-inner">
    <div class="mou-hero-eyebrow">Almarah Foundation</div>
    <h1>Memorandum of <em>Understanding</em></h1>
    <p class="mou-hero-sub">
      Almarah Foundation actively collaborates with leading organisations through Memorandums of Understanding (MoUs) to enhance community development, education, healthcare, and social welfare — each partnership a shared commitment to lasting, meaningful change across Pakistan.
    </p>
    <div class="mou-hero-badges">
      <div class="mou-hero-badge gold">
        <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        <?= (int) $mouCount ?> Active Partner<?= $mouCount === 1 ? '' : 's' ?>
      </div>
      <div class="mou-hero-badge">
        <svg viewBox="0 0 24 24"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
        Healthcare &amp; Education
      </div>
      <div class="mou-hero-badge">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
        Community Impact
      </div>
    </div>
  </div>
</section>

<!-- SECTION INTRO -->
<div class="mou-section-intro mou-animate">
  <div class="mou-section-tag">Our Partners</div>
  <h2 class="mou-section-title">Building a Network of <span class="accent">Shared Purpose</span></h2>
  <p class="mou-section-desc">Each MoU represents a commitment between Almarah Foundation and a partner organisation to work together for the welfare of orphans, underprivileged children, and vulnerable communities across Pakistan.</p>
</div>

<!-- ═══════════ ENTRIES (from the database) ═══════════ -->
<div class="mou-entries">
<?php if ($mous === []): ?>
  <div class="mou-section-intro">
    <p class="mou-section-desc">Our partnership agreements will be listed here soon.</p>
  </div>
<?php endif; ?>
<?php foreach ($mous as $i => $m):
    $reverse = $i % 2 === 1;
    $imgPath = trim((string) ($m['logo_path'] ?? ''));
    $body = $mouBody($m);
    $date = $mouDate($m);
    $category = trim((string) ($m['category'] ?? ''));
    $subtitle = trim((string) ($m['subtitle'] ?? ''));
    $vision = trim((string) ($m['vision_text'] ?? ''));
    $link = trim((string) ($m['external_url'] ?? ''));
    $doc = trim((string) ($m['document_path'] ?? ''));
    ?>

  <div class="mou-entry<?= $reverse ? ' reverse' : '' ?>">
    <div class="mou-entry-img <?= $reverse ? 'mou-animate-right' : 'mou-animate-left' ?>">
      <?php if ($imgPath !== ''): ?>
        <img loading="lazy" decoding="async" src="<?= e(asset($imgPath)) ?>" alt="<?= e((string) $m['name']) ?>">
      <?php else: ?>
        <div class="mou-img-placeholder" style="display:flex;">
          <div class="mou-img-placeholder-icon">
            <svg viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
          </div>
          <span class="mou-img-placeholder-text"><?= e(initials((string) $m['name'])) ?></span>
        </div>
      <?php endif; ?>
    </div>
    <div class="mou-entry-content <?= $reverse ? 'mou-animate-left' : 'mou-animate-right' ?>">
      <div class="mou-entry-number"><?= e(str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)) ?></div>
      <?php if ($category !== ''): ?><div class="mou-entry-category"><?= e($category) ?></div><?php endif; ?>
      <h3 class="mou-entry-name"><?= e((string) $m['name']) ?></h3>
      <?php if ($subtitle !== ''): ?><p class="mou-entry-subtitle"><?= e($subtitle) ?></p><?php endif; ?>
      <div class="mou-divider"></div>
      <?= $body /* sanitised above: basic formatting tags only */ ?>
      <?php if ($vision !== ''): ?>
      <div class="mou-vision-box">
        <div class="mou-vision-label">Vision &amp; Impact</div>
        <p class="mou-vision-text"><?= nl2br(e($vision)) ?></p>
      </div>
      <?php endif; ?>
      <?php if ($date !== ''): ?>
      <div class="mou-date-chip">
        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        <?= e($date) ?>
      </div>
      <?php endif; ?>
      <?php if ($doc !== '' || preg_match('~^https?://~i', $link)): ?>
      <p style="margin-top:14px;font-size:.85rem;font-weight:600">
        <?php if ($doc !== ''): ?><a href="<?= e(asset($doc)) ?>" target="_blank" rel="noopener noreferrer">View signed MoU (PDF)</a><?php endif; ?>
        <?php if ($doc !== '' && preg_match('~^https?://~i', $link)): ?> · <?php endif; ?>
        <?php if (preg_match('~^https?://~i', $link)): ?><a href="<?= e($link) ?>" target="_blank" rel="noopener noreferrer">Visit website</a><?php endif; ?>
      </p>
      <?php endif; ?>
    </div>
  </div>
<?php endforeach; ?>

</div><!-- end mou-entries -->

<!-- ═══════════ CTA ═══════════ -->
<section class="mou-cta">
  <div class="mou-cta-circle1"></div>
  <div class="mou-cta-circle2"></div>
  <div class="mou-cta-inner mou-animate">
    <div class="mou-cta-tag">Collaborate With Us</div>
    <h2 class="mou-cta-title">Partner With <em>Almarah Foundation</em></h2>
    <p class="mou-cta-desc">Join hands with us to create meaningful, lasting impact. Whether you are a hospital, university, business, or NGO — there is a place for your organisation in our growing family of partners.</p>
    <a href="<?= e(base_url('contact')) ?>" class="mou-cta-btn">
      Contact Us to Discuss a Partnership
      <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
    </a>
  </div>
</section>

</div><!-- end mou-page -->
