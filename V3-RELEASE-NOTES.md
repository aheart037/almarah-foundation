# Almarah Foundation V3 — release notes

**Version:** V3 · **Release name:** Almarah Foundation V3 · **Prepared:** 4 November 2026 · **Status:** packaged local QA build, **NOT production-certified**. This archive is a real application package, not evidence that online payment obligations are met. V1 remains in Git history (`ee39ca6 v1`); V2 history and documents remain untouched. The archive excludes Git history and does not overwrite old releases.

## 1. New features

- Admin sidebar/navigation with route-aware active states and responsive drawer; reports catalogue and filtered, permission-aware exports.
- Simplified donation form, bank-transfer fallback and submission replay protection; payable-only currency selection using published bank accounts and PKR-only configured online options.
- Server-side country hint with manual override, currency names/flags and cached informational exchange-rate estimates; safe fallback when external services fail.
- Users, editable roles and permissions, least-privilege admin guards; minimal grant register (title, unique reference, description, status) with user assignments, without invented financial data.
- Security hardening for financial totals, verified-vs-legacy payment classification, migration preflights and payment-history/reconciliation links; Part 10 responsive/CSRF/permission regression tests.

## 2. Fixed bugs

- Admin links/active state and reports navigation; donation amount/currency synchronization, stale conversion, duplicate form submission and gateway ineligibility cases are covered by regression suites. Older `completed` entries are not silently treated as verified.
- Part 10 corrected a browser test that assumed one PKR bank card, repaired the seeded fixture used for a gateway smoke check, aligned a CSRF test with HTTP 419, and fixed 53 px overflow on `/admin/users/create` at 320 px by allowing its submit button to wrap. See `V3-FINAL-QA-REPORT.md` for failures and reruns.

## 3. Modified files

Existing PHP routes/controllers, services, migration runner, seeders, templates and frontend assets from V2 were updated. The **complete path-by-path inventory** of tracked modified files appears in the appendix below (the working tree includes all approved V3 parts, not only Part 11). No V1/V2 backup or Git commit was modified by packaging.

## 4. New files

New currency/geolocation/FX, RBAC and grants services/controllers/templates; SQL migrations; browser/PHP QA tests; V3 phase reports and this release package's `VERSION`, `.env.example`, `config.dist.php` and `README_V3.md`. Exact new paths appear in the appendix. Bundled third-party runtime `vendor/` remains available in the ZIP; `node_modules/` does not.

## 5. Database changes

The shipped migrations are `database/migrations/0001_*.sql` through `0017_v3_reconciliation_links.sql`. V3 additions include RBAC defaults, grant assignments/register, foreign keys for payment history/reconciliation and earlier V3 donation/reporting structures. Inspect the ordered migration files and `V3-PART-9-SECURITY.md` for the exact SQL; no schema or financial data was changed in production. Local fresh migration and seed succeeded, and a second migration run found nothing pending.

## 6. Migration instructions

Back up files and database first; run preflight on a staging clone with the same schema and data; inspect legacy payments and foreign-key integrity; provision storage and check DB permissions. Run `php bin/console migrate` **sequentially** with the destination environment, then `php bin/console db:seed` where appropriate, verify status and the app/finance reports. Do not use destructive `tests/*_test.php` on real data. MySQL DDL is not transactionally rolled back by default: rollback requires coordinated file restore plus the pre-upgrade database snapshot. Never edit historical transaction amounts/statuses to make a migration pass.

## 7. Payment gateway setup

Gateway visibility requires an enabled **and configured** provider; payment choice is enforced server-side and only PKR online methods are in scope here. Bank transfer remains the fallback, with only published currency-matched accounts shown. Gateway settings can be overridden from protected admin DB settings; **disabling via environment variables alone may not disable an already-enabled DB row**. Verify both before deployment. Keep all online gateways off pending approval and real acceptance; fake local test credentials are not valid.

## 8. Stripe configuration

**Not available in this release.** There is no Stripe SDK, Checkout/PaymentIntent integration, webhook handler, signature verification, merchant account or `STRIPE_*` env consumption. Adding environment variables cannot make it work. Before a future implementation, confirm the organization's registered-account-country eligibility, signed webhook contract, idempotency/refunds/currency and delayed events, then complete real sandbox and live acceptance. No claim of non-PKR card acceptance is made.

## 9. UBL configuration

The existing UBL EPG adapter uses the legacy internal code **`etisalat`**. Template names: `ETISALAT_ENABLED`, `ETISALAT_ENVIRONMENT`, `ETISALAT_SANDBOX_URL`, `ETISALAT_LIVE_URL`, `ETISALAT_PORT`, `ETISALAT_CUSTOMER`, `ETISALAT_USERNAME`, `ETISALAT_PASSWORD`, `ETISALAT_STORE`, `ETISALAT_TERMINAL`, `ETISALAT_CURRENCY`, `ETISALAT_ALLOWED_HOSTS`; additional options are documented in `.env.example`/`config/payments.php`. Leave disabled. Obtain approved UBL merchant docs and endpoint allowlists; configure sandbox credentials in private environment/admin secret storage, then prove registration, verified callbacks/finalization, cancellation, failure, replay, refunds and PKR ledger outcomes with bank approval before enabling. No such external acceptance occurred in QA.

