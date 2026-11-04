# V3 Part 9 — Database and Security Review

**Scope:** Local review and hardening of the V3 work actually present on `v2-delivery` (Parts 1–5, 7–8). Part 6 has **no delivered implementation**; this is not a production security certification or live-gateway verification. No Part 10 work was started.

## Findings and changes made in Part 9

### Database relationships, history and migrations

- Added version-controlled, **non-data-changing** `0016_v3_payment_history_constraints.sql`: historical payment transactions, callbacks and refunds now restrict deletion of their referenced donation/transaction instead of cascading or clearing the relationship. Added an indexed FK from `financial_adjustments.payment_transaction_id` to `payment_transactions.id`. Existing financial adjustment rows are not rewritten.
- Added `0017_v3_reconciliation_links.sql`: indexed FKs from reconciliation items to donation, payment transaction and resolver; reconciliation runs with items cannot be deleted by cascade. A removed user may be set NULL as resolver, but the item and its text remain.
- The Part 8 `(grant_id,user_id)` unique key, grant/user/event FKs, grant reference uniqueness and assignment indexes were verified. Assignment removal preserves the row and immutable event snapshots. The pre-existing donation/payment amount fields and financial rows were not migrated or updated during this Part 9 work.
- `Migrator` now executes SQL statements **once**, rather than trying a bulk execution and then replaying already-applied DDL after a partial failure. Before migrations 0016 and 0017, it refuses known orphan or cross-donation financial/reconciliation references with an actionable error instead of silently deleting or reparenting historical records. Fresh migration-runner rehearsal applied **17 migrations once** and a repeat run applied none.

### Financial integrity and audit

- `FinanceService::adjust()` now locks the donation row and performs the change, financial-adjustment ledger insert and **required audit write in one database transaction**. It rechecks an active `finance.manage` or Super Admin actor, validates reason/evidence lengths, requires evidence for refunds/amount corrections and rejects a payment-transaction ID belonging to another donation.
- Manual adjustments cannot mark an online payment failed, change an online gateway amount, or record an online refund without provider confirmation. Historical/legacy, verified, completed and refunded amounts cannot be silently corrected. Only independently verified offline donations are eligible for manual completion. The adjustment UI hides inapplicable actions. Manual amount and refund inputs are parsed as strict decimal minor units—no float rounding, exponent syntax, or extra fractional digits.
- Reconciliation-item resolution now locks the item, checks active finance authorization, validates its note and requires an audit write in the same transaction. Finance CSV downloads require an audit entry; user-controlled CSV cells are neutralized against spreadsheet formula execution.
- Finance dashboard **confirmed** totals now contain only independently verified PKR donations. Verified foreign-currency totals appear in separate currency rows; status rows are separated by currency. Trend and reconciliation monetary cards are explicitly PKR-only and verified counts exclude legacy completed rows. The detailed CSV still lists each donation with its actual currency; it does not sum different currencies.
- Public reference-only payment pages no longer print a donor's free-text message. Name/email remain masked, not fully displayed; this does **not** make a reference URL a private authenticated receipt.

### Application access and payment review

- Frontend constraints and server-side validation were inspected for donations, roles, users, grants, assignments and manual finance actions. The Part 9 test specifically rejects malformed decimal input even when a POST bypasses HTML controls. Admin mutating routes use authentication, named permission checks and CSRF; Part 7–8 role/assignment services also validate privileges server-side. Disabled accounts are denied on subsequent requests.
- Bank return/callback routes are intentionally public and CSRF-exempt because provider requests do not carry a browser session. The code resolves a locally stored payment using signed state or matching stored bank-issued identifiers, rejects mismatches/expired state, uses a payment lock, asks the provider server-to-server for the authoritative result, and does not trust browser success parameters. Synthetic payment regression passed for unverified, verified and duplicate responses. **No real Stripe webhook exists here; no live Meezan/UBL callback or webhook was exercised.** This review does not assert operational gateway verification.
- Gateway/SMTP secrets are read from environment or encrypted settings; settings pages do not echo stored passwords. `APP_KEY` protection, encryption/decryption behavior, HTTPS, host allowlists and web-server denial of `storage/` and config files still require deployment-specific verification. No credentials were added to the repository or report.

## Files changed in Part 9

**New:** `database/migrations/0016_v3_payment_history_constraints.sql`, `database/migrations/0017_v3_reconciliation_links.sql`, `tests/v3_security_part9_test.php`, `tests/v3_migrator_part9_test.php`, this report.

**Changed:** `app/Database/Migrator.php`, `app/Services/FinanceService.php`, `app/Http/Controllers/Admin/FinanceController.php`, `app/Http/Controllers/Admin/DonationController.php`, `resources/views/admin/finance/index.php`, `resources/views/admin/finance/run.php`, `resources/views/admin/finance/adjust.php`, `resources/views/partials/payment-result.php`.

**Deleted:** none. Parts 2–5 and 7–8 have earlier uncommitted changes in this shared working tree; they were not claimed as newly written in Part 9.

## Regression evidence

All destructive database tests below ran **sequentially** against the explicitly named disposable `almarah_v3part4_test`; they did not run against a production database:

