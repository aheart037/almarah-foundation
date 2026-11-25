# Footer v4.0

Visual-only change. Same palette (wine #6B0F35, rose #A92D63, gold #F2C200) and fonts (Manrope, Open Sans).

Files (copy over the repo, same paths):
- `assets/css/footer-v40.css`  new, all styles scoped to `.site-footer.sf4`
- `resources/views/partials/site-footer.php`  new markup
- `resources/views/layouts/public.php`  one line added: links `footer-v40.css` after `header-v38.css`

What it adds: curved wave edge and gold shimmer line, wine gradient with a star-lattice pattern, glows, a faint heart and rings, a frosted subscribe + Donate Now card, brand / Quick Links / Get Involved / Reach Us columns with icon chips and gold markers, a bottom bar, and a gold back-to-top button. Responsive at 1440, 820 and 390 px with no horizontal scroll.

Kept as before: newsletter form (POST forms/newsletter, data-ajax-form, CSRF, honeypot, #footerEmail, .form-feedback), #backToTop, CMS menus (footer_quick / footer_more), address / email / mobile / landline, social links, copyright and Softex credit, Sitemap / Registrations / Financial Reports links. The landline row is hidden if the setting is empty.

Rollback: remove the footer-v40.css link from layouts/public.php and restore the old site-footer.php (`git checkout -- resources/views/partials/site-footer.php`).

## Update: animation + mobile
- New file `assets/js/footer-v40.js` (loaded by layouts/public.php with `defer`). Optional: without it the footer still shows fully, just without scroll-reveal/spotlight/ripple/progress ring.
- Effects: drifting double wave, rising gold sparkles, floating glows, scroll-reveal with staggered links/icons, pointer spotlight on the subscribe card, pulsing Subscribe button, beating heart, button ripple, back-to-top scroll-progress ring.
- Mobile: Quick Links / Get Involved side by side, 44px+ tap targets, full-width buttons, fewer particles, no hover effects on touch, room above bottom for back-to-top.
- `prefers-reduced-motion` turns all motion off.
