# Donation Management (Parts 1 + 2) - installation

1. Back up the database and the site files.
2. Extract this ZIP over the existing project root (folder structure is preserved; it only adds/overwrites the listed files).
3. Run migrations: `php bin/console migrate` (applies `0019_donation_management_part1.sql` and `0020_donation_certificates.sql`; both are idempotent). Or run those two SQL files manually in order.
4. Re-run the permission/role seeder if your roles were customised: `php bin/console db:seed` (donations.record / donations.verify are also inserted by migration 0019).
5. Check certificate details in table `site_settings` (keys `certificate.*`): NTN, approval text, approval valid from/until, address, `certificate.signatory_name` (blank by default). Verify them against your FBR approval order.
6. Make sure `storage/private/` is writable by the web server (certificates are saved in `storage/private/certificates/`, never web-served).
7. Mail: uses the existing SMTP settings and queue. Keep `php bin/console mail:work` on cron; optional cron: `php bin/console certificates:issue` (retries failed certificates/emails).
8. Tests (disposable DB whose name contains "test"):
   `TEST_DB_NAME=almarah_test php tests/donation_management_part1_test.php`
   `TEST_DB_NAME=almarah_test php tests/donation_certificates_part2_test.php`
