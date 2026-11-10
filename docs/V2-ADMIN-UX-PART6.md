# V2 Part 6 — existing admin UI/UX improvement

Branch: `v2-admin-ux`, based on Part 5 commit `12a80c4`. No schema migration, public-page change, replacement admin module, or route change.

## What changed

- Kept the existing permission-filtered sidebar and all existing links. Clarified Workspace, Website, People, Finance, Insights and System groupings; exposed the **existing** campaigns route to admin-role users; retained the catalog-backed expandable 13-report menu. Current links now have `aria-current="page"`. Fixed the previously white-on-white Sign out button.
- At ≤1024px the sidebar is an overlay drawer rather than a horizontal or page-pushing menu. It has a backdrop, Escape/backdrop close, expanded state, focus return and tab wrapping; on desktop it remains visible. Narrow filters and form grids now fit the viewport; wide data tables still scroll *inside* their panels. Changes are scoped to `.admin-body` and admin JS/CSS.
- Extended the existing database dashboard: completed **staff-verified** gifts (primary currency only), pending verification, donor records, active scheduled campaigns, published approved fundraisers, contact inquiries, volunteer applications, 12-month verified-donation trend and an actionable queue. Existing 30-day trend, purpose breakdown, recent transactions, inbox, website health and audit activity remain. The review queue is distinct from pending payment. No historical `legacy` completion or other currency is silently counted as verified PKR income. These gross dashboard cards exclude refunded gifts; the detailed Reports module remains the place for verified gross/refunds/net.
- Added a local search with a no-match message to the existing recent-transaction table. Existing report and donation server-side filters, chart hover/tap, flash success/error alerts, form controls and confirmation dialogs are preserved; no duplicate forms, modals or database tables were introduced. Existing chart helper accepts an optional currency and accessible description without changing its callers.
- Sensitive dashboard blocks remain permission-gated. A reviewer with `dashboard.view` and `donations.view` receives financial cards, not inbox or reports. Admin-only campaign navigation is hidden from non-admin roles; report routes remain server-protected.

## Verification

- `php tests/admin_ux_part6_test.php`: **76 assertions passed** against a disposable migrated/seeded MariaDB. Tests real dashboard sums and isolation of legacy/USD data, pending workflow, action cards, role-limited output and forbidden report access, every rendered sidebar link against a registered GET route, representative admin navigation and all 13 report page routes.
- `ADMIN_PREVIEW_DIR=$PWD/tests php tests/admin_ux_part6_test.php` then `node tests/admin_ux_browser_part6.cjs`: **54 Chromium checks passed**. Real DB-rendered HTML with local admin CSS/JS at 1440px desktop, 1200px laptop, 820px tablet, 390px phone and 320px small phone for dashboard, donations, reports, contact inbox, campaigns and settings. Checks page overflow, drawer open/Escape close, report expansion and transaction search. Tables deliberately have internal horizontal scrolling. Requires locally installed Playwright and Chromium; generated `.admin-*.html` snapshots should be deleted afterward.
- Regression tests: `reports_part4_test.php` **58**, `reports_test.php` **190/190**, `donation_review_test.php` **33**, `form_pages_test.php` **37**, `reports_exports_test.php` **198**. `php bin/console lint`: **139 PHP files, 0 failures**. Tests are destructive: use a database with `test` in its name, never production.

## Changed files

- `app/Http/Controllers/Admin/DashboardController.php` — permission-scoped source queries and monthly series.
- `resources/views/admin/dashboard.php` — real-data cards, task queue, trend and search affordance.
- `resources/views/layouts/admin.php` — existing navigation grouping, active state, admin campaign link and drawer controls.
- `resources/views/partials/charts.php` — optional currency/accessible series description.
- `assets/css/admin.css` — admin-only responsive sidebar, cards, table/filter/form refinements.
- `assets/js/admin.js` — drawer keyboard/backdrop behavior and local recent-table search.
- `tests/admin_ux_part6_test.php`, `tests/admin_ux_browser_part6.cjs` — repeatable integration and browser smoke checks.

## Remaining limitations

- The 12-month/30-day dashboard uses completed staff-verified donations in the configured site currency, not a converted multi-currency total or a refund-adjusted net. Refer to Financial Reports for gross/refunds/net and other currencies. Historical legacy donations are excluded from the verified cards.
- Donor records count all donor records, not only distinct verified donors; fundraiser counts are published and approved within scheduled dates. Those definitions are shown beside the cards.
- The browser checks use locally rendered fixture HTML and assets, not production data or a live production deployment. They check representative page layouts and routes, not every individual edit flow, attachment, destructive action or every breakpoint. Existing native confirmation dialogs and in-page flash notifications remain; a new modal framework was intentionally not introduced.
- The existing `initial_reviewer` role name differs from the user-requested `initial_review` label; this Part 6 did not change authorization or migrate roles. Production SMTP/gateways and independently deployed fundraising integration were not available for end-to-end testing.
