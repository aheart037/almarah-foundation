<?php
/**
 * Site footer — v3.3 redesign.
 *
 * Same data and hooks as before (nav items from the CMS, org contact settings,
 * social links, newsletter form posting to /newsletter with data-ajax-form,
 * #backToTop button) — only the markup and look changed. Styles live in
 * assets/css/footer-v33.css and use the existing brand palette and fonts.
 */
$year = date('Y');
$socialIcons = [
    'facebook'  => '<svg viewBox="0 0 24 24"><path d="M14 8h3V4h-3a4 4 0 0 0-4 4v2H8v4h2v8h4v-8h3l1-4h-4V8a1 1 0 0 1 0 0z"/></svg>',
    'instagram' => '<svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor"/></svg>',
    'youtube'   => '<svg viewBox="0 0 24 24"><path d="M22 8s-.2-1.6-.9-2.3c-.8-.9-1.8-.9-2.2-1C15.8 4.5 12 4.5 12 4.5s-3.8 0-6.9.2c-.4.1-1.4.1-2.2 1C2.2 6.4 2 8 2 8s-.2 1.9-.2 3.8v1.7C1.8 15.4 2 17.3 2 17.3s.2 1.6.9 2.3c.8.9 1.9.8 2.4.9 1.7.2 6.7.2 6.7.2s3.8 0 6.9-.2c.4-.1 1.4-.1 2.2-1 .7-.7.9-2.3.9-2.3s.2-1.9.2-3.8v-1.7C22.2 9.9 22 8 22 8z"/><path d="m10 15 5-3-5-3z" fill="currentColor" stroke="none"/></svg>',
    'linkedin'  => '<svg viewBox="0 0 24 24"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-4 0v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg>',
    'tiktok'    => '<svg viewBox="0 0 24 24"><path d="M15 3v9.5a3.5 3.5 0 1 1-3.5-3.5"/><path d="M15 3c.5 2.6 2.4 4.3 5 4.5"/></svg>',
];
$phoneTel = preg_replace('/[^+\d]/', '', (string) $orgPhone);
$landTel  = preg_replace('/[^+\d]/', '', (string) $orgLandline);
?>
<footer class="site-footer sf3" id="site-footer">
  <span class="sf3-ring sf3-ring-a" aria-hidden="true"></span>
  <span class="sf3-ring sf3-ring-b" aria-hidden="true"></span>

  <div class="container sf3-inner">

    <!-- ============ Newsletter band ============ -->
    <section class="sf3-join" aria-labelledby="sf3JoinTitle">
      <div class="sf3-join-copy">
        <h3 id="sf3JoinTitle">Recent Updates</h3>
        <p>Subscribe to receive stories of impact, project updates and ways to help.</p>
      </div>
      <div class="sf3-join-form">
        <form class="sf3-form" method="post" action="<?= e(base_url('newsletter')) ?>" data-ajax-form>
          <?= csrf_field() ?>
          <input type="hidden" name="source" value="footer">
          <input type="text" name="website" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">
          <label class="sr-only" for="footerEmail">Email address</label>
          <div class="sf3-form-row">
            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
            <input id="footerEmail" type="email" name="email" placeholder="Your email address" required autocomplete="email" inputmode="email">
            <button type="submit" class="sf3-btn">Subscribe
              <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
            </button>
          </div>
          <p class="form-feedback" aria-live="polite"></p>
        </form>
        <a class="sf3-donate" href="<?= e(base_url('donate')) ?>">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s-7-4.6-9.3-9.1C1.2 8.9 2.8 5 6.4 5c2 0 3.4 1.1 4.2 2.4h2.8C14.2 6.1 15.6 5 17.6 5c3.6 0 5.2 3.9 3.7 6.9C19 16.4 12 21 12 21z"/></svg>
          Donate Now
        </a>
      </div>
    </section>

    <!-- ============ Main columns ============ -->
    <div class="sf3-grid">
      <div class="sf3-col sf3-about">
        <a href="<?= e(base_url()) ?>/" class="sf3-brand"><img src="<?= e($footerLogoUrl) ?>" alt="<?= e($appName) ?>" width="223" height="42" loading="lazy" decoding="async"></a>
        <p>Almarah Foundation is a registered non-governmental organization (NGO) and a recognized charitable foundation dedicated to transforming the lives of orphaned children. Operating under national and international accreditations.</p>

        <ul class="sf3-social" aria-label="Social media">
          <?php foreach ($social as $key => $url): ?>
            <li><a href="<?= e($url) ?>" target="_blank" rel="noopener" aria-label="<?= e(ucfirst($key)) ?>"><?= $socialIcons[$key] ?? '' ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>

      <nav class="sf3-col" aria-label="Quick links">
        <h3 class="sf3-h">Quick Links</h3>
        <ul class="sf3-links">
          <?php foreach ($footerQuick as $item): ?>
            <li><a href="<?= e($item['href']) ?>"<?= $item['newtab'] ? ' target="_blank" rel="noopener"' : '' ?>><?= e($item['label']) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </nav>

      <nav class="sf3-col" aria-label="Get involved">
        <h3 class="sf3-h">Get Involved</h3>
        <ul class="sf3-links">
          <?php foreach ($footerMore as $item): ?>
            <li><a href="<?= e($item['href']) ?>"<?= $item['newtab'] ? ' target="_blank" rel="noopener"' : '' ?>><?= e($item['label']) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </nav>

      <div class="sf3-col">
        <h3 class="sf3-h">Reach Us</h3>
        <ul class="sf3-contact">
          <li>
            <span class="sf3-ico"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s7-6.1 7-11a7 7 0 1 0-14 0c0 4.9 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/></svg></span>
            <div><small>Address</small><span><?= e($orgAddress) ?></span></div>
          </li>
          <li>
            <span class="sf3-ico"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg></span>
            <div><small>Email</small><a href="mailto:<?= e($orgEmail) ?>"><?= e($orgEmail) ?></a></div>
          </li>
          <li>
            <span class="sf3-ico"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.9 2z"/></svg></span>
            <div><small>Call</small>
              <a href="tel:<?= e($phoneTel) ?>"><?= e($orgPhone) ?></a>
              <a href="tel:<?= e($landTel) ?>"><?= e($orgLandline) ?></a>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>


  <!-- ============ Bottom bar ============ -->
  <div class="container">
    <div class="sf3-bottom">
      <p class="sf-footer-line">
        <span class="sf-copy">&copy; <span class="sf-year"><?= $year ?></span> Copyrights by <span class="sf-brand"><?= e($appName) ?></span></span>
        <span class="sf-pipe">|</span>
        <span>Created with <span class="sf-heart" aria-hidden="true">❤️</span> by: <a class="sf-link" href="https://softextech.com" target="_blank" rel="noopener">Softex Tech</a></span>
      </p>
      <p class="sf-footer-line sf-legal">
        <a class="sf-link-muted" href="<?= e(base_url('sitemap.xml')) ?>">Sitemap</a>
        <span class="sf-pipe">|</span>
        <a class="sf-link-muted" href="<?= e(base_url('registrations-mou')) ?>">Registrations</a>
        <span class="sf-pipe">|</span>
        <a class="sf-link-muted" href="<?= e(base_url('financial-reports')) ?>">Financial Reports</a>
      </p>
    </div>
  </div>
</footer>
<button class="back-to-top" id="backToTop" type="button" aria-label="Back to top" hidden>
  <svg viewBox="0 0 24 24"><path d="m18 15-6-6-6 6"/></svg>
</button>
