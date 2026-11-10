# V2 Part 4 — Admin reports (13 pages)

Branch: `v2-reports`, based on committed Part 3. **No production deployment. No new PDF export implementation.**

## Inspection before modification

The repository already had `ReportService`, `ReportController`, a shared responsive report view, protected routes for all 13 slugs, an expandable 13-item Reports sidebar, report permissions, sorting/pagination/date filters, and a 190-assertion reports suite. These were retained rather than duplicated. Existing historical logic counted `status=completed` as confirmed even for pre-V2 records marked `verification_status=legacy`; it also had no offline-transfer report, counted outcome-based refund amounts as if they were actual refund transactions, and displayed the public donation reference as a receipt number. The user explicitly chose to **exclude legacy completed donations from verified totals** and to show **verified gross including a later-refunded gift, less actual completed refunds for net**.

## Accounting contract

- A *standing verified donation* has both `verification_status=verified` and `status=completed`. Historical completed rows with `verification_status=legacy` are never presented as staff-verified. Refunded verified donations remain in the **financial gross** at their original amount, even when their current status becomes `refunded`; completed `payment_refunds` rows are subtracted exactly once in the refund's period. Financial net = verified gross − completed refunds on verified donations. A partial refund of an otherwise completed gift is also deducted once. Gateway attempts never multiply donations.
- Payment-attempt totals are labeled as **payment outcomes**, not verified donation revenue. Pending, rejected, failed and cancelled states are separate; rejected review amounts do not enter pending financial amounts. Every money report selects a single currency, with no conversion. Financial, campaign and method subtotals follow the stated date and currency basis. Refund-only months may correctly have negative net.
- Campaign/team/fundraiser *raised* measures use standing verified gifts. The campaign has **no native goal field**; its existing combined fundraiser goals are explicitly labeled as such rather than invented as a campaign goal. An offline transfer's donor-supplied bank reference and staff approval are **not** a bank-statement reconciliation. Absent bank reconciliation status is displayed as “Not recorded”.

## Page status (existing page enhanced or retained)

| # | Report | Outcome and any unavailable metric |
| --- | --- | --- |
| 1 | Overview | Enhanced verified totals, separate rejected/failed metrics, month/day trend, payment-method/campaign charts, recent transactions and review events; searchable/sortable/paginated fundraiser results. Snapshot counts are labeled “right now”; search applies to result list, not dashboard-wide money cards. |
| 2 | Donation Summary | Enhanced verified totals, period grouping, donor search, rejection/cancellation cards and separate outcomes, top verified donors. |
| 3 | Payment Transactions | Enhanced actual receipt number, donor, campaign, method and verification columns; keeps one row per payment attempt, search, sort and pagination. Offline submissions are on report 9 because they have no gateway attempt. |
| 4 | Campaign Performance | Enhanced remaining amount against **combined fundraiser goals**, with verified donations/donors; a standalone campaign-native goal is unavailable in the schema. |
| 5 | Fundraiser Performance | Existing search/status/campaign filters, period and all-time verified amounts, donors and counts retained. |
| 6 | Fundraising Team | Existing team membership/campaign links and verified attribution retained: only donations carrying `team_id` count. |
| 7 | Donor | Enhanced actual acknowledgment recipient(s) from gifts; verified gifts, first/latest dates, unique donor aggregation and search retained. |
| 8 | Fundraiser Approval | Enhanced campaign column; existing application/review audit information, statuses, reviewer and rejection reason retained. Missing historical decision logs remain unavailable. |
| 9 | Bank Transfer & Reconciliation | Enhanced selectable **paginated offline-transfer view** with real reference, donor, actual receipt, entered date, amount, verifier and explicit “Not recorded” reconciliation state. Existing gateway exception diagnostics retained. No bank statement/settlement records exist, so bank matching remains unavailable. |
| 10 | Refund & Failed Payment | Enhanced transaction/receipt IDs, original/refund amount and refund date; actual completed partial refunds have a separate searchable, paginated view, while full-refund/failure outcomes and failed refund requests remain visible. |
| 11 | Financial Summary | Enhanced staff-verified gross, actual completed refunds, net, pending/rejected cards, payment-method breakdown including offline gifts, monthly/day/year and campaign summaries. Refund-only periods and partial refunds are handled. Campaign summary remains a top-10 view, not a complete paginated campaign ledger. |
| 12 | Donation Receipt | Enhanced actual `receipt_number`, recipient, certificate/verification/email statuses and search; unverified legacy donations do not masquerade as authorized receipts. |
| 13 | Admin Activity & Audit Log | Enhanced current role and recorded safe-metadata previous/new values, IP and action time. Default scope includes all staff (including finance and review roles). Historical role membership and missing before/after metadata cannot be reconstructed. |

All 13 existing pages remain database-driven and permission-protected. Their main result lists retain date filters, search (overview search affects its fundraiser result list), whitelisted sorting, pagination, responsive table wrappers and empty states. Some **supplementary top-N tables/charts are intentionally limited views**, not full paginated ledgers; use their main report or relevant filters for detailed records. No dummy operational data, placeholder pages or duplicate report controller were added.

## Changed files

- `app/Services/ReportService.php` — existing queries, filters, summaries, report-specific columns, financial recognition and new selectable offline/partial-refund report modes.
- `resources/views/partials/charts.php` — include rejected and unverified/legacy outcomes in the overview chart instead of silently omitting them.
- `resources/views/admin/reports/show.php` — enable sorting on the overview main table and clarify chart/directory copy; existing layout/sidebar unchanged.
- `tests/Reports/Fixture.php` — mark synthetic historical **test fixture** paid gifts as verified to exercise the current policy (production legacy records are not modified).
- `tests/reports_test.php` — correct two stale test descriptions referring to default audit scope and the old absence of transfers.
- `tests/reports_part4_test.php` — additional real-schema regression suite.
- `docs/V2-REPORTS-PART4.md` — this status and limitations report.

No schema migration was required. The already-expanded Reports sidebar and its `reports.view` permission-gated routes were reused unchanged. The repository already had a PDF export endpoint; it was **not newly implemented or extended as a Part 4 export feature**, and Part 5 remains the place for any new PDF work.

## Verification

Disposable MariaDB test database only, never production:

- `php tests/reports_test.php`: **190 passed, 0 failed** (existing reports/permission/export regression suite).
- `php tests/reports_part4_test.php`: **58 assertions passed**, including legacy exclusion, repeated gateway attempts, verified refunded gross, full + partial refund net, refund-only negative month, currency separation, offline bank records without invented reconciliation, search/sort, actual receipt/recipient/audit metadata, all 13 query paths and all 13 authenticated HTTP/admin view paths plus sidebar.
- `php tests/donation_review_test.php`: **33 assertions passed**; `php tests/form_pages_test.php`: **37 assertions passed**; `php tests/form_migration_test.php`: legacy data preservation passed.
- `php bin/console lint`: **136 PHP files, 0 failures**; `git diff --check`: clean.

Staging review should validate production-sized data, index/query plans, permissions, actual currencies and historical refunds before deployment. No live bank feed, SMTP, browser/device accessibility run or production data was available. The bank reconciliation, campaign-native goal and absent historical metadata listed above remain **unavailable metrics**, not fabricated results.
