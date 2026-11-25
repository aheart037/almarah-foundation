<?php
/**
 * Site footer (v4.0 redesign): wine gradient with gold accents, subscribe + donate call-to-action card,
 * brand / quick links / get involved / contact columns, and a bottom bar.
 *
 * Variables come from layouts/public.php: $appName, $footerLogoUrl, $orgAddress, $orgEmail, $orgPhone,
 * $orgLandline, $social, $footerQuick, $footerMore. Styles: assets/css/footer-v40.css (scoped to .sf4).
 * Hooks kept for site.js: form[data-ajax-form] + .form-feedback, #footerEmail, #backToTop.
 */
$year = date('Y');
$socialIcons = [
    'facebook'  => '<svg viewBox="0 0 24 24"><path d="M14 8h3V4h-3a4 4 0 0 0-4 4v2H8v4h2v8h4v-8h3l1-4h-4V8a1 1 0 0 1 0 0z"/></svg>',
    'instagram' => '<svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor"/></svg>',
    'youtube'   => '<svg viewBox="0 0 24 24"><path d="M22 8s-.2-1.6-.9-2.3c-.8-.9-1.8-.9-2.2-1C15.8 4.5 12 4.5 12 4.5s-3.8 0-6.9.2c-.4.1-1.4.1-2.2 1C2.2 6.4 2 8 2 8s-.2 1.9-.2 3.8v1.7C1.8 15.4 2 17.3 2 17.3s.2 1.6.9 2.3c.8.9 1.9.8 2.4.9 1.7.2 6.7.2 6.7.2s3.8 0 6.9-.2c.4-.1 1.4-.1 2.2-1 .7-.7.9-2.3.9-2.3s.2-1.9.2-3.8v-1.7C22.2 9.9 22 8 22 8z"/><path d="m10 15 5-3-5-3z" fill="currentColor" stroke="none"/></svg>',
    'linkedin'  => '<svg viewBox="0 0 24 24"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-4 0v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg>',
    'tiktok'    => '<svg viewBox="0 0 24 24"><path d="M15 3v9.5a3.5 3.5 0 1 1-3.5-3.5"/><path d="M15 3c.5 2.6 2.4 4.3 5 4.5"/></svg>',
];
$telHref = static fn (string $n): string => 'tel:' . preg_replace('/[^+\d]/', '', $n);
?>
<footer class="site-footer sf4">
  <div class="sf4-wave" aria-hidden="true">
    <svg class="sf4-wave-back" viewBox="0 0 2880 60" preserveAspectRatio="none" focusable="false"><path d="M0 34Q360 6 720 34T1440 34T2160 34T2880 34V60H0z"/></svg>
    <svg class="sf4-wave-front" viewBox="0 0 2880 60" preserveAspectRatio="none" focusable="false"><path d="M0 30Q360 2 720 30T1440 30T2160 30T2880 30V60H0z"/></svg>
  </div>
  <div class="sf4-bg" aria-hidden="true">
    <span class="sf4-spark" style="--x:6%;--s:5px;--t:11s;--d:-0s"></span><span class="sf4-spark" style="--x:14%;--s:3px;--t:9s;--d:-3s"></span><span class="sf4-spark" style="--x:22%;--s:6px;--t:13s;--d:-6s"></span><span class="sf4-spark" style="--x:31%;--s:4px;--t:10s;--d:-2s"></span><span class="sf4-spark" style="--x:40%;--s:3px;--t:12s;--d:-8s"></span><span class="sf4-spark" style="--x:48%;--s:5px;--t:9s;--d:-5s"></span><span class="sf4-spark" style="--x:57%;--s:4px;--t:14s;--d:-1s"></span><span class="sf4-spark" style="--x:64%;--s:6px;--t:11s;--d:-7s"></span><span class="sf4-spark" style="--x:72%;--s:3px;--t:10s;--d:-4s"></span><span class="sf4-spark" style="--x:80%;--s:5px;--t:13s;--d:-9s"></span><span class="sf4-spark" style="--x:88%;--s:4px;--t:9s;--d:-2s"></span><span class="sf4-spark" style="--x:94%;--s:6px;--t:12s;--d:-6s"></span>
    <span class="sf4-glow sf4-glow-a"></span>
    <span class="sf4-glow sf4-glow-b"></span>
    <svg class="sf4-heart" viewBox="0 0 200 180" focusable="false"><path d="M100 168S12 112 12 56a44 44 0 0 1 88-8 44 44 0 0 1 88 8c0 56-88 112-88 112z"/><path d="M100 140S42 100 42 60a28 28 0 0 1 58-4 28 28 0 0 1 58 4c0 40-58 80-58 80z"/></svg>
    <svg class="sf4-rings" viewBox="0 0 400 400" focusable="false"><circle cx="200" cy="200" r="60"/><circle cx="200" cy="200" r="110"/><circle cx="200" cy="200" r="160"/><circle cx="200" cy="200" r="198"/></svg>
  </div>

  <div class="container sf4-wrap">
    <section class="sf4-cta" aria-labelledby="sf4CtaTitle" data-sf-reveal>
      <div class="sf4-cta-text">
        <span class="sf4-eyebrow">Recent Updates</span>
        <h2 id="sf4CtaTitle">Join us in giving every child a brighter tomorrow</h2>
        <p>Subscribe to receive stories of impact, project updates and ways to help.</p>
      </div>
      <div class="sf4-cta-act">
        <form class="footer-subscribe sf4-sub" method="post" action="<?= e(base_url('forms/newsletter')) ?>" data-ajax-form>
          <?= csrf_field() ?>
          <input type="hidden" name="source" value="footer">
          <input type="text" name="website" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">
          <label class="sr-only" for="footerEmail">Email address</label>
          <span class="sf4-sub-field">
            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
            <input id="footerEmail" type="email" name="email" placeholder="Your email address" required autocomplete="email" inputmode="email">
          </span>
          <button type="submit" class="btn btn-gold">Subscribe</button>
          <p class="form-feedback" aria-live="polite"></p>
        </form>
        <a class="sf4-donate" href="<?= e(base_url('donate')) ?>">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s-8-5.2-8-11a4.6 4.6 0 0 1 8-3 4.6 4.6 0 0 1 8 3c0 5.8-8 11-8 11z"/></svg>
          Donate Now
        </a>
      </div>
    </section>

    <div class="sf4-grid">
      <div class="sf4-col sf4-about" data-sf-reveal style="--i:1">
        <a href="<?= e(base_url()) ?>/" class="sf4-brand"><img src="<?= e($footerLogoUrl) ?>" alt="<?= e($appName) ?>" width="223" height="42" loading="lazy" decoding="async"></a>
        <p>Almarah Foundation is a registered non-governmental organization (NGO) and a recognized charitable foundation dedicated to transforming the lives of orphaned children. Operating under national and international accreditations.</p>
        <ul class="sf4-social" aria-label="Social media">
          <?php foreach ($social as $key => $url): ?>
            <li><a href="<?= e($url) ?>" target="_blank" rel="noopener" aria-label="<?= e(ucfirst($key)) ?>"><?= $socialIcons[$key] ?? '' ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>

      <nav class="sf4-col" aria-label="Quick links" data-sf-reveal style="--i:2">
        <h3>Quick Links</h3>
        <ul class="sf4-links">
          <?php foreach ($footerQuick as $idx => $item): ?>
            <li style="--n:<?= (int) $idx ?>"><a href="<?= e($item['href']) ?>"<?= $item['newtab'] ? ' target="_blank" rel="noopener"' : '' ?>><?= e($item['label']) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </nav>

      <nav class="sf4-col" aria-label="Get involved" data-sf-reveal style="--i:3">
        <h3>Get Involved</h3>
        <ul class="sf4-links">
          <?php foreach ($footerMore as $idx => $item): ?>
            <li style="--n:<?= (int) $idx ?>"><a href="<?= e($item['href']) ?>"<?= $item['newtab'] ? ' target="_blank" rel="noopener"' : '' ?>><?= e($item['label']) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </nav>

      <div class="sf4-col sf4-contact" data-sf-reveal style="--i:4">
        <h3>Reach Us</h3>
        <ul class="sf4-reach">
          <li>
            <span class="sf4-chip"><svg viewBox="0 0 24 24"><path d="M12 21s7-6.1 7-11a7 7 0 1 0-14 0c0 4.9 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/></svg></span>
            <div><small>Address</small><span><?= e($orgAddress) ?></span></div>
          </li>
          <li>
            <span class="sf4-chip"><svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg></span>
            <div><small>Email</small><a href="mailto:<?= e($orgEmail) ?>"><?= e($orgEmail) ?></a></div>
          </li>
          <li>
            <span class="sf4-chip"><svg viewBox="0 0 24 24"><rect x="7" y="2" width="10" height="20" rx="2.5"/><path d="M11 18h2"/></svg></span>
            <div><small>Mobile</small><a href="<?= e($telHref($orgPhone)) ?>"><?= e($orgPhone) ?></a></div>
          </li>
          <?php if (trim((string) $orgLandline) !== ''): ?>
          <li>
            <span class="sf4-chip"><svg viewBox="0 0 24 24"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.9 2z"/></svg></span>
            <div><small>Landline</small><a href="<?= e($telHref($orgLandline)) ?>"><?= e($orgLandline) ?></a></div>
          </li>
          <?php endif; ?>
        </ul>
      </div>
    </div>

    <div class="sf4-bottom" data-sf-reveal style="--i:5">
      <p class="sf4-line">
        <span class="sf4-copy">&copy; <span class="sf4-year"><?= $year ?></span> Copyrights by <span class="sf4-name"><?= e($appName) ?></span></span>
        <span class="sf4-pipe">|</span>
        <span>Created with <span class="sf4-pulse" aria-hidden="true">❤️</span> by: <a class="sf4-credit" href="https://softextech.com" target="_blank" rel="noopener">Softex Tech</a></span>
      </p>
      <p class="sf4-line sf4-legal">
        <a href="<?= e(base_url('sitemap.xml')) ?>">Sitemap</a>
        <span class="sf4-pipe">|</span>
        <a href="<?= e(base_url('registrations-mou')) ?>">Registrations</a>
        <span class="sf4-pipe">|</span>
        <a href="<?= e(base_url('financial-reports')) ?>">Financial Reports</a>
      </p>
    </div>
  </div>
</footer>
<button class="back-to-top" id="backToTop" type="button" aria-label="Back to top" hidden>
  <svg viewBox="0 0 24 24"><path d="m18 15-6-6-6 6"/></svg>
</button>