## 10. Meezan configuration

Template names: `MEEZAN_ENABLED`, `MEEZAN_ENVIRONMENT`, `MEEZAN_API_URL` (or sandbox/live URLs), `MEEZAN_RETURN_URL`, `MEEZAN_USERNAME`, `MEEZAN_PASSWORD`, `MEEZAN_MERCHANT_ID`, `MEEZAN_CURRENCY_CODE`, `MEEZAN_CURRENCY_NAME`, `MEEZAN_ALLOWED_HOSTS`; see `.env.example`/`config/payments.php`. Leave disabled. The existing adapter registers and checks order status on return; the local browser test used an **unreachable** endpoint and proved failure/replay handling only. Require approved Meezan product/API docs, real sandbox merchant details, bank-approved return URL and success/failure/cancel/status/retry acceptance before live use.

## 11. Geo-IP configuration

Current public HTTPS endpoint: `https://ipwho.is/` (no API key used). The server transmits eligible visitor IPs, caches country results per session and falls back to PKR without preventing donation. `DONATION_GEO_DISABLE_REMOTE=1` disables network lookup; a manual currency selection remains authoritative. Verify proxy/IP attribution, provider quota/terms, failure behavior and visitor-IP disclosure on the actual host before enabling remote calls. Local QA used deterministic injected responses/disabled remote service, not live country mapping.

## 12. Exchange-rate API configuration

Current public HTTPS endpoint: `https://open.er-api.com/v6/latest/PKR` (no API key used). `DONATION_RATES_CACHE_FILE` sets writable JSON cache location; `DONATION_RATES_DISABLE_REMOTE=1` disables the feed. Verify writable protected cache, quotas/terms and outage behavior. Estimates are **informational only**, never settlement amounts, provider charges or reconciled ledger exchange rates. Local browser checks used a deterministic cache.

## 13. Environment variables

Use `.env.example` for the **full named template and descriptions**, without secrets. Core: `APP_URL`, `APP_ENV`, `APP_DEBUG`, `APP_KEY`, `SESSION_SECURE_COOKIE`, `SETUP_LOCK`; DB: `DB_*`; SMTP: `MAIL_*`, `SMTP_*`; gateway: `MEEZAN_*`, `ETISALAT_*`; optional fundraise bridge: `FUNDRAISE_*`; geolocation/FX: `DONATION_GEO_DISABLE_REMOTE`, `DONATION_RATES_DISABLE_REMOTE`, `DONATION_RATES_CACHE_FILE`; backups: `BACKUP_*`. `config.dist.php` supports basic cPanel-style settings as an alternative private `config.php`. `APP_NAME` remains visible organization branding; version and release name are declared in `config/app.php` and `VERSION`. Store real values outside the archive and source control. No `STRIPE_*` variable is supported.

## 14. Installation instructions

Read `README_V3.md`; provision PHP 8.2+, required extensions, MySQL and HTTPS; extract the ZIP into a **new directory** (one top-level `Almarah-Foundation-V3/` folder); configure private environment or copy `config.dist.php` to `config.php`; set an APP_KEY and nonblank setup lock; run migrations/seed on a fresh isolated database or the guarded installer; check mail, storage permissions, public routes and local backup/restore. Bundled PHPMailer runtime is present; verify third-party compatibility on the destination. Do not use the legacy V2 ZIP filename or legacy README deployment instructions verbatim.

## 15. Deployment instructions

**Deployment is blocked pending the approvals below.** For a future approved window: stage the exact tested ZIP, back up existing app/DB, retain V1/V2 directory/history, compare and preflight migration SQL, test restoration, apply sequential migrations with provider gateways disabled, run route/security/report smoke on staging, validate HTTPS/private paths, configure monitoring and schedule rollback. Only switch traffic after operations and payment owners sign off. Preserve historical payment records and currency separation. Do not claim production success from a local ZIP.

## 16. Known limitations

No Stripe or verified non-PKR online gateway. UBL/Meezan unvalidated with approved merchant sandbox; no real-provider verified success, cancellation, failure or webhook acceptance. Remote Geo-IP and FX, production dataset/report URLs, real grant data, deployment-specific permissions/cron/backup and production migration/restore were not tested. Composer CLI and PHPUnit executable were unavailable in local QA; no npm build is defined. The earlier security report records additional residual risks. Local suites: 18/18 PHP, 6/6 browser scripts (430 browser checks), 157 PHP lint and 11 JS syntax files passed; see `V3-FINAL-QA-REPORT.md` for precise scope.

## 17. External approvals required

