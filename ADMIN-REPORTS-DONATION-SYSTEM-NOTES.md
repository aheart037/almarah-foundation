# Admin reports: donation system (no fundraise)

Upload each file to the same path in the repo.

## Files
- app/Services/ReportService.php  (all report logic)
- app/Http/Controllers/Admin/DashboardController.php
- app/Http/Controllers/Admin/SettingsController.php
- app/Http/Controllers/Admin/UserController.php
- resources/views/admin/reports/show.php
- resources/views/admin/dashboard.php
- resources/views/admin/settings/index.php
- resources/views/admin/users/show.php
- resources/views/admin/users/index.php
- resources/views/admin/donations/show.php
- resources/views/admin/email/queue.php
- resources/views/admin/email/templates.php
- resources/views/admin/campaigns/form.php

## Reports
Removed: Fundraiser Performance, Fundraising Team, Fundraiser Approval
(/admin/reports/fundraisers, /teams, /approvals now return 404).
Added: Donation Purpose (/purposes), Referrer (/referrers), Donation Verification (/verification).
Changed: Overview, Campaigns (no fundraiser goals; title/status sort fixed), Refunds and Receipts ("Purpose" column).

## Not touched on purpose
- Inbox "Fundraising enquiries" box and volunteer "fundraising team" field.
- Stored email subject for account.created ("... fundraising"): edit in Admin > Email templates.
- fundraiser role and old database tables.

## Tested
SQL run against MariaDB with sample data: 508 report/sort/filter combinations, plus CSV, XLSX and PDF
export of every report. All changed PHP files pass php -l. Pages were not opened in a browser.
