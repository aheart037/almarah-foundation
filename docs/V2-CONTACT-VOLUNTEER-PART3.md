# V2 Part 3 — Contact and Volunteer forms

Branch: `v2-contact-volunteer` (built on committed Part 2). Scope: only the existing Contact and Become a Volunteer page form slots and their existing admin inbox workflows. No new page URL, alternate inbox, contact/volunteer table, donation/gateway change, or production deployment.

## What changed

- Replaced the two Forminator shortcode tokens in the original page templates with includes of the existing form partials. The surrounding headings, content, branding, headers, footers and URLs remain unchanged. For published CMS overrides of these same pages, the renderer replaces just the matching shortcode inside `body_html` with the same form, retaining neighboring content.
- Contact requires full name, email, phone, subject and message. Volunteer requires name, email, mobile, city, address, occupation category/conditional occupation detail, skills, interests, days available, experience and motivation; WhatsApp and extra notes are available. Forms use labels, required-field and length checks, inline error/feedback areas, mobile-friendly existing site form styles, conditional occupation display and checkbox-group validation. Server-side validation remains authoritative.
- Existing CSRF middleware and form guard (honeypot, submission timing, rate limit, fingerprint-based recent duplicate detection) remain in use. Contact messages and volunteer applications are saved in the existing tables; volunteer supplemental fields use existing JSON columns. Public submissions append case events. Office notifications and volunteer acknowledgment are queued through the existing mail queue.
- Existing admin inbox supports the requested list, search, status/date filters, details and filtered CSV export. Contact records can be assigned to active staff, moved through New/In Progress/Responded/Closed, and given a recorded response with optional queued email. Volunteers can be moved through New Application/Under Review/Approved/Rejected/Active/Inactive, assigned to existing campaigns/teams, and reviewed with skills/availability visible. Each update/response appends an event with actor and time. Existing status `spam` is retained for legacy moderation. Inbox deletion is disabled for these two case types to protect their activity histories; any separately authorized data-erasure procedure would need to be handled outside this workflow. Existing per-box permissions remain enforced.
- Additive migration `0013` extends the existing tables and creates `form_case_events`. It saves prior contact/volunteer statuses as imported events before mapping historical contact `read`, `replied`, `archived` to `in_progress`, `responded`, `closed`. Existing comments and records are preserved.

## Files changed or added

| File | Change |
| --- | --- |
| `database/migrations/0013_contact_volunteer_workflow.sql` | Additive columns, status extension/mapping, historical case events |
| `resources/views/public/pages/contact.php` | Replace only contact shortcode slot |
| `resources/views/public/pages/become-a-volunteer.php` | Replace only volunteer shortcode slot |
| `app/Http/Controllers/Public/PageController.php` | Same slot replacement for published CMS page overrides |
| `resources/views/partials/forms/contact.php` | Contact fields and client-side constraints |
| `resources/views/partials/forms/volunteer.php` | Volunteer fields, conditional UI and group validation |
| `resources/views/partials/forms/_field.php` | Allow HTML `minlength` alongside existing field constraints |
| `app/Http/Controllers/Public/FormsController.php` | Server validation and duplicate-attempt handling |
| `app/Services/FormService.php` | Preserve additional data, append submission events, queue acknowledgment |
| `app/Http/Controllers/Admin/InboxController.php` | Workflow, assignment, search/filter/export, histories, response handling, permissions |
| `resources/views/admin/inbox/index.php` | Date/status filters and export link |
| `resources/views/admin/inbox/show.php` | Case details, assignments, response and immutable activity display |
| `app/Services/AuthService.php` | Invalidate permission cache on login/logout (identity changes in a process) |
| `tests/form_pages_test.php` | Public-to-admin, validation, guard, permission and captured mail transport integration tests |
| `tests/form_migration_test.php` | Pre-migration records/status preservation test |
| `docs/V2-CONTACT-VOLUNTEER-PART3.md` | This report |

## Verification performed

- Disposable local MariaDB only; migrations from scratch and seeded pre-`0013` legacy data both passed. Preexisting contact notes/status and volunteer status/skills survived; legacy statuses were captured before contact mapping.
- `php tests/form_pages_test.php`: **37 assertions passed** across both page render paths, CSRF, validation, spam/duplicates, DB persistence, email jobs, real queue processing with a capture-only mail transport, staff workflows, assignment, history, filters/export and permissions.
- `php tests/donation_review_test.php`: **33 assertions passed**; `php tests/reports_test.php`: **190 passed, 0 failed**. `php bin/console lint`: **135 PHP files, 0 failures**. `git diff --check`: clean.

## Deployment and limitations

Run normal migrations in staging with a backup and validate the two pages against actual CMS content before production; this branch is not deployed. The test mail transport captures deliveries; no live SMTP credentials or deliverability test were available. The duplicate guard is the application's existing time-window/fingerprint check, not a guarantee against concurrent submissions racing. Live browser/device accessibility review and load testing have not been performed. No tax-exemption document was generated or authorized. Other modules were not advanced.
