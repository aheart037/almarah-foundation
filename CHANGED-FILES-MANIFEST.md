# Almarah Foundation — v3.1 Changed Files

Base: the `v3` commit in Git (`813ecf3`, tagged `backup/approved-v3`). Extract this ZIP into the project root; folder paths are preserved.
**ONE database migration IS required: `database/migrations/0018_v31_donation_review_repair.sql`** (see "Database migration" below). No new Composer dependency. No `.env` change.

## Files

| File | Status | What changed | Why |
|---|---|---|---|
| `app/Services/ReportExportService.php` | NEW | Builds CSV (UTF-8 BOM, spreadsheet-formula protection) and a real `.xlsx` (no zip extension needed) from the same report data the PDF uses. | The Export CSV / Export Excel buttons on every report returned HTTP 500: routes and buttons existed but nothing answered them. |
| `app/Http/Controllers/Admin/ReportExportController.php` | NEW | `csv()` and `xlsx()` actions: same filters, `reports.view` permission, 404 for unknown reports, audit log entry `report.exported` (with format). | Same. Kept separate so the existing `ReportController` is not overwritten. |
| `routes/admin.php` | MODIFIED | The two `/admin/reports/{slug}/csv` and `/xlsx` routes use `ReportController::csv/xlsx` if that class already has them (your full V3), and fall back to `ReportExportController` otherwise. Nothing else changed. | Same; never overrides export code you already have. |
| `resources/views/public/home.php` | MODIFIED | Loads `home-v31.css`; includes the stats strip right after the slider and the closing hero right before the footer. | Requested homepage sections; existing sections untouched. |
| `resources/views/public/pages/home-stats.php` | NEW | Stats strip: 275 Children Supported, 13 Homes for Children, 21,000+ Ration Packs Distributed, 1,500+ Meals Provided Daily (the figures published on almarah.org). Animated count-up, reduced-motion safe. Each value can be overridden via `site_settings` keys `home.stat_children`, `home.stat_homes`, `home.stat_rations`, `home.stat_meals` (whole numbers). | Requested stats after the slider. |
| `resources/views/public/pages/home-cta-hero.php` | NEW | Closing hero: "Together, We Can Create a Lasting Impact", real Almarah photo, Donate Now → `/donate`, Explore Our Projects → `/projects`. | Requested hero before the footer. |
| `assets/css/home-v31.css` | NEW | Styles for both homepage sections (responsive, brand colours). | Keeps changes out of the bundled `site.css`. |
| `resources/views/public/donate/index.php` | MODIFIED | Donate page redesigned: new banner, cleaner numbered steps, 4-up amount tiles, compact pill-style currency selector with SVG flags (name, code, symbol per option). **All element IDs, names and data attributes used by `donate.js` are unchanged**; PHP logic, gateways, Geo-IP and rates untouched. The `donation-extra-fields.php` partial is now loaded only if the file exists. | Requested redesign. |
| `assets/css/donate-v31.css` | NEW | Styles for the redesigned donate page. | Same. |
| `assets/images/flags/{pk,us,gb,eu,ae,sa,ca,au}.svg` | NEW | SVG flags for the 8 supported currencies (from `flag-icons`, MIT; licence included). | SVG flags instead of emoji. |
| `assets/images/flags/LICENSE-flag-icons.txt` | NEW | MIT licence for the flags. | Licence requirement. |
| `tests/reports_export_v31_test.php` | NEW | Tests for CSV/XLSX output and formula protection. | Regression coverage. |
| `index.php` | MODIFIED | The HTTP 500 handler now creates a short **Error ID**, writes it to the log together with the URL, and shows it on the error page. A signed-in administrator also sees the real cause (exception, message, file:line); visitors still see only the generic message. A database error on a site that has an administrator but no `storage/app/installed.lock` file is no longer redirected to `/setup`; the real error is shown instead (a genuinely uninstalled site still goes to `/setup`). | A live 500 gave no clue what failed, and a missing lock file turned report errors into a confusing redirect to the setup page. Now the exact cause is on screen and in `storage/logs/app-YYYY-MM-DD.log`. |
| `assets/css/admin-v31.css` | NEW | Fixed icon sizes in the admin sidebar and report pages; the Reports group is now a compact, scrollable list (max about 360px tall on desktop). | "Reports menu shows very large". Works even if an older `admin.css` / `reports.css` is on the server. |
| `resources/views/layouts/admin.php` | MODIFIED | One line: loads `admin-v31.css` after `reports.css`. | Same. |
| `database/migrations/0018_v31_donation_review_repair.sql` | NEW | Idempotent repair: adds only the missing donation-review columns on `donations` (`verification_status`, `verified_by`, `verified_at`, `certificate_status`, `transaction_at`, `payment_method`, `payment_proof_path`, recipient/acknowledgment and referral fields) and the `donation_review_events` table. Existing rows become `verification_status='legacy'`, `certificate_status='not_issued'`; no amount, status or transaction is changed. | Your live error `Unknown column 'd.verification_status'`: migration `0012` never ran on the live database. |
| `tools/verify-install.php` | NEW | Install checker: lists files missing from, or different on, the server versus the V3 manifest. CLI only; read-only. | Finds every missing upload in one run. |
| `app/Support/Pdf/**` (10 files) | UNCHANGED (included for upload) | Same as V3. | The server is missing them; see above. |
| `resources/views/admin/dashboard.php` | MODIFIED | Redesigned, animated dashboard: welcome banner with live clock and greeting, KPI cards with count-up numbers and sparklines, interactive area / bar charts (30 days / 12 months, Amount / Gifts, hover tooltip), animated donut for giving by purpose, pulsing "needs attention" alerts, activity feed with search, inbox / website / system cards, auto-refresh every minute. Uses the same data as before ($finance, $inbox, $content, $system, $campaignStats); every value is read null-safely so it also works with an older DashboardController (the old one crashed here for a missing `monthlyTrend`). Role-based blocks are unchanged. | "Current dashboard design is very odd": requested redesign. |
| `resources/views/partials/dash-widgets.php` | NEW | Small helper functions (`dv31_*`) for sparklines, deltas, icons and initials. Does not depend on `charts.php`. | Support for the dashboard. |
| `assets/css/dashboard-v31.css` | NEW | Dashboard styling and animations, scoped under `.dv`; respects "reduce motion". | Same. |
| `assets/js/dashboard-v31.js` | NEW | Counters, charts, live clock, search and in-place refresh. No external libraries. | Same. |
| `VERSION` | MODIFIED | `V3.1`. | Versioning. |

