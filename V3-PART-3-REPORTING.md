# V3 Part 3 — Reporting investigation (implementation blocked)

**Date:** 4 November 2026 (Asia/Karachi)  
**Base:** user-approved local `v2-delivery` plus the uncommitted Part 2 changes.  
**Status:** Investigation only. **The reported page error's actual root cause has not been established; no reporting source, financial data, or database migrations were changed.** The instruction to establish the root cause *before* modifying reporting functionality takes precedence over guessing a fix.

## What was inspected

- Protected GET routes for the 13 existing reports and PDF/CSV/XLSX (`routes/admin.php`), `ReportController`, `PermissionMiddleware`, `ReportService`, `ReportPdfService`, `ReportSpreadsheetService`, shared report template/filters, and the existing report fixtures/tests.
- Source SQL relationships: `donations` to donors/campaigns/gateways and separately to payment attempts/refunds; monetary reports use currency-specific minor units and staff verification. Date selection generally uses inclusive UTC start and exclusive next-day end. Existing reports include donations, donors, payments, financial summaries and day/month/year grouping. The donation summary lacks a dedicated status selector; the payment report has one. Export URLs are permission guarded. Neither these code observations nor test fixtures reveal the cause of the *reported deployment page error*.
- Search across `database/migrations/`, `app/`, `routes/` and report views found **no grants, grant applications, awards or allocations tables or report source**. The user confirmed there are no grant records. Therefore `grant` and `grant allocation` reports/filters cannot be calculated from actual records in this model; creating empty or fictitious financial reports would violate the requested accuracy rules. The existing `campaigns` and donation purposes are not silently treated as grants.

## Reproduction and test evidence before any proposed modification

Using only the disposable MariaDB database `almarah_v3reports_test`, the current source passed:

- `tests/reports_test.php`: **190 passed, 0 failed** — totals, ranges, filters, statuses, pagination, permissions and PDF rendering against known inserted rows.
- `tests/reports_part4_test.php`: **58 passed** — verified versus legacy money, refunds, duplicate payment attempts, offline transfers, actual records and all report pages.
- `tests/reports_exports_test.php`: **198 passed** — all 13 PDFs parsed by independent tools, CSV and XLSX exports, filters and financial values. An initial run of this test failed because the sandbox lacked the **test-only `pdfinfo`/`pdftotext` programs**; installing `poppler-utils` made the unchanged application pass. This is **not evidence** that missing PDF tools caused the user's deployment page error; those tools are used by the test, not by the web app.

The tests write fixtures only into the named disposable database. They did not inspect or modify the production database, and cannot certify the deployed report pages. The user described the failure as a report page error but could not provide its URL, error text or a sanitized log excerpt. No production server/database access, deployed schema ledger, PHP error log or screenshot was supplied. **Consequently no actual production root cause can honestly be asserted or repaired yet.** Possible causes such as a missing migration, runtime PHP extension, mismatched deployed code, database permission or error in a particular report are hypotheses, not findings.

## What is already present / what remains unsupported

| Requested item | Source-backed status |
| --- | --- |
| Donation, donor and payment reports | Existing `donations`, `donors`, `payments` report slugs. Payment outcomes and verified donation revenue are distinct. Exact deployed failure unknown. |
| Financial summaries and date-based reports | Existing `financial` and date-grouped `donations` reports, with UTC range filters and separate currencies; existing refunds count completed refund records rather than fabricated transactions. Exact deployed failure unknown. |
| Start/end dates, search, sort, pagination | Existing shared form and whitelist-based report query machinery; some supplemental top-N tables are intentionally not complete paginated ledgers. |
| Status filters | Available for payment report and relevant other report types; not a dedicated donation-summary status selector. This is a feature gap, not a reproduced cause of the page error. |
| CSV/Excel/printable PDF | Existing protected exports passed the disposable-data suite after the independent test parser was installed. Live host capability and data not validated. |
| Grant and grant allocation reports/filter | **Unavailable:** no grant facts, IDs or allocation model. User confirmed there are no grant records. Do not present campaigns, permissions or donations as grants. |
| Permission restrictions | `reports.view` is required on page and each PDF/CSV/XLSX route; existing fixture tests exercise the guards. Production role assignments unknown. |

## Required diagnostic evidence to unblock a repair

A staff member can provide **one sanitized failing request** (report path and filters, not donor data), its HTTP status and a timestamp-matched PHP/server error message or SQLSTATE. Separately, a read-only comparison of the deployed Git revision, applied migration *names* (`migrations` table) versus files `0001`–`0013`, installed PHP version/extensions (`pdo_mysql`, `mbstring`, `zip`, image library), and the affected staff role's permission names would distinguish application, schema, extension and authorization failures. Do not send passwords, donor rows, payment credentials, full exception dumps with secrets, or run migrations against production as a diagnostic.

If the failure cannot be observed on production, provide an isolated staging database with a **sanitized, representative schema/data copy** and the failing URL so it can be reproduced safely. Once a root cause is reproduced, fix only that path and add a targeted regression for page/filters/empty state/export/permission, then rerun all existing suites. Any later grant reporting requires an separately approved grant data source/schema and authoritative accounting definitions; Part 3's “where supported” condition is not met now.

## Changes, database and outstanding issues

- **New:** `V3-PART-3-REPORTING.md` (this investigation). **Modified or deleted Part 3 source files:** none. Earlier Part 1 and Part 2 changes are unchanged. No commit/push.
- **Application database/schema/financial records:** no changes. Test suites recreated only `almarah_v3reports_test` and inserted synthetic test records; those are not production transactions.
- **Outstanding critical blocker:** no reproducible report-page failure or actual deployment diagnostics. Unsupported grant reports/selection (no grant records). Status-filter enhancements and any other reporting changes are deferred until the page error is diagnosed, so the requested repair is **not represented as complete**.

**Stop here. Do not start Part 4 or make speculative reporting changes without the missing evidence/approval.**
