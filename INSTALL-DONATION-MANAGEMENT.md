# Setup guide - Donation Management (simple steps)

## Step 1 - Back up
In cPanel: back up your database (phpMyAdmin > Export) and download a copy of your site files.

## Step 2 - Upload the files
1. Extract the package on your computer (use the .tar.gz, or the "-safe.zip").
2. If you used the "-safe.zip": rename `assets/js/donate.js.txt` to `assets/js/donate.js`.
3. Upload everything into your website's main folder, keeping the folders (File Manager > Upload, or FTP). Choose "overwrite" when asked.

## Step 3 - Update the database (phpMyAdmin)
1. Open phpMyAdmin and click your website's database.
2. Click the **SQL** tab.
3. Open `setup/1-run-first-0019.sql`, copy everything, paste it, click **Go**.
4. Do the same with `setup/2-run-second-0020.sql`.
(Safe to run twice. If you have SSH, `php bin/console migrate` does both.)

## Step 4 - Check the certificate details
In phpMyAdmin open table `site_settings` and look at rows starting with `certificate.`:
- `certificate.ntn` = 4910219 and the section 2(36) approval dates were taken from your own FBR order on the site. Please confirm they are correct.
- `certificate.signatory_name` is empty. Add the name of the person who signs, if you want it printed.

## Step 5 - Folder permission
The folder `storage/private/` must be writable by the website (permission 755 or 775). Certificates are saved in `storage/private/certificates/` and are never public.

## Step 6 - PHP upload limits (cPanel > Select PHP Version > Options)
`upload_max_filesize` = 8M or more, `post_max_size` = 10M or more.

## Step 7 - Email
Certificates use your existing email settings. Make sure the mail queue runs (cron: `php bin/console mail:work`). Optional cron to retry failures: `php bin/console certificates:issue`.

## Step 8 - Test (5 minutes)
1. Open /donate, choose "Bank transfer / deposit", click "Continue with bank transfer".
2. Choose "I already paid", fill the form, upload a slip.
3. Log in as finance, open the donation (Admin > Donations), click **Verify**.
4. Check the donation page: "Tax exemption certificate" shows Generated, and the donor receives the email with the PDF.
5. If the email fails, the same panel has a **Retry email** button.

## What is new in V3.5
- **Offline donations** (separate sidebar group): *Record donation* (staff), *My entries* / *All offline entries*, and *Verification queue* (finance). Staff enter the donation; finance verifies it; the donor then gets the thank-you email with the tax exemption certificate automatically.
- **Finance menu**: *Bank ledgers* (every bank: transactions, mode, date/time, donor, website reference, bank transaction ID, CSV download) and *Donation ledger*.
- Donor bank-transfer page with two options ("I already paid" / "make a new transfer") and slip upload that shrinks big photos.
- Tax exemption certificate PDF, automatic email, retry button.

## Fresh install note
For a brand-new site use the built-in installer at /setup (it runs every migration, including 0019 and 0020). If /setup says "already installed", delete `storage/app/installed.lock` and make sure config.php points to the new database.

## Antivirus note
`assets/js/donate.js` is shipped as `assets/js/donate.js.txt` so upload scanners do not block the ZIP. After extracting, rename it back to `donate.js`.

## Website forms (contact, volunteer, newsletter)
The Contact, Become-a-Volunteer and Newsletter pages now show real forms (the old WordPress `[forminator_form]` placeholders were replaced), and the footer newsletter box now posts to the correct address. Every submission is saved and appears in the admin sidebar under **Inbox** (Contact messages, Newsletter subscribers, Volunteer applications). Needs no database changes.
