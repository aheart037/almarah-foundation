# V2 Part 5 — server-side PDF, CSV and Excel exports

Branch: `v2-exports` (on top of the committed Part 4 reports branch). **Not deployed.** No extra report pages, permissions or invented financial data were added.

## What was inspected and implemented

The existing reports module already had protected PDF routes and a real FPDF-based PDF service, plus a 190-assertion regression suite. I retained those routes, FPDF and the existing responsive report page. I repaired/extended the PDF to embed the existing header-logo PNG when no uploaded logo is configured, keep brand text beside it, include record counts and the active currency filter, and export **all filtered main-table rows** in 5,000-row database batches rather than stopping at the old 5,000-row PDF limit. PDF generation and batch reads use a consistent database transaction. Long tables paginate automatically, repeat headings and carry a page-numbered footer.

Real, permission-gated `/{slug}/csv` and `/{slug}/xlsx` endpoints now exist for every report. They reuse the report service's normalized filters, main-table columns, status/date formatting, summary cards, supplementary breakdown tables (including overview trends), and single-currency accounting. CSV quotes fields and prefixes risky untrusted spreadsheet formulas with an apostrophe. Excel files are actual OOXML `.xlsx` ZIP workbooks with an XML worksheet, default/header styles, numeric amount cells displayed in the selected currency, and inert text cells. CSV/Excel use 5,000-row **batches, not a 5,000-row cap**; exports are written to private, mode-0600 temporary files, streamed as downloads, and removed after sending. The export build runs in a database transaction. `ext-zip` is required on deployment for Excel; a missing extension yields HTTP 503 rather than a fake workbook.

I also corrected an existing date-filter bug: converting a local-midnight `date_to +1 day` back through UTC could exclude the selected final day when the app timezone was not UTC. Date boundaries and overview trend enumeration now use explicit UTC calendar dates.

## 13 PDF checklist — actual endpoint/parser tests

Each line below was exercised through its authenticated `/admin/reports/{slug}/pdf` route against disposable MariaDB records. Every output opened with the independent `pdfinfo` parser and was inspected with `pdftotext` for its title, foundation branding, selected range, filter/record metadata, generation time, footer and page number. The **existing bundled logo was confirmed as an embedded PDF image**. Empty-result PDF and unauthenticated/unauthorized checks also ran for every slug.

| # | Report | Slug | PDF result |
|---|---|---|---|
| 1 | Reports Overview Dashboard | `overview` | **PASS** |
| 2 | Donation Summary | `donations` | **PASS** |
| 3 | Payment Transactions | `payments` | **PASS** |
| 4 | Campaign Performance | `campaigns` | **PASS** |
| 5 | Fundraiser Performance | `fundraisers` | **PASS** |
| 6 | Fundraising Team | `teams` | **PASS** |
| 7 | Donor Report | `donors` | **PASS** |
| 8 | Fundraiser Approval | `approvals` | **PASS** |
| 9 | Bank Transfer & Reconciliation | `reconciliation` | **PASS** |
| 10 | Refund & Failed Payment | `refunds` | **PASS** |
| 11 | Financial Summary | `financial` | **PASS** |
| 12 | Donation Receipt | `receipts` | **PASS** |
| 13 | Admin Activity & Audit Log | `audit` | **PASS** |

## Test evidence

- `php tests/reports_exports_test.php`: **198 export assertions passed**. In addition to the checklist, tests checked PDF financial gross (PKR 13,300), refunds (PKR 3,000) and net (PKR 10,300) against fixture records, a failed-transaction filter, every empty PDF, role/guest denial for every PDF, CSV and Excel integrity for each of the 13 routes, and permission denial for both spreadsheet formats. A 5,020-row audit export verified that **PDF, CSV and Excel all include rows past 5,000**; the long PDF was parsed and showed repeatable page numbers. CSV formula neutralization and Excel inert string cells were tested. An inclusive single-day date filter was tested with the app's non-UTC timezone.
- The `.xlsx` worksheet/workbook/styles XML parsed for all 13 reports; an independent `openpyxl` reader opened the financial workbook and read numeric major-unit money cells with the `PKR` number format.
- `php tests/reports_test.php`: **190 passed, 0 failed** (existing PDF pagination, header repetition, branding, empty PDFs, permission and report regressions). `php tests/reports_part4_test.php`: **58 passed**. Donation review **33 passed**, contact/volunteer workflow **37 passed**, legacy contact/volunteer migration passed. PHP lint and staged whitespace checks passed.

## Files changed or added

- `app/Services/ReportSpreadsheetService.php` (new): private batched CSV and OOXML `.xlsx` writer.
- `app/Services/ReportPdfService.php`: bundled logo fallback/branding, record count, streamed table-row provider.
- `app/Services/ReportService.php`: export pagination beyond first batch, UTC date boundary/trend fix, always describe active currency.
- `app/Http/Controllers/Admin/ReportController.php`: full filtered PDF batches plus CSV/Excel endpoints and export audit events.
- `app/Core/Response.php`: one-use private file response with deletion after streaming.
- `routes/admin.php`: protected CSV and Excel GET routes sharing `reports.view` middleware.
- `resources/views/admin/reports/show.php`: working download links for PDF, CSV and Excel.
- `tests/reports_test.php`: assert all five protected report route patterns.
- `tests/reports_exports_test.php` (new): 13-by-3 authenticated export integrations, PDF parser/authorization, large and empty exports, data/currency/formula tests.
- `docs/V2-EXPORTS-PART5.md`: this verification report.

## Operational caveats

- The existing bundled FPDF core fonts **do not faithfully render Urdu/Arabic or other non-Western scripts**; they may be transliterated or shown as `?` in PDF text. HTML, CSV and Excel preserve UTF-8. A Unicode-capable PDF font/renderer is needed before PDFs containing such names can be considered text-faithful. This was observed as an existing PDF limitation, not silently marked solved.
- Large PDFs are paginated and not capped at 5,000 rows, but FPDF holds the final document in PHP memory. Set appropriate worker memory/time limits and test production-sized data in staging; CSV/Excel use bounded database batches and temp-file streaming.
- The sample exports used a disposable local database and bundled PNG logo. Live credentials, production volumes, user-uploaded logo variants, bank statement reconciliation and external mail were not available. On deployment, install/enable PHP `zip`, ensure private temp storage is writable, and verify that a web response finishes streaming so one-use files are deleted. Back up production data and validate access controls in staging first.