| Test | Result |
|---|---|
| New Part 9 security/integrity suite | **31 checks passed**: FK deletion/orphan denial, immutable history, unauthorized/historical/gateway adjustment denial, currency separation, CSV formula protection and audit, malformed amount rejection, CSRF and public-message privacy |
| New migration-runner rehearsal | **17 migrations** applied once; no pending migrations on re-run |
| Historical upgrade rehearsal (`qa_migrations_part7_test.php`) | Passed; prior completed donation retained and classified legacy |
| Donation review suite | **33 assertions passed** |
| Part 8 grants/assignments | **43 checks passed** |
| Part 7 users/roles | **41 checks passed** |
| Part 2 navigation/authorization | **258 assertions passed** |
| Part 4 donation form | **21 checks passed** |
| Part 5 currency | **27 checks passed** |
| Synthetic payment-state regression | Passed; **not** a real bank transaction |
| Reporting | **190 passed, 0 failed** |
| Public/admin/login route smoke, forms, report exports | **40**, **37**, **198** assertions passed respectively |

PHP syntax checks on touched files and `git diff --check` passed. Playwright-based V3 browser regressions were **not** run in this workspace because the Node `playwright` module was unavailable. No staging, production, external provider, real callback, penetration or concurrent-load acceptance test was performed.

## Deployment preflight and rollback strategy

1. **Back up and verify restore** of the live database and encrypted settings/APP_KEY; run 0016–0017 on a staging clone first. Check for orphan/mismatched links *before* migration:
   ```sql
   SELECT fa.id FROM financial_adjustments fa LEFT JOIN payment_transactions pt ON pt.id=fa.payment_transaction_id
   WHERE fa.payment_transaction_id IS NOT NULL AND (pt.id IS NULL OR fa.donation_id<>pt.donation_id);
   SELECT ri.id FROM reconciliation_items ri LEFT JOIN donations d ON d.id=ri.donation_id
   LEFT JOIN payment_transactions pt ON pt.id=ri.payment_transaction_id LEFT JOIN users u ON u.id=ri.resolved_by
   WHERE (ri.donation_id IS NOT NULL AND d.id IS NULL) OR (ri.payment_transaction_id IS NOT NULL AND pt.id IS NULL)
      OR (ri.resolved_by IS NOT NULL AND u.id IS NULL)
      OR (pt.id IS NOT NULL AND ri.donation_id IS NOT NULL AND pt.donation_id<>ri.donation_id);
   ```
   Stop and investigate every returned row; do **not** silently delete or fabricate a parent. The application migrator performs the same checks for 0016/0017.
2. The project's SQL migrator has **no automated `down` or transactional DDL rollback**. MySQL `ALTER TABLE` can commit even if a later statement fails. After a partial failure, leave traffic in maintenance, inspect `information_schema` and the `migrations` table, and repair **forward** from the reviewed migration or restore the verified backup; never blindly re-run a partially applied non-idempotent ALTER. Reversing 0016/0017 foreign keys would weaken history protection and requires a separately reviewed change. Do not drop the Part 8 grant tables if they contain records; export/retain them instead.
3. Compare pre/post row counts and representative IDs/statuses for donations, payment transactions/refunds/callbacks, financial adjustments, grants and assignment events; verify at least one active Super Admin and least-privilege role assignments. Do not deploy with missing bank documentation or unverified payment settings.

## Remaining risks and follow-up priorities

- **High — Part 6 still blocked.** Stripe is not implemented, and UBL/Meezan credentials, approved API guides and live/sandbox callback evidence are missing. Existing gateway return logic has synthetic coverage, not provider certification; callback signatures/webhooks must follow the approved provider contract and be tested with real credentials before activation. Keep the bank-transfer fallback. External refunds cannot be atomically committed with local database/audit writes; reconcile any exception after a provider-side success.
- **High — first-install exposure.** `/setup` is publicly reachable during an uninstalled window and `SETUP_LOCK` is optional by default; on a live host this permits a first-admin takeover if an attacker gets there first. Set a strong deployment-only `SETUP_LOCK` and restrict setup by web-server/network policy until the first authorized administrator exists, then verify the installer is locked. No installer code/config default was changed in this review to avoid silently breaking the existing setup workflow.
- **Medium — public reference privacy.** Donation status URLs are reference-based, not authenticated; masked donor indicators, amount, date and status are visible to anyone holding a reference. Free-text donor messages are now hidden. Consider a separate long-lived, non-guessable status token or donor-authenticated receipt flow with a compatibility plan for existing links; never equate a public status page with private identity verification.
- **Medium — audit limits.** User/role/grant assignment changes and new manual financial adjustments use required audit writes; staged donation-review events are persisted transactionally. Existing provider-driven payment status events and some reconciliation-run events use best-effort audit logging, and a provider refund may succeed before local persistence can complete. Review reconciliation alerts, durable outbox/retry and actor/event coverage before a live payment launch.
- **Medium — data handling and operations.** Grant assignment notes/events and financial evidence references can contain personal data and are not field-encrypted. Limit DB/export access, set retention and backup policies, confirm HTTPS/session cookie behavior and configure access controls for non-Apache deployments. Existing payment/refund and reconciliation tables still rely partly on application checks for cross-donation consistency; migration preflight detects known historical mismatches but is not a DB-level composite invariant.
- **Operational — migrations and browser checks.** No automated rollback or real staging restore drill, production schema inspection, browser suite, concurrency stress test, or provider transaction verification has occurred. Require those before declaring V3 production-ready. Older role/permission grants and historic donation flags should be reviewed explicitly, never silently reclassified.

**Paused before Part 10 pending approval.**