## Deployment
1. Back up the current site, then extract the ZIP over the project root.
2. Nothing to run. Clear any opcache/page cache if your host uses one.
3. Check: open an admin report and click **PDF**, **CSV**, **Excel**; open `/`, `/donate`.

## Database migration (REQUIRED) — the second cause of the report errors
After the PDF library was uploaded, the live error became `SQLSTATE[42S22]: Unknown column 'd.verification_status'`. The live database is missing the donation-review schema (migration `0012`, which is not in Git).
1. Back up the database (cPanel > phpMyAdmin > Export).
2. cPanel Terminal, in the website folder: `php bin/console migrate:status` then `php bin/console migrate`. This applies any pending migrations in order (your own `0012`/`0013`, if the files exist on the server) and then `0018`.
3. No Terminal? In phpMyAdmin select the database, open the SQL tab and run the contents of `0018_v31_donation_review_repair.sql`. It is safe to run twice.
Tested: on a database in your server's state (Git migrations, no `0012`, with donations in it) the migration completed a half-applied state, a second run did nothing, and donation count, total amount and statuses were unchanged.
**Important:** existing donations become `legacy`. The V3 finance screens count only `verified` completed donations as confirmed, so finance totals will not include legacy donations until you decide, under your own policy, which to mark verified. This release does not change any financial record.

## Known issues found but NOT changed (read this)
1. **The Git `v3` commit is not the full approved V3.** `V3-RELEASE-MANIFEST.sha256` lists 608 files: 549 match, 34 differ (33 are byte-identical to the V2 commit, e.g. `ReportController.php`, `ReportService.php`, `FinanceService.php` callers, `Response.php`) and 25 are missing from Git (e.g. `ReportSpreadsheetService.php`, `DonationReviewController.php`, `DonationDocumentService.php`, `partials/donation-extra-fields.php`, migrations `0012`, `0013`). Every file in this ZIP was chosen so that, before my edit, it matched your V3 manifest (`routes/admin.php`, `home.php`, `donate/index.php`) or is new. Please commit the complete V3 file set to Git.
2. **Other files missing from Git** (`DonationReviewController`, `DonationDocumentService`, `ReportSpreadsheetService`, `config/donation_review.php`, `partials/donation-extra-fields.php`, migrations `0012`/`0013`) and 34 files that are older V2 copies (e.g. `AuditService` without `logRequired`, `UploadService` without `storePaymentProof`). On a site built only from Git, `/admin` (dashboard), donation export/proof/receipt, and Finance > Offline donation fail. If `php tools/verify-install.php` on the server lists these as MISSING or DIFFERENT, send me its output and I will rebuild exactly those.
3. **Stripe does not exist in this codebase.** Current behaviour, preserved as-is: PKR → online gateways (Meezan / UBL-Etisalat); other currencies → bank transfer. Adding Stripe is a new integration that needs your Stripe keys and webhooks.
4. Not covered in this release: PDF/eBook viewer changes, Grant / Grant-allocation reports (no grant tables exist in this schema), live payment-gateway tests.
