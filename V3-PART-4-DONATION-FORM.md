# V3 Part 4 — Donation form restoration

**Working copy:** local `v2-delivery` (base HEAD `6be9d92`); earlier uncommitted V3 Part 2 work was preserved. **Scope:** donation entry, transfer reporting, and donor-facing payment results only. No production database was accessed.

## V1 comparison and decision

The repository was initially shallow; after fetching the full history, the original **`v1` commit is `ee39ca6`**, immediately before V2 commit `b470a6a`. The `/donate` form in `ee39ca6:resources/views/public/donate/index.php` already had a purpose selector, one-time/monthly choice, preset/custom PKR amount, donor name/email, optional contact/location/message, and online/bank choices. The subsequent V2 diff to this specific template added the acknowledgment/referral fields partial and a transfer-report link. The old WordPress donation page also contained plugin shortcodes for a currency switcher; those are **not** evidence of a working foreign-currency gateway on this PHP site. The separate V1 fundraiser checkout template was inspected and not replaced.

The restored form retains V1's layout, branding, cards and fundamental fields. Full name and email remain visible for online payment; phone remains optional. Frequency and additional location/message/anonymity/acknowledgment/referral fields are available in accessible, collapsed details rather than forming extra default steps. Existing conditional acknowledgment/referral validation and storage remain in place. A monthly pledge remains an intention, not recurring card billing.

## Currency, payment and safety behavior

- Explicit currency selector: PKR plus currencies of **published** bank accounts from the existing CMS (supported offline currency codes only). Presets are shown for PKR; custom amount is available. No automatic currency conversion is claimed.
- Online card gateways are offered only for PKR **when enabled and configured**. Non-PKR selection hides/disables them; the server rejects any forged foreign-currency online submission. Bank accounts shown on `/donate` match the selected currency; hidden/unpublished accounts are not exposed. Choosing bank transfer reveals account details and never marks a donation paid or invokes a card gateway.
- `/donate/transfer` now explicitly records the selected currency via its existing review service. Public foreign-currency reports are restricted to published bank currencies; legacy PKR reports remain possible. The resulting gift remains `pending`/`submitted`, with no verified receipt until authorized staff review. The report form and main online form use expiring, one-use, session-bound attempt tokens, plus disabled submit buttons in the browser; replay of the same form cannot create a second record. This does not deduplicate separate deliberate donations from different forms/sessions or replace the existing server rate limits.
- Amount, donor details, CSRF, gateway availability, and monetary limits remain server-validated. The current gateway registration, pending donation/transaction persistence, return callbacks, and fundraiser checkout contract were not replaced. A gateway handoff is **not** a successful payment.
- Donor-facing status now describes pending transfer reports as staff verification, not bank confirmation; failed results retry at the working main-site `/donate` route. Historical `completed` rows without `verification_status=verified` are labeled unverified on the result page: no authorized receipt control or confirmed-payment claim is displayed. Underlying historical rows, statuses and reporting totals were not changed. The unapproved tax-deductibility claim was removed from the form.

## Files

**Modified:** `app/Http/Controllers/Public/DonateController.php`, `app/Http/Controllers/Public/OfflineDonationController.php`, `app/Http/Controllers/Public/PaymentController.php`, `fundraise-submit.php`, `resources/views/public/donate/index.php`, `resources/views/public/donate/transfer.php`, `resources/views/public/donate/status.php`, `resources/views/partials/payment-result.php`, `assets/css/site.css`, `assets/js/site.js`.

**Added:** `assets/js/donate.js`, `tests/v3_donation_form_part4_test.php`, `tests/v3_donation_form_browser_part4.cjs`, `tests/v3_donation_online_browser_part4.cjs`, this report. **Deleted:** none. Earlier Part 2 administrative edits and all prior reports remain untouched by Part 4.

**Database:** no schema changes, migrations, or changes to production/historical data. Tests reset only disposable `almarah_v3part4_test` and created synthetic donors/gifts in that test database.

## Verification

- New Part 4 PHP integration suite: **21 checks** (V1-like form, published/hidden currency selection, separate form tokens, guest USD transfer and exact minor units, pending/staff review, replay block, forged currency rejection, recorded-status behavior, legacy vs verified receipts, failed-payment retry URL).
- New live Chromium/Playwright browser tests against a local disposable test server: **46 checks** with no gateway configured (desktop, 390px, 320px, visible/hidden controls, currency/bank matching, bank selection without online payment, guest transfer and replay, JS errors, horizontal overflow); **15 checks** with deliberately unreachable local test gateway settings (PKR-only online option, custom/minimum amount, required name/email, forged USD rejection, gateway failure and token replay). Dummy credentials were used only for the disposable test process; no live provider was contacted.
- Existing `tests/donation_review_test.php`: **33 assertions passed**. Existing `tests/qa_payment_part7_test.php`: passed synthetic unverified/verified provider response, duplicate callback and verified-report exclusion checks. PHP syntax checks, JS syntax checks and `git diff --check` passed.

## Remaining limits / pause

A genuine card authorization, hosted payment page, provider callback, real-bank failure or successful live settlement **cannot** be certified without configured merchant sandbox credentials and provider access; those paths need a staging checkout with a real sandbox provider before deployment. The test gateway intentionally could not register a payment. Existing historical records were not reverified or rewritten. The independently documented Part 3 report-page error still lacks a reproducible URL/error, and grant reports remain blocked without grant records; see `V3-PART-3-REPORTING.md`.

**Part 4 is delivered; no further V3 part was started.**