Payment/finance owners and UBL/Meezan/Stripe merchant providers must approve account-country eligibility, documentation, test credentials via secure channel, verified callback/webhook rules and acceptance results. Legal/privacy owner must approve Geo-IP IP-sharing disclosure and public API terms. Operations/security owners must approve backed-up migration, host hardening, production environment/secrets, backups/restore, alerting and change window. Product/data owners must approve real reporting/grant requirements. **Until then this ZIP is a non-production release artifact; critical requested payment functionality remains unresolved.**

## Appendix — V3 working-tree source inventory

The paths below are a snapshot of the uncommitted V3 working tree and do not imply that each file was changed in Part 11. Generated archive, private configuration and volatile files are not included in this list. The generated `V3-RELEASE-MANIFEST.sha256` is included in the archive.

**Modified tracked files (41):**

- `README.md`
- `app/Admin/ResourceRegistry.php`
- `app/Database/Migrator.php`
- `app/Http/Controllers/Admin/DonationController.php`
- `app/Http/Controllers/Admin/FinanceController.php`
- `app/Http/Controllers/Admin/UserController.php`
- `app/Http/Controllers/Public/DonateController.php`
- `app/Http/Controllers/Public/OfflineDonationController.php`
- `app/Http/Controllers/Public/PaymentController.php`
- `app/Http/Middleware/PermissionMiddleware.php`
- `app/Repositories/UserRepository.php`
- `app/Services/AuthService.php`
- `app/Services/DonationReviewService.php`
- `app/Services/FinanceService.php`
- `assets/css/site.css`
- `assets/css/style.css`
- `assets/js/site.js`
- `bootstrap/app.php`
- `config/app.php`
- `database/seeders/RoleSeeder.php`
- `database/seeders/SiteContentSeeder.php`
- `fundraise-submit.php`
- `resources/views/admin/dashboard.php`
- `resources/views/admin/donations/index.php`
- `resources/views/admin/email/queue.php`
- `resources/views/admin/email/templates.php`
- `resources/views/admin/finance/adjust.php`
- `resources/views/admin/finance/index.php`
- `resources/views/admin/finance/offline.php`
- `resources/views/admin/finance/run.php`
- `resources/views/admin/reports/show.php`
- `resources/views/admin/settings/index.php`
- `resources/views/admin/users/index.php`
- `resources/views/admin/users/show.php`
- `resources/views/layouts/admin.php`
- `resources/views/partials/payment-result.php`
- `resources/views/public/donate/index.php`
- `resources/views/public/donate/status.php`
- `resources/views/public/donate/transfer.php`
- `routes/admin.php`
- `routes/web.php`

**New release/implementation/test files (47):**

- `.env.example`
- `.gitignore`
- `README_V3.md`
- `V3-FINAL-QA-REPORT.md`
- `V3-PART-2-ADMIN-NAVIGATION.md`
- `V3-PART-3-REPORTING.md`
- `V3-PART-4-DONATION-FORM.md`
- `V3-PART-5-CURRENCY-SYSTEM.md`
- `V3-PART-7-USER-ROLES.md`
- `V3-PART-8-GRANT-ASSIGNMENTS.md`
- `V3-PART-9-SECURITY.md`
- `V3-PROJECT-AUDIT.md`
- `V3-RELEASE-MANIFEST.sha256`
- `V3-RELEASE-NOTES.md`
- `VERSION`
- `app/Http/Controllers/Admin/GrantController.php`
- `app/Http/Controllers/Admin/RoleController.php`
- `app/Services/DonationCurrencies.php`
- `app/Services/DonationExchangeRates.php`
- `app/Services/GeoCurrencyService.php`
- `app/Services/GrantService.php`
- `app/Services/RbacService.php`
- `assets/js/donate.js`
- `config.dist.php`
- `database/migrations/0014_v3_rbac_defaults.sql`
- `database/migrations/0015_v3_grants_assignments.sql`
- `database/migrations/0016_v3_payment_history_constraints.sql`
- `database/migrations/0017_v3_reconciliation_links.sql`
- `resources/views/admin/grants/form.php`
- `resources/views/admin/grants/index.php`
- `resources/views/admin/grants/show.php`
- `resources/views/admin/roles/form.php`
- `resources/views/admin/roles/index.php`
- `resources/views/admin/users/create.php`
- `tests/v3_admin_navigation_browser_part2.cjs`
- `tests/v3_admin_navigation_part2_test.php`
- `tests/v3_currency_browser_part5.cjs`
- `tests/v3_currency_part5_test.php`
- `tests/v3_donation_form_browser_part4.cjs`
- `tests/v3_donation_form_part4_test.php`
- `tests/v3_donation_online_browser_part4.cjs`
- `tests/v3_final_browser_part10.cjs`
- `tests/v3_grants_part8_test.php`
- `tests/v3_migrator_part9_test.php`
- `tests/v3_security_part9_test.php`
- `tests/v3_user_roles_part7_test.php`
- `tools/build_v3_release.py`
