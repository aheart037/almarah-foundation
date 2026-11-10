<?php
/**
 * Site footer — "Editorial Light" redesign.
 *
 * Concept:  warm paper ground, index-style numbered nav rows,
 *           oversized clipped wordmark as the closing gesture.
 * Preserves every route, guard, social URL and config lookup.
 * Styling is inline + fully scoped to .site-footer (no new deps).
 */

use App\Core\Config;

$org = (array) Config::get('app.org', []);
$appName = (string) Config::get('app.name', 'Almarah Foundation');
$year = date('Y');
$footerLogoUrl = site_brand_asset('site.footer_logo');
?>

<style>
/* ============================================================
   SITE FOOTER — editorial light
   ============================================================ */
.site-footer {
  --paper: #f7f2ea;
  --ink: #1a1410;
  --ink-soft: rgba(26, 20, 16, .68);
  --line: rgba(26, 20, 16, .12);
  --line-strong: rgba(26, 20, 16, .24);
  --accent: #6b0f35;
  --gold: #b8860b;

  position: relative;
  isolation: isolate;
  overflow: hidden;
  background: var(--paper);
  color: var(--ink);
  font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
}

/* top accent stitch */
.site-footer::before {
  content: "";
  position: absolute; inset: 0 0 auto 0;
  height: 3px;
  background: linear-gradient(90deg,
    transparent 0%, var(--gold) 25%, var(--accent) 50%, var(--gold) 75%, transparent 100%);
  z-index: 3;
}
/* faint paper dot texture */
.site-footer::after {
  content: "";
  position: absolute; inset: 0;
  background-image: radial-gradient(rgba(26, 20, 16, .055) 1px, transparent 1px);
  background-size: 24px 24px;
  opacity: .5;
  pointer-events: none;
  -webkit-mask-image: linear-gradient(180deg, #000 0%, transparent 55%);
          mask-image: linear-gradient(180deg, #000 0%, transparent 55%);
  z-index: 0;
}
.site-footer > * { position: relative; z-index: 1; }

.site-footer .sf-sr {
  position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px;
  overflow: hidden; clip: rect(0,0,0,0); white-space: nowrap; border: 0;
}

/* shared type */
.site-footer .sf-eyebrow {
  display: inline-flex; align-items: center; gap: .55rem;
  margin: 0;
  font-size: .72rem; font-weight: 600;
  letter-spacing: .24em; text-transform: uppercase;
  color: var(--ink-soft);
}
.site-footer .sf-mark { color: var(--accent); font-size: .85rem; }

/* ---------- HERO ---------- */
.site-footer .sf-hero {
  display: grid;
  grid-template-columns: 1.4fr 1fr;
  gap: 4rem;
  align-items: end;
  padding: 5rem 0 4rem;
  border-bottom: 1px solid var(--line);
}
.site-footer .sf-display {
  margin: 1.2rem 0 0;
  font-family: 'Iowan Old Style', 'Palatino Linotype', 'Charter', Georgia, 'Times New Roman', serif;
  font-size: clamp(2.1rem, 4.2vw, 3.9rem);
  font-weight: 500;
  line-height: 1.05;
  letter-spacing: -.025em;
  color: var(--ink);
  max-width: 20ch;
}
.site-footer .sf-display em { font-style: italic; color: var(--accent); font-weight: 500; }

.site-footer .sf-sub {
  margin: 1.5rem 0 0;
  max-width: 46ch;
  font-size: .96rem; line-height: 1.7;
  color: var(--ink-soft);
}

.site-footer .sf-hero__aside { display: grid; gap: 2.2rem; padding-bottom: .5rem; }

/* newsletter */
.site-footer .sf-newsletter { display: grid; gap: .9rem; }
.site-footer .sf-newsletter > label { display: block; }
.site-footer .sf-newsletter__field {
  display: flex; align-items: center; gap: .6rem;
  border-bottom: 1px solid var(--line-strong);
  padding: .4rem 0;
  transition: border-color .3s ease;
}
.site-footer .sf-newsletter__field:focus-within { border-color: var(--accent); }
.site-footer .sf-newsletter input {
  flex: 1; min-width: 0; border: 0; background: transparent;
  font: inherit; font-size: 1rem; color: var(--ink); padding: .5rem 0;
}
.site-footer .sf-newsletter input::placeholder { color: rgba(26, 20, 16, .4); }
.site-footer .sf-newsletter input:focus { outline: none; }
.site-footer .sf-newsletter button {
  flex-shrink: 0; width: 36px; height: 36px;
  display: grid; place-items: center;
  border: 0; border-radius: 50%;
  background: var(--ink); color: var(--paper);
  cursor: pointer;
  transition: background .3s ease, transform .3s ease;
}
.site-footer .sf-newsletter button:hover { background: var(--accent); transform: translateX(2px); }
.site-footer .sf-newsletter button svg { width: 15px; height: 15px; }
.site-footer .sf-newsletter__note {
  margin: 0; font-size: .74rem; letter-spacing: .04em;
  color: rgba(26, 20, 16, .45);
}

/* socials */
.site-footer .sf-follow { display: grid; gap: .9rem; }
.site-footer .sf-socials {
  display: flex; flex-wrap: wrap; gap: .5rem;
  list-style: none; padding: 0; margin: 0;
}
.site-footer .sf-socials a {
  display: grid; place-items: center;
  width: 42px; height: 42px;
  border-radius: 50%;
  border: 1px solid var(--line-strong);
  color: var(--ink);
  transition:
    background .28s cubic-bezier(.2, .8, .2, 1),
    color .28s ease,
    border-color .28s ease,
    transform .28s cubic-bezier(.2, .8, .2, 1);
}
.site-footer .sf-socials a:hover {
  background: var(--ink); color: var(--paper); border-color: var(--ink);
  transform: translateY(-3px);
}
.site-footer .sf-socials svg { width: 16px; height: 16px; }

/* ---------- INDEX ---------- */
.site-footer .sf-index { display: grid; }
.site-footer .sf-row {
  position: relative;
  display: grid;
  grid-template-columns: 56px 200px 1fr 40px;
  align-items: center;
  gap: 1.5rem;
  padding: 1.7rem 0;
  border-bottom: 1px solid var(--line);
  transition: background .3s ease;
}
.site-footer .sf-row::before {
  content: "";
  position: absolute; inset: 0;
  background: linear-gradient(90deg, transparent 0%, rgba(107, 15, 53, .045) 50%, transparent 100%);
  opacity: 0;
  transition: opacity .35s ease;
  pointer-events: none;
}
.site-footer .sf-row:hover::before { opacity: 1; }
.site-footer .sf-row:last-child { border-bottom: 0; }

.site-footer .sf-row__num {
  position: relative; z-index: 1;
  font-family: ui-monospace, 'SF Mono', Menlo, Consolas, monospace;
  font-size: .78rem; letter-spacing: .06em;
  color: var(--gold);
  transition: color .3s ease;
}
.site-footer .sf-row:hover .sf-row__num { color: var(--accent); }

.site-footer .sf-row__title {
  position: relative; z-index: 1;
  margin: 0;
  font-family: 'Iowan Old Style', 'Palatino Linotype', 'Charter', Georgia, serif;
  font-size: 1.5rem; font-weight: 500; letter-spacing: -.01em;
  color: var(--ink);
}

.site-footer .sf-row__links {
  position: relative; z-index: 1;
  list-style: none; padding: 0; margin: 0;
  display: flex; flex-wrap: wrap;
  gap: .35rem 1.8rem;
}
.site-footer .sf-row__links a {
  position: relative; display: inline-block;
  padding: .25rem 0;
  font-size: .94rem; color: var(--ink-soft);
  text-decoration: none;
  transition: color .22s ease;
}
.site-footer .sf-row__links a::after {
  content: "";
  position: absolute; left: 0; right: 0; bottom: 0; height: 1px;
  background: var(--accent);
  transform: scaleX(0); transform-origin: right;
  transition: transform .38s cubic-bezier(.2, .8, .2, 1);
}
.site-footer .sf-row__links a:hover { color: var(--accent); }
.site-footer .sf-row__links a:hover::after { transform: scaleX(1); transform-origin: left; }

.site-footer .sf-row__contact {
  position: relative; z-index: 1;
  list-style: none; padding: 0; margin: 0;
  display: flex; flex-wrap: wrap;
  gap: .5rem 1.8rem;
}
.site-footer .sf-row__contact li {
  display: inline-flex; align-items: center; gap: .55rem;
  font-size: .92rem; color: var(--ink-soft);
  min-width: 0;
}
.site-footer .sf-row__contact li > span {
  min-width: 0; overflow-wrap: anywhere;
}
.site-footer .sf-row__contact svg {
  width: 15px; height: 15px; flex-shrink: 0;
  color: var(--gold);
}
.site-footer .sf-row__contact a {
  color: inherit; text-decoration: none;
  transition: color .22s ease;
}
.site-footer .sf-row__contact a:hover { color: var(--accent); }

.site-footer .sf-row__action {
  position: relative; z-index: 1;
  display: grid; place-items: center;
  width: 38px; height: 38px;
  border-radius: 50%;
  border: 1px solid var(--line-strong);
  color: var(--ink);
  justify-self: end;
  transition:
    background .3s ease, color .3s ease,
    border-color .3s ease, transform .3s ease;
}
.site-footer .sf-row__action svg { width: 15px; height: 15px; }
.site-footer .sf-row:hover .sf-row__action {
  background: var(--accent); color: var(--paper);
  border-color: var(--accent);
  transform: translateX(4px);
}

/* ---------- CODA (giant clipped wordmark) ---------- */
.site-footer .sf-coda {
  position: relative;
  height: clamp(140px, 18vw, 240px);
  overflow: hidden;
  margin-top: 3.5rem;
  border-top: 1px solid var(--line);
}
.site-footer .sf-coda__mark {
  position: absolute;
  bottom: -.22em; left: 50%;
  transform: translateX(-50%);
  font-family: 'Iowan Old Style', 'Palatino Linotype', 'Charter', Georgia, serif;
  font-size: clamp(9rem, 26vw, 26rem);
  font-weight: 800; letter-spacing: -.045em; line-height: .78;
  white-space: nowrap;
  user-select: none; pointer-events: none;
  background: linear-gradient(180deg,
    rgba(107, 15, 53, .42) 0%,
    rgba(107, 15, 53, .06) 85%);
  -webkit-background-clip: text;
          background-clip: text;
  color: transparent;
}

/* ---------- BOTTOM ---------- */
.site-footer .sf-bottom {
  display: flex; align-items: center; justify-content: space-between;
  flex-wrap: wrap; gap: 1rem;
  padding: 1.5rem 0 2rem;
  border-top: 1px solid var(--line);
  font-size: .82rem; color: var(--ink-soft);
}
.site-footer .sf-legal {
  list-style: none; padding: 0; margin: 0;
  display: flex; flex-wrap: wrap;
  gap: .3rem 1.4rem;
}
.site-footer .sf-legal a {
  color: inherit; text-decoration: none;
  transition: color .22s ease;
}
.site-footer .sf-legal a:hover { color: var(--accent); }

/* focus */
.site-footer a:focus-visible,
.site-footer button:focus-visible,
.site-footer input:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 3px;
  border-radius: 3px;
}

/* ---------- responsive ---------- */
@media (max-width: 960px) {
  .site-footer .sf-hero {
    grid-template-columns: 1fr; gap: 3rem;
    padding: 4rem 0 3rem;
  }
  .site-footer .sf-hero__aside { padding-bottom: 0; }
  .site-footer .sf-row {
    grid-template-columns: 44px 1fr;
    grid-template-rows: auto auto;
    gap: .8rem 1rem;
    padding: 1.6rem 0;
  }
  .site-footer .sf-row__num { grid-row: 1; grid-column: 1; align-self: center; }
  .site-footer .sf-row__title { grid-row: 1; grid-column: 2; font-size: 1.25rem; }
  .site-footer .sf-row__links,
  .site-footer .sf-row__contact {
    grid-row: 2; grid-column: 1 / -1;
    padding-left: 56px;
  }
  .site-footer .sf-row__action { display: none; }
  .site-footer .sf-coda { height: clamp(110px, 24vw, 180px); }
}

@media (max-width: 620px) {
  .site-footer .sf-hero { padding: 3rem 0 2.5rem; }
  .site-footer .sf-sub { font-size: .92rem; }
  .site-footer .sf-row__links,
  .site-footer .sf-row__contact { padding-left: 0; }
  .site-footer .sf-bottom {
    flex-direction: column; align-items: flex-start; gap: 1rem;
  }
  .site-footer .sf-coda { height: clamp(100px, 30vw, 140px); }
}

@media (prefers-reduced-motion: reduce) {
  .site-footer *,
  .site-footer *::before,
  .site-footer *::after { transition-duration: .01ms !important; }
}
</style>

<footer class="site-footer" id="site-footer">
  <div class="wrap">

    <!-- ================= HERO ================= -->
    <div class="sf-hero">
      <div class="sf-hero__main">
        <p class="sf-eyebrow">
          <span class="sf-mark" aria-hidden="true">✦</span>
          <?= e($appName) ?> · Est. <?= e((string) ($org['founded'] ?? '2021')) ?>
        </p>
        <h2 class="sf-display">
          A safe home, a full plate, <em>a future worth dreaming.</em>
        </h2>
        <p class="sf-sub">
          That's what your support builds every day. Get the monthly dispatch from the field — stories, updates, and the moments you make possible.
        </p>
      </div>

      <div class="sf-hero__aside">
        <form class="sf-newsletter" action="#" method="post" data-newsletter-form>
          <label class="sf-eyebrow" for="sf-email">Join the newsletter</label>
          <div class="sf-newsletter__field">
            <input id="sf-email" type="email" name="email" autocomplete="email"
                   inputmode="email" placeholder="you@example.com" required>
            <button type="submit" aria-label="Subscribe">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                   stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                <path d="M5 12h14"/><path d="M13 6l6 6-6 6"/>
              </svg>
            </button>
          </div>
          <p class="sf-newsletter__note">Monthly. No spam. Unsubscribe anytime.</p>
        </form>

        <div class="sf-follow">
          <span class="sf-eyebrow">Or follow along</span>
          <ul class="sf-socials">
            <li><a href="https://www.facebook.com/almarahfoundation" target="_blank" rel="noopener" aria-label="Facebook"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M22 12a10 10 0 1 0-11.6 9.9v-7H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.5h-1.3c-1.2 0-1.6.8-1.6 1.6V12h2.8l-.4 2.9h-2.4v7A10 10 0 0 0 22 12z"/></svg></a></li>
            <li><a href="https://www.instagram.com/almarahfoundation" target="_blank" rel="noopener" aria-label="Instagram"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg></a></li>
            <li><a href="https://www.youtube.com/@almarahfoundation" target="_blank" rel="noopener" aria-label="YouTube"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg></a></li>
            <li><a href="https://www.linkedin.com/company/almarahfoundation/home/" target="_blank" rel="noopener" aria-label="LinkedIn"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.063 2.063 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg></a></li>
            <li><a href="https://www.tiktok.com/@almarahfoundation1" target="_blank" rel="noopener" aria-label="TikTok"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1-.06z"/></svg></a></li>
            <li><a href="https://wa.me/923268050116?text=Assalam%20o%20Alaikum%2C%20I%20would%20like%20to%20get%20in%20touch%20with%20Almarah%20Foundation." target="_blank" rel="noopener" aria-label="WhatsApp"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M12 2a10 10 0 0 0-8.6 15L2 22l5.2-1.4A10 10 0 1 0 12 2zm5.8 14.2c-.2.7-1.4 1.3-2 1.4-.5.1-1.1.1-1.8-.1-.4-.1-1-.3-1.7-.6a11 11 0 0 1-4.2-3.7c-.3-.5-.7-1.2-.7-2s.3-1.4.5-1.6c.2-.3.5-.3.6-.3h.5c.2 0 .4 0 .5.4l.7 1.6c.1.2 0 .4-.1.5l-.3.4c-.1.1-.2.3-.1.5.2.5.6 1 1 1.4.5.5 1.1.9 1.7 1.2.2.1.4.1.5 0l.6-.7c.2-.2.3-.2.5-.1l1.6.7c.2.1.4.2.4.3.1.2.1.7-.1 1.3z"/></svg></a></li>
          </ul>
        </div>
      </div>
    </div>

    <!-- ================= INDEX ================= -->
    <nav class="sf-index" aria-label="Footer navigation">
      <div class="sf-row">
        <span class="sf-row__num">01</span>
        <h3 class="sf-row__title">Fundraise</h3>
        <ul class="sf-row__links">
          <?php if (can_fundraiser_capability('manage_pages')): ?>
            <li><a href="<?= e(base_url('register')) ?>">Start a Fundraiser</a></li>
          <?php endif; ?>
          <li><a href="<?= e(base_url('fundraisers')) ?>">Support a Fundraiser</a></li>
          <li><a href="<?= e(base_url('teams')) ?>">Top Teams</a></li>
          <li><a href="<?= e(base_url('campaigns')) ?>">Appeal Campaigns</a></li>
          <li><a href="<?= e(base_url('login')) ?>">Fundraiser Login</a></li>
        </ul>
        <span class="sf-row__action" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M13 6l6 6-6 6"/></svg>
        </span>
      </div>

      <div class="sf-row">
        <span class="sf-row__num">02</span>
        <h3 class="sf-row__title">Our Work</h3>
        <ul class="sf-row__links">
          <li><a href="<?= e(base_url('campaigns')) ?>">Parent the Orphan</a></li>
          <li><a href="<?= e(base_url('campaigns')) ?>">Feed the Hungry</a></li>
          <li><a href="<?= e(base_url('campaigns')) ?>">Educate Pakistan</a></li>
          <li><a href="<?= e(base_url('about-us')) ?>">Izzat ki Roti</a></li>
          <li><a href="<?= e(base_url('about-us')) ?>">Compassionate Haven</a></li>
        </ul>
        <span class="sf-row__action" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M13 6l6 6-6 6"/></svg>
        </span>
      </div>

      <div class="sf-row">
        <span class="sf-row__num">03</span>
        <h3 class="sf-row__title">Get in Touch</h3>
        <ul class="sf-row__contact">
          <li>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            <span><?= e((string) ($org['address'] ?? '')) ?></span>
          </li>
          <li>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M2 7l10 6 10-6"/></svg>
            <a href="mailto:<?= e((string) ($org['email'] ?? '')) ?>"><?= e((string) ($org['email'] ?? '')) ?></a>
          </li>
          <li>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/></svg>
            <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', (string) ($org['phone'] ?? '')) ?? '') ?>"><?= e((string) ($org['phone'] ?? '')) ?></a>
          </li>
          <li>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M3 9.5L12 3l9 6.5V20a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/></svg>
            <a href="<?= e((string) ($org['website'] ?? 'https://www.almarah.org')) ?>" target="_blank" rel="noopener">almarah.org</a>
          </li>
        </ul>
        <span class="sf-row__action" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M13 6l6 6-6 6"/></svg>
        </span>
      </div>
    </nav>

  </div>

  <!-- ================= CODA ================= -->
  <div class="sf-coda" aria-hidden="true">
    <span class="sf-coda__mark">ALMARAH</span>
  </div>

  <!-- ================= BOTTOM ================= -->
  <div class="wrap">
    <div class="sf-bottom">
      <span class="sf-copy">&copy; <?= e($year) ?> <?= e($appName) ?>. All Rights Reserved.</span>
      <ul class="sf-legal">
        <li><a href="<?= e(base_url('faq')) ?>">FAQs</a></li>
        <li><a href="<?= e(base_url('privacy-policy')) ?>">Privacy Policy</a></li>
        <li><a href="<?= e(base_url('terms')) ?>">Terms</a></li>
        <li><a href="<?= e(base_url('refund-policy')) ?>">Refund Policy</a></li>
        <li><a href="<?= e(base_url('support')) ?>">Support</a></li>
      </ul>
    </div>
  </div>
</footer>