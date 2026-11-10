# Almarah Foundation V3.3 — Footer redesign

A visual-only release. No routes, controllers, database, payment or admin code was touched.
Built only from the existing brand palette (wine `#6B0F35`, rose `#A92D63`, gold `#F2C200`)
and existing fonts (Manrope + Open Sans).

## What changed
* Deep wine aurora background with a shimmering gold top line, fading dot-grid and ripple rings
  (same visual language as the V3.2 page heroes)
* Frosted-glass newsletter band at the top (original "Recent Updates" copy): pill-shaped email field, gold Subscribe button, Donate Now button
* Brand column: footer logo, about text, social buttons that turn gold on hover
* "Quick Links" and "Get Involved" lists (still driven by the CMS menus `footer_quick` / `footer_more`) with an animated gold marker
* "Reach Us" column with icon chips for address, email, mobile and landline (still from Settings)
* Bottom bar keeps the copyright, "Created with ❤️ by Softex Tech" credit and Sitemap / Registrations / Financial Reports links
* Gold back-to-top button
* Fully responsive (checked at 1440, 820 and 390 px, no horizontal scroll)

## Kept exactly as before
Newsletter form (`POST /newsletter`, `data-ajax-form`, CSRF field, honeypot `website`, `#footerEmail`,
`.form-feedback`), `#backToTop` button, all `$orgAddress/$orgEmail/$orgPhone/$orgLandline`, `$social`,
`$footerQuick`, `$footerMore` variables from `layouts/public.php`.

## Files
| File | Change |
|---|---|
| `assets/css/footer-v33.css` | **New** – the whole footer design, scoped to `.site-footer.sf3` |
| `resources/views/partials/site-footer.php` | Rewritten markup |
| `resources/views/layouts/public.php` | Links `footer-v33.css` after `hero-v32.css` |

The fundraiser dashboard footer (`partials/footer.php`, used by `layouts/dashboard.php`) is a separate footer and was not changed.

## Editing copy
Heading / sub-text: top of `site-footer.php` (`sf3-join` block).

## Rollback
Remove the `footer-v33.css` link from `layouts/public.php` and restore the previous `site-footer.php`
(`git checkout main -- resources/views/partials/site-footer.php`).
