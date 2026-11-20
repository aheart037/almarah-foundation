# Admin glass design + In-kind donations (merged)

Extract this ONE zip over your project. It contains both pieces of work, already merged, so nothing overwrites anything.

The only file both changes touched is `resources/views/layouts/admin.php`: it has the glass design AND the
"In-kind donations" sidebar link (Finance group). Do not extract the old inkind.zip or the earlier glass zips after this one.

## NEW: In-kind permissions (grantable to roles)
In-kind now has its own two permissions, shown on Admin > Roles & permissions > Edit under the group **In Kind**:
- `inkind.view` — open the list/detail pages, certificates and photos
- `inkind.verify` — verify/reject/reopen, generate and re-send certificates

Run once on the database: `database/migrations/0023_inkind_permissions.sql` (same as `setup/5-run-fifth-0023-inkind-permissions.sql`).
It is safe to re-run. Existing roles keep their current access (roles with donations.view / donations.verify get the matching in-kind permission),
and super_admin + finance_manager get both. The sidebar link and every in-kind route now check these permissions instead of the donation ones.

## Before committing
Rename `assets/js/dashboard-glass.js.txt` -> `assets/js/dashboard-glass.js`
(.txt only avoids an antivirus false positive on zips that contain .js files).

## In-kind donations (unchanged from your zip)
Migration `0022_in_kind_donations.sql` (setup/4-run-fourth-…) then `0023_inkind_permissions.sql` (setup/5-run-fifth-…)
still need to be run once on the database, in that order. Routes, controllers, services, public Donate Goods form,
admin list/detail pages, certificate email and tests are exactly your files.

## Glass design
assets/css/dashboard-glass.css, assets/css/admin-glass.css, assets/js/dashboard-glass.js,
resources/views/partials/admin-dock.php, resources/views/admin/dashboard.php, resources/views/layouts/admin.php.
See DASHBOARD-GLASS-NOTES.md in the previous package for details (set `$glass = false;` in the layout to switch it off).
