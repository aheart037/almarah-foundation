# Almarah Foundation — v3.1 Changed Files

Base: the `v3` commit in Git (`813ecf3`, tagged `backup/approved-v3`). Extract this ZIP into the project root; folder paths are preserved.
**No database migration is required.** No new Composer dependency. No `.env` change.

## Files

| File | Status | What changed | Why |
|---|---|---|---|
| `app/Services/ReportExportService.php` | NEW | Builds CSV (UTF-8 BOM, spreadsheet-formula protection) and a real `.xlsx` (no zip extension needed) from the same report data the PDF uses. | The Export CSV / Export Excel buttons on every report returned HTTP 500: routes and buttons existed but nothing answered them. |
| `app/Http/Controllers/Admin/ReportExportController.php` | NEW | `csv()` and `xlsx()` actions: same filters, `reports.view` permission, 404 for unknown reports, audit log entry `report.exported` (with format). | Same. Kept separate so the existing `ReportController` is not overwritten. |
| `routes/admin.php` | MODIFIED | The two `/admin/reports/{slug}/csv` and `/xlsx` routes now point to `ReportExportController`. Nothing else changed. | Same. |
| `resources/views/public/home.php` | MODIFIED | Loads `home-v31.css`; includes the stats strip right after the slider and the closing hero right before the footer. | Requested homepage sections; existing sections untouched. |
| `resources/views/public/pages/home-stats.php` | NEW | Stats strip: 275 Children Supported, 13 Homes for Children, 21,000+ Ration Packs Distributed, 1,500+ Meals Provided Daily (the figures published on almarah.org). Animated count-up, reduced-motion safe. Each value can be overridden via `site_settings` keys `home.stat_children`, `home.stat_homes`, `home.stat_rations`, `home.stat_meals` (whole numbers). | Requested stats after the slider. |
| `resources/views/public/pages/home-cta-hero.php` | NEW | Closing hero: "Together, We Can Create a Lasting Impact", real Almarah photo, Donate Now → `/donate`, Explore Our Projects → `/projects`. | Requested hero before the footer. |
| `assets/css/home-v31.css` | NEW | Styles for both homepage sections (responsive, brand colours). | Keeps changes out of the bundled `site.css`. |
| `resources/views/public/donate/index.php` | MODIFIED | Donate page redesigned: new banner, cleaner numbered steps, 4-up amount tiles, compact pill-style currency selector with SVG flags (name, code, symbol per option). **All element IDs, names and data attributes used by `donate.js` are unchanged**; PHP logic, gateways, Geo-IP and rates untouched. The `donation-extra-fields.php` partial is now loaded only if the file exists. | Requested redesign. |
| `assets/css/donate-v31.css` | NEW | Styles for the redesigned donate page. | Same. |
| `assets/images/flags/{pk,us,gb,eu,ae,sa,ca,au}.svg` | NEW | SVG flags for the 8 supported currencies (from `flag-icons`, MIT; licence included). | SVG flags instead of emoji. |
| `assets/images/flags/LICENSE-flag-icons.txt` | NEW | MIT licence for the flags. | Licence requirement. |
| `tests/reports_export_v31_test.php` | NEW | Tests for CSV/XLSX output and formula protection. | Regression coverage. |
| `VERSION` | MODIFIED | `V3.1`. | Versioning. |

## Deployment
1. Back up the current site, then extract the ZIP over the project root.
2. Nothing to run. Clear any opcache/page cache if your host uses one.
3. Check: open an admin report and click **PDF**, **CSV**, **Excel**; open `/`, `/donate`.

## Known issues found but NOT changed (read this)
1. **The Git `v3` commit is not the full approved V3.** `V3-RELEASE-MANIFEST.sha256` lists 608 files: 549 match, 34 differ (33 are byte-identical to the V2 commit, e.g. `ReportController.php`, `ReportService.php`, `FinanceService.php` callers, `Response.php`) and 25 are missing from Git (e.g. `ReportSpreadsheetService.php`, `DonationReviewController.php`, `DonationDocumentService.php`, `partials/donation-extra-fields.php`, migrations `0012`, `0013`). Every file in this ZIP was chosen so that, before my edit, it matched your V3 manifest (`routes/admin.php`, `home.php`, `donate/index.php`) or is new. Please commit the complete V3 file set to Git.
2. **On a database built only from the Git migrations, `/admin` (dashboard) and `/admin/finance` return 500**: they use `donations.verification_status`, added by missing migration `0012`. If your live database already has `0012`/`0013` this does not occur.
3. **Stripe does not exist in this codebase.** Current behaviour, preserved as-is: PKR → online gateways (Meezan / UBL-Etisalat); other currencies → bank transfer. Adding Stripe is a new integration that needs your Stripe keys and webhooks.
4. Not covered in this release: PDF/eBook viewer changes, Grant / Grant-allocation reports (no grant tables exist in this schema), live payment-gateway tests.
