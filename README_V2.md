# Almarah Foundation V2 — work in progress (checkpoint 1)

This ZIP contains ONLY new/modified files, with original paths. Extract over the repository root.
The final README_V2.md (features, env vars, migrations, roles, backup, limitations, test results) is produced with the final package.

## Included in this checkpoint: Reports module (13 reports + server-side PDF export)
- Ported from the almarah_fundraise reference repo, adapted to this site's permission system.
- Routes: /admin/reports, /admin/reports/{slug}, /admin/reports/{slug}/pdf — all require login + `reports.view` (checked server-side).
- Admin sidebar: expandable "Reports" group listing all 13 reports.
- PDF writer: FPDF 1.9 vendored in app/Support/Pdf (no Composer needed). Latin text only; Urdu/Arabic prints as "?" in PDFs.
- Tests: `TEST_DB_NAME=almarah_test TEST_DB_USER=... TEST_DB_PASS=... php tests/reports_test.php` (WIPES that database; name must contain "test").
- No database migration in this checkpoint.

## Known gaps in this checkpoint (fixed in later phases)
- Reports do not yet know about the V2 verification workflow, receipt numbers or offline-donation proof (not built yet).
- ReportService still prints "bank transfers are not recorded" notes inherited from the reference repo; that is no longer accurate for this site.
