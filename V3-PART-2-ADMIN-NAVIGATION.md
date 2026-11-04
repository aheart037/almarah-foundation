# V3 Part 2 — Admin dashboard and navigation

**Date:** 4 November 2026 (Asia/Karachi)  
**Base:** user-approved local `v2-delivery` (`6be9d92`), not GitHub `main` (`b470a6a`). This part is uncommitted; Part 1 audit is a separate untracked document. No Part 3 work was started.

## Scope and fixes

- Audited the entire sidebar, its 13-report nested menu, linked admin screens, redirects and route middleware. Kept existing branding, layout, forms, payments and report calculations.
- **Permissions are now enforced behind the menu.** Previously `admin`-role middleware alone allowed access to Users, Email Templates and Settings despite their menu items being hidden when the named permission was absent. `routes/admin.php` now requires `users.manage` for user screens/actions and `settings.manage` for email templates/site settings/actions; super-admin-only credential, order and role operations remain super-admin-only. Campaign routes continue to require admin/super-admin **and** now require `content.manage`. Other routes retain their existing permission/role guards, per-resource CMS/Inbox checks and CSRF checks.
- Sidebar `Overview` is shown only with `dashboard.view`; payment callbacks appear whenever the user has `logs.view` even without donations/finance grants. CMS bank accounts still require `finance.manage` in `ResourceController`; Job Openings still require `volunteers.manage`; Campaigns remain admin-only with `content.manage`. Inbox and Reports remain permission filtered. No route is protected *only* by being hidden.
- Active state now uses canonical normalized paths (works in subfolder installations), exact or slash-boundary matching (no false prefix collisions) and highlights the correct submenu item, including report exports. The Reports group opens automatically on report pages and still supports native keyboard/mouse expansion. Emails no longer share an active state with Email Queue.
- Fixed page-level dead/unauthorized links: view-only donation users no longer see the restricted CSV export; staff without settings permission no longer get a Settings link in the dashboard warning or Email Queue; settings-only staff do not get the logs-only Email Queue link from Settings or Email Templates; users without donations permission do not get a Donor List link; the nonexistent `/admin/fundraisers/{id}` links in Users/Reports are replaced with a permission-gated report search or plain text; campaign links in reports are shown only if the viewer can actually edit campaigns.
- The Settings page previously submitted “fundraiser controls” to an **unregistered POST route** and did not load actual capability values. The panel is now visibly unavailable and its inputs/button disabled; it does **not** pretend to save or change fundraiser permissions. Separate fundraising host ownership and a real settings API remain for a later authorized part. Its mobile fieldset is constrained to prevent horizontal overflow.
- Existing desktop sidebar and mobile drawer, backdrop, Escape/focus behavior, CSS and JavaScript remain otherwise intact; no redesign or global public-site changes. Existing Apache rewrite rules and PHP development router still route refreshed admin URLs through the front controller.

## Navigation and authorization test evidence

Tests used an explicitly disposable `almarah_v3nav_test` MariaDB database, rebuilt with the existing migration fixtures. No production records, live gateway or real SMTP were used. The local test server was stopped and generated snapshots, temporary key and test logs were removed afterward.

| Check | Outcome |
| --- | --- |
| `tests/v3_admin_navigation_part2_test.php` | **258 assertions passed:** every authorized sidebar item and submenu has a GET route, returns admin HTML/200, returns 200 on repeat dispatch, has exactly one correct active link; 13 report entries; folder-installed route; dashboard; view-only and non-staff/guest denials; direct GET, export/PDF, and token-bearing POST authorization checks; super-admin access. |
| Live Chromium `tests/v3_admin_navigation_browser_part2.cjs` against PHP built-in server | **199 checks passed:** real login/redirect, all **38 distinct** authorized sidebar targets loaded with 200 and correct active state, all survived browser refresh; nested reports menu, click navigation, drawer/Escape, and no overflow at widths 1024/820/390/320. Run with `NAV_TEST_URL=http://127.0.0.1:<port> NAV_TEST_LOGIN_EMAIL=<disposable user> NAV_TEST_LOGIN_PASSWORD=<disposable password> node tests/v3_admin_navigation_browser_part2.cjs`; requires Playwright and local test server. |
| Existing `tests/admin_ux_part6_test.php` | **76 assertions passed** on disposable database. |
| Existing `tests/admin_ux_browser_part6.cjs` | **54 Chromium checks passed** at 1440/1200/820/390/320 using temporary rendered test snapshots. |
| Existing `tests/qa_routes_part7_test.php` | **40 assertions passed** for public/admin/login routing against disposable database. |
| Static checks | `php -l` passed for all changed PHP and new PHP test; `node --check` passed for new browser test; `git diff --check` clean; no simple literal admin view link mismatches against registered routes. |

The initial Chromium attempt lacked system libraries; they were installed for the sandbox. A browser check then caught a 390px horizontal overflow in the disabled Settings fieldset, which was corrected; both Chromium suites passed afterward. These tests do not certify the production web-server configuration or the external fundraising service.

## Changed files and database

**Modified (10):**

- `routes/admin.php`
- `resources/views/layouts/admin.php`
- `resources/views/admin/dashboard.php`
- `resources/views/admin/email/templates.php`
- `resources/views/admin/email/queue.php`
- `resources/views/admin/donations/index.php`
- `resources/views/admin/reports/show.php`
- `resources/views/admin/settings/index.php`
- `resources/views/admin/users/index.php`
- `resources/views/admin/users/show.php`

**New (3):** `tests/v3_admin_navigation_part2_test.php`, `tests/v3_admin_navigation_browser_part2.cjs`, `V3-PART-2-ADMIN-NAVIGATION.md`.  
**Deleted:** none. `V3-PROJECT-AUDIT.md` remains the separate Part 1 deliverable; it was not changed here.  
**Database migrations or application-schema changes:** none. Test fixtures rebuilt only the explicitly disposable `almarah_v3nav_test` database. No application records were migrated/modified outside that local test database. No commit or push.

## Remaining issues and release notes

1. **Role migration/operations:** a user with only the `admin` role and no named `users.manage` or `settings.manage` grant will now receive HTTP 403 for those pages; this deliberately matches the existing permission-based sidebar and least-privilege seed grants. Audit real staff role grants and explicitly authorize any desired additional access before rollout. Super administrators retain access.
2. **Fundraiser controls** cannot be saved from the main site's Settings page until the separate fundraising deployment/contract is specified. Disabled UI prevents a misleading submission/404; no new capability storage or API was invented.
3. Full donor/fundraiser self-service routes (`/dashboard`, `/fundraisers`) and genuine V1 donation artifact were flagged in Part 1 but are **outside this admin-only part**. The report now avoids linking to a nonexistent local fundraiser admin detail page. No external fundraiser host or live bank/SMTP was tested.
4. Verify Apache/LiteSpeed rewrite and cookie/HTTPS/subfolder settings on staging before release. Browser refresh was tested on the local PHP front controller, not the production host. Production schema and permissions were not accessed.

**Stop: wait for explicit approval before Part 3.**
