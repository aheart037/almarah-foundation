# Almarah Foundation — Revision 3.6 (admin panel update)

Extract this ZIP over the project root (folder paths are preserved), then run the database migrations
(SSH: `php bin/console migrate`; or open `https://YOURSITE/setup`; or in phpMyAdmin > SQL paste `setup/3-run-third-0021-v36.sql` and click Go).
**Migration `0021_v36_team_mou_inbox.sql` is REQUIRED.** It is idempotent and deletes nothing.

## What changed
1. **Sidebar** — removed Campaigns, Pages, Banners, Job openings, Grants. Inbox badge/list = unread only.
   (Routes still exist; only the menu entries were removed.)
2. **Board & Team** — `Admin → Website → Board & Team` now drives the public `/members` page.
   Categories: Board of Directors, Honorary Members, Advisory Board, Management, Ambassadors.
   Create / edit / delete / reorder / hide. "Page headings" button edits the banner and section headings.
3. **MOUs & Certificates** — drives `/mous` and `/registrations-mou`. Create / edit / delete / reorder / hide.
4. **Migration 0021** copies the previously hard-coded content (3 board, 26 honorary, 8 ambassadors,
   18 MOUs, 7 certificates) into the database, only for categories/types that are still empty.

## Files
| File | Status |
|---|---|
| resources/views/layouts/admin.php | modified |
| app/Http/Controllers/Admin/InboxController.php | modified |
| resources/views/admin/inbox/index.php | modified |
| app/Admin/ResourceRegistry.php | modified |
| app/Http/Controllers/Admin/ResourceController.php | modified |
| resources/views/admin/resources/index.php | modified |
| app/Services/CmsService.php | modified |
| app/Http/Controllers/Public/PageController.php | modified |
| resources/views/public/pages/members.php | modified (now dynamic) |
| resources/views/public/pages/mous.php | modified (now dynamic) |
| resources/views/public/pages/registrations-mou.php | modified (now dynamic) |
| database/migrations/0021_v36_team_mou_inbox.sql | NEW |
| setup/3-run-third-0021-v36.sql | NEW (same SQL, for phpMyAdmin) |
