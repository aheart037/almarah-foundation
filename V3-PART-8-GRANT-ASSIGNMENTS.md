# V3 Part 8 — Grant Management and User Assignments

**Status:** Implemented locally on `v2-delivery`. Part 9 has not been started. Part 6 payment integrations remain independently blocked.

## Scope decision and existing data

An audit of the repository found **no existing grant table, grant records, grant assignment table or grant-management route**. The requested clarification selected **a minimal new grant register**. Migration `0015_v3_grants_assignments.sql` therefore adds new tables instead of replacing or guessing an existing structure. It does not invent grants, financial awards, disbursements, beneficiaries or historic assignments. The grant register contains only a unique reference, title, optional description, draft/active/closed status, creator and timestamps. There is no grant deletion workflow or financial-payment feature.

## Delivered

- **Grant administration:** `/admin/grants` lists grants; authorized staff can create, view details and edit grant records. The grant detail page shows its assigned users and assignment history when the viewer has the separate assignment-view/manage permission. A user detail page shows that user's assigned grants when the viewer has user, grant and assignment visibility.
- **Assignment operations:** From a grant detail page, assign one or several active users; from a user detail page, assign that user to one or several non-closed grants. Update an assignment's active/on-hold/completed status, responsibilities and optional notes. Remove an assignment without deleting its row; reassigning the same user/grant restores that unique pair. A Grant Assignment Manager can atomically change the assignee, retaining the old user as removed history. A duplicate active pair is rejected and a failed batch rolls back in full. Closing a grant prevents new assignments; suspended users cannot be newly assigned.
- **Stored assignment data:** `grant_id`, `user_id`, `assigned_at`, `assigned_by`, status, responsibilities, notes and update timestamp. A separate append-only `grant_assignment_events` table records assignment, edit, removal, restoration and transfer snapshots, including previous responsibilities/notes. Required audit events are also written transactionally. The `(grant_id,user_id)` database unique key prevents duplicate pairs, including under concurrent requests. Assignments never copy or duplicate grant records.
- **Authorization:** Server-side routes require `grants.view` for grant lists/details, `grants.create|manage` to create and `grants.edit|manage` **plus** `grants.view` to edit. Assignment writes require `grants.view` and the relevant `grant_assignments.create|edit|delete|manage` permission. Assignment identities and history are not rendered without `grant_assignments.view|manage`. The service independently rechecks the active actor's database grants for writes; an unprivileged direct service call fails. Access is currently *role-wide* across the grant register, not per-grant ownership; no per-grant visibility policy or beneficiary data was supplied. Super Admin retains its existing bypass.
- **Role defaults:** On a fresh initial seed, Grant Manager receives grant view/create/edit, assignment view/manage and user-view grants. Existing installations are **not silently granted new access**: a Super Admin must review and assign the Part 8 permissions in the Part 7 role editor. Other roles retain their current grants.

## Files

**New:** `database/migrations/0015_v3_grants_assignments.sql`, `app/Services/GrantService.php`, `app/Http/Controllers/Admin/GrantController.php`, `resources/views/admin/grants/index.php`, `resources/views/admin/grants/form.php`, `resources/views/admin/grants/show.php`, `tests/v3_grants_part8_test.php`, this report.

**Changed:** `routes/admin.php`, `app/Http/Controllers/Admin/UserController.php`, `database/seeders/SiteContentSeeder.php`, `resources/views/admin/users/show.php`, `resources/views/layouts/admin.php`.

**Deleted:** none.

## Database changes and preservation

`0015` creates `grants`, `grant_user_assignments` and `grant_assignment_events` with foreign keys and indexes. Existing user/role/donation/financial tables are **not altered**, and no historical financial rows are edited. User and grant records are referenced by IDs rather than copied. Removal is a status transition, not a destructive delete. The migrations and seeders were exercised only against an explicitly named disposable `*_test` database during testing; production migration and deployment have not been performed.

## Tests performed

- `tests/v3_grants_part8_test.php`: **43 checks passed** on `almarah_v3part4_test` (disposable): grant creation/edit, single and bulk user assignment, multiple grants per user, updates, removal and reassignment, change of assignee, retained event history, unique-key and service-level duplicate rejection, atomic bulk rollback, closed-grant/suspended-user denial, grant/assignment visibility, route and service permission enforcement, audit events and no duplicated grant records.
- Regression: Part 7 RBAC integration **41 passed**; Part 2 admin navigation and authorization **258 passed**; Part 4 donation form integration **21 passed**; Part 5 currency integration **27 passed**.
- PHP syntax checks on touched PHP and `git diff --check` passed. **No Part 8 browser or production acceptance test** was run; the workspace's Playwright dependency was unavailable in this environment. These tests do not establish production mail, payment or grant data.

## Outstanding checks and limitations

- Verify the migration, permissions, templates and usability on a backed-up staging copy before rollout. The multi-select controls currently load all active users/open grants rather than providing search/pagination; this may need improvement for a large staff/grant register.
- Clarify any organization-specific per-grant confidentiality/ownership rules before introducing restricted grants; the current model grants global register visibility to roles with `grants.view`. No monetary grant allocations, disbursement approvals, external grant source sync or grant reporting were specified or added.
- Part 6 remains blocked on the previously requested Stripe account country/currency details, approved UBL/Meezan API documentation and test access. No Part 6 implementation or operational gateway claim is made.

**Paused before Part 9 pending approval.**
