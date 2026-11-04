# V3 Part 7 — User and Role Management

**Delivery status:** Implemented locally on `v2-delivery`; Part 8 not started. Part 6 is independently blocked and has not been implemented. This report describes code and disposable-database tests, not a production deployment.

## Delivered

- **Roles:** `/admin/roles` lists persisted roles and their membership counts. Super Admin can create and edit labels, keys (except built-in keys), descriptions, and permission grants; it can delete only unused non-system roles. The role editor groups permissions by category and exposes read-only role details to an authorized role viewer. Unknown permission IDs are rejected; grant replacement, validation, and audit are transactional. Super Admin's role cannot be edited or deleted. System role keys cannot be renamed or deleted.
- **Users:** `/admin/users/create` creates an account with a random unknown password and initiates the existing time-limited password-reset/setup flow; creation does not reveal or choose a password. `/admin/users/{id}` supports editing name, email and phone; viewing roles, related activity and history; sending a password reset request; verification; suspension/reactivation; and Super Admin role assignment. Accounts are *not hard-deleted*: suspension preserves donations, audit trail and other history. Email delivery depends on configured templates and queue processing; operators should verify the email queue rather than assume delivery.
- **Backend authorization:** Specific user read/create/edit routes accept the corresponding `users.*` grants or legacy `users.manage`. Role writes and role assignment require Super Admin through server-side middleware **and** the role service verifies Super Admin in the database. Role reads require `roles.view` or `roles.manage`. Existing donation, finance, reporting and settings routes retain their existing server-side permission gates; a menu is not the security boundary. A custom role with a granted admin permission is no longer blocked by a hard-coded staff-role list. Non-active accounts cannot authenticate or continue using an existing session.
- **Lockout protection:** Role assignment and status changes serialize through a locked Super Admin role row and account row, count only *active, undeleted* Super Admins, forbid self-demotion/self-suspension, and reject removal/suspension of the last active Super Admin. A non-Super Admin with user-edit privileges cannot modify, verify, reset, or suspend a Super Admin account. Email verification still activates a pending account but cannot accidentally reactivate a suspended account.
- **Audit:** Role creation/edit/grant replacement/deletion, user creation/edit, role assignment, suspension, and reactivation require an audit write in the same transaction. Existing reset/verification actions retain their existing audit events. No password, token or credential is put into these new audit payloads.

## Permissions and scope boundaries

The seeder adds permission entries for Dashboard, Donations, Donors, Grants, Grant assignments, Reports, Financial records (`finance`), Users, Roles, and Settings, including view/create/edit/delete/manage entries, plus `reports.export`. The suggested Grant Manager, Donation Manager, Reporting Officer, and Viewer roles are added to the role catalogue; on **fresh** seeds they receive only conservative starter grants. Fresh-seeded Administrator receives user view/create/edit and role-view grants, **not** authority to assign roles or change role grants. Existing installations do not have grants silently added to their existing roles; a Super Admin must review and explicitly assign new permissions.

**A catalogue entry is not an implemented route.** Grants and grant-assignment permissions are reserved placeholders: no grant records, assignment UI, or grant processing exists, and these permissions grant no grant workflow. Likewise generic donation/donor/finance `create/edit/delete` entries do not authorize deletion or rewriting of historical financial records; only the existing review/finance routes operate. Existing `reports.view` continues to guard report exports (the new `reports.export` is not yet an independent export gate). The legacy `users.manage`, `settings.manage`, `donations.manage`, and `finance.manage` permissions remain effective for their existing routes. Generic `roles.create/edit/delete/manage` catalogue entries do **not** override the Super Admin-only role-write policy.

## Files

**New:** `app/Services/RbacService.php`, `app/Http/Controllers/Admin/RoleController.php`, `database/migrations/0014_v3_rbac_defaults.sql`, `resources/views/admin/roles/index.php`, `resources/views/admin/roles/form.php`, `resources/views/admin/users/create.php`, `tests/v3_user_roles_part7_test.php`, this report.

**Changed:** `app/Http/Controllers/Admin/UserController.php`, `app/Http/Middleware/PermissionMiddleware.php`, `app/Repositories/UserRepository.php`, `app/Services/AuthService.php`, `database/seeders/RoleSeeder.php`, `database/seeders/SiteContentSeeder.php`, `routes/admin.php`, `resources/views/admin/users/index.php`, `resources/views/admin/users/show.php`, `resources/views/layouts/admin.php`.

**Deleted:** none.

## Database changes

- Migration `0014_v3_rbac_defaults.sql` adds **no new table or column**. Existing `roles`, `permissions`, `user_roles`, `role_permissions`, `site_settings`, password-reset tokens and audit tables are reused. The migration records `rbac.defaults_seeded` on already-populated installations to avoid reseeding a previously revoked grant. Fresh installations receive that marker after their first permission seed.
- `RoleSeeder` populates the suggested roles once and records `rbac.role_catalog_seeded` so a deliberately deleted role is not resurrected on a future seed. `SiteContentSeeder` expands the permission catalogue and first-seed role defaults, but never re-adds removed role grants on subsequent runs. On an existing install, migrate **then** run the normal seed command; review/assign new role grants explicitly. No real user, donation, grant, or financial records were changed for this work. Integration tests wipe **only** the explicitly named disposable `*_test` database.

## Verification

- `tests/v3_user_roles_part7_test.php`: **41 passed** against `almarah_v3part4_test`. Includes HTTP and service role create/edit/delete, grant add/remove, user create/edit, role assignment, password-reset token initiation, permission denials, custom-role access, disabled and pending login, Super Admin takeover/last-account protection, rejected unknown grants, audit events, and seed non-restoration.
- `tests/v3_admin_navigation_part2_test.php`: **258 passed** after the changes; existing admin routes and navigation remain accessible as authorized.
- `tests/v3_donation_form_part4_test.php`: **21 passed**; `tests/v3_currency_part5_test.php`: **27 passed** during this Part 7 work.
- PHP syntax checks on changed/new PHP and `git diff --check` passed. A Part 5 browser regression was attempted but **not run** in this environment because the Node `playwright` module is not installed; no browser pass is claimed for Part 7.

## Outstanding / deployment checks

- Confirm outbound email templates/worker and setup/reset delivery in the target environment. The integration test confirms creation of reset tokens, not actual mailbox delivery.
- Review the permission catalogue with the organization: many generic actions are intentionally reserved, and role-write delegation is intentionally restricted to Super Admin to prevent privilege escalation. Grant management needs its own approved implementation and real data before any grant permission can be considered operational.
- Run the migration and seeds on a backed-up staging copy and verify at least one active Super Admin and the desired role assignments before production rollout. No live deployment or browser acceptance test was performed here.
- Part 6 payment integration remains blocked by missing Stripe account country/currency details, approved bank API documentation, and test access. Bank-transfer fallback remains in place. No Part 6 payment tests or operational payment claims are made.

**Paused before Part 8 pending approval.**
