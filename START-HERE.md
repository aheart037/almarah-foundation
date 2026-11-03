# Deploying almarah.org on Hostinger

This guide takes the release ZIP (`almarah-site-release.zip`) to a live site on
a Hostinger shared / Business / Cloud plan. The same steps work on any
cPanel‑style host with Apache or LiteSpeed. Allow about **45 minutes**, plus
DNS time.

> **Before you start — what you need at hand**
> - hPanel login for the almarah.org account
> - the release ZIP (built with `bash deploy/build-site-package.sh`)
> - SMTP details for the address the site sends from (e.g. `fundraise@almarah.org`)
> - Meezan / Etisalat merchant credentials (can be added later; the Donate
>   page shows bank transfer only until a gateway is enabled)
> - a **backup passphrase** you will store somewhere safe (password manager)

---

## 1. PHP version and extensions

hPanel → **Websites → Manage → Advanced → PHP Configuration**

| Setting | Value |
|---|---|
| PHP version | **8.2** or 8.3 (8.4 also works) |
| Extensions (tick) | `pdo_mysql`, `mbstring`, `openssl`, `curl`, `fileinfo`, `zip`, `gd` (or `imagick`), `intl` |
| `upload_max_filesize` | 20M |
| `post_max_size` | 25M |
| `memory_limit` | 256M |
| `max_execution_time` | 120 |
| `display_errors` | Off |
| `allow_url_fopen` | On (used only for fetching nothing external; safe to leave) |

Everything else can stay at the Hostinger defaults. Save, then wait a minute
for the change to apply.

## 2. Database

hPanel → **Databases → Management → Create new database**

- Database name: e.g. `u123456_almarah_site`
- Username: e.g. `u123456_site`
- Password: generate a long one and keep it for step 4

Hostinger's MySQL host is **`localhost`** for sites on the same plan. (If the
panel shows a hostname such as `sqlXXX.main-hosting.eu`, use that instead.)
No tables need to be created — the installer does it.

## 3. Upload the files

hPanel → **Files → File Manager → public_html**

1. If an old WordPress site is still there, first move it into a folder
   outside `public_html` (e.g. `/home/u123456/old-wordpress/`) — keep it until
   the import in step 9 is done, then delete it.
2. Upload `almarah-site-release.zip` into `public_html` and **Extract** it.
3. Open the extracted `almarah-site` folder, select everything inside it
   (including the hidden `.htaccess` — enable *Show hidden files*), and
   **Move** it up into `public_html`. Delete the now‑empty `almarah-site`
   folder and the ZIP.

You should end up with `public_html/index.php`, `public_html/.htaccess`,
`public_html/app/`, `public_html/assets/` … directly under `public_html`.

> **SSH alternative** (Business plan and above): `cd domains/almarah.org/public_html && unzip -q ~/almarah-site-release.zip && mv almarah-site/* almarah-site/.htaccess . && rmdir almarah-site`

## 4. config.php

In File Manager, copy `config.dist.php` to **`config.php`** and edit it:

```php
'db_name' => 'u123456_almarah_site',
'db_user' => 'u123456_site',
'db_pass' => 'the-password-from-step-2',
'db_host' => 'localhost',
'site_url' => 'https://almarah.org',
'timezone' => 'Asia/Karachi',

// recommended
'backup_path'       => '/home/u123456/almarah-backups',   // outside public_html
'backup_passphrase' => 'a-long-random-passphrase',          // store it safely!
'backup_keep_days'  => 30,

// optional: lets the dashboard read fundraise.almarah.org statistics
'fundraise_db_host' => 'localhost',
'fundraise_db_name' => 'u123456_fundraise',
'fundraise_db_user' => 'u123456_fundraise_ro',   // SELECT-only user
'fundraise_db_pass' => '…',

// optional: a word the /setup page will ask for before installing
'setup_lock' => 'some-word',
```

Replace `u123456` with your actual Hostinger username (shown in File Manager's
path bar). `config.php` is refused to browsers by `.htaccess`, but you can
double‑check: opening https://almarah.org/config.php must show **403**.

## 5. Run the installer

Open **https://almarah.org/setup**.

The page checks PHP, extensions, folder permissions and the database
connection and lists anything that is wrong with a plain‑language fix. When
every line is green:

1. enter the first **super administrator** (your name, email, a strong
   password — at least 12 characters);
2. click **Install**. This runs all migrations, seeds the content (programmes,
   bank accounts, purposes, menus, email templates, legal pages) and writes
   `storage/app/installed.lock`.

Sign in at **https://almarah.org/login** → you land on the admin dashboard.
`/setup` now only shows "Already installed".

## 6. Settings inside the admin

Admin → **System → Settings**

- **Email (SMTP)** — use Hostinger's mail or your provider:
  host `smtp.hostinger.com`, port `465`, encryption `ssl`, username = the full
  address, password = mailbox password. Send a test email from the same page.
  Add SPF/DKIM for the domain in hPanel → Emails → DNS to keep receipts out
  of spam.
- **Payment gateways** — paste the Meezan and/or Etisalat credentials, keep
  *sandbox* until the bank confirms the test transaction, then switch to
  *live*. The return / callback URLs to give the banks are shown on that page
  (`https://almarah.org/payments/meezan/return` etc.).
- **Site options** — organisation phone, address, social links, Google
  Analytics ID (optional).

Admin → **System → Users & roles** — create accounts for the content,
volunteer and finance managers. Each gets only the menu items for their role.

## 7. Cron jobs

hPanel → **Advanced → Cron Jobs**. Add four jobs (type *Custom*). Replace the
path with yours — File Manager shows it; usually
`/home/u123456/domains/almarah.org/public_html`.

| Schedule | Command |
|---|---|
| `*/5 * * * *` | `cd /home/u123456/domains/almarah.org/public_html && php bin/console mail:work --quiet` |
| `*/10 * * * *` | `cd /home/u123456/domains/almarah.org/public_html && php bin/console payments:reconcile` |
| `10 2 * * *` | `cd /home/u123456/domains/almarah.org/public_html && php bin/console backups:run database --quiet` |
| `10 3 * * 0` | `cd /home/u123456/domains/almarah.org/public_html && php bin/console backups:run full --quiet` |

If `php` on the cron line picks an old version, use Hostinger's explicit
binary, e.g. `/opt/alt/php82/usr/bin/php` (Advanced → PHP Configuration shows
the path, or run `which php` in SSH).

Email receipts are also delivered immediately after each request when the
queue is enabled, so the 5‑minute job is a safety net rather than a delay.

## 8. SSL, HTTPS and caching

- hPanel → **Security → SSL** → install the free Let's Encrypt certificate
  (automatic on Hostinger). `.htaccess` already redirects http → https and
  sends HSTS.
- Hostinger's **LiteSpeed cache**: leave it on for static files; it does not
  cache pages that set cookies, so the admin and forms are unaffected.
- If you put **Cloudflare** in front, set SSL mode to *Full (strict)*; the
  https redirect checks `CF-Visitor` so there is no loop.

## 9. Bring the history across

Once the site is up, from SSH in `public_html`:

```bash
# a) old WooCommerce donation orders (CSV export from WP admin → WooCommerce → Orders)
php bin/console import:woocommerce ~/orders.csv --dry-run      # look first
php bin/console import:woocommerce ~/orders.csv

# or straight from the old WordPress database while it still exists
php bin/console import:woocommerce --wp-dsn="mysql:host=localhost;dbname=u123456_wp" \
    --wp-user=u123456_wp --wp-pass='…' --wp-prefix=wp_ --dry-run
```

b) **Annual reports / PDFs** — upload the files from the old site's
`wp-content/uploads` into `assets/documents/` (names are listed in
`tools/media-manifest.tsv`), or rebuild the ZIP with `--with-pdfs`.
c) **Photos & videos** — already included; add new ones under Admin → Website
→ Photos & videos.

## 10. Go‑live checklist

- [ ] https://almarah.org/ loads with the padlock; http:// redirects
- [ ] `/config.php`, `/storage/logs/`, `/app/` all return 403
- [ ] `/robots.txt` and `/sitemap.xml` render
- [ ] Contact form → message arrives in Admin → Inbox and by email
- [ ] Volunteer form with a CV upload works
- [ ] Donate → bank transfer page shows the right accounts (Admin → Finance → Bank accounts)
- [ ] Donate → card payment in **sandbox** completes and a receipt email arrives
- [ ] Gateway switched to **live**; a real PKR 100 donation completes; refund it from Admin → Donations
- [ ] Admin → System → Backups → run *Full* once; download it and keep it off the server
- [ ] Cron jobs show a last‑run time in hPanel
- [ ] Legal pages reviewed: `/privacy-policy`, `/terms`, `/refund-policy` — replace every **[REVIEW]** note (Admin → Website → Pages)
- [ ] Old WordPress folder deleted; its database dropped after the import
- [ ] Google Search Console: submit `https://almarah.org/sitemap.xml`

## 11. Updating later

Upload the new ZIP, extract **over** the existing files (never delete
`config.php`, `storage/` or `uploads/`), then open
`https://almarah.org/setup` — it runs any new migrations and shows "Already
installed". Or via SSH: `php bin/console migrate`.

## 12. If something goes wrong

| Symptom | Fix |
|---|---|
| Blank page / 500 | `storage/logs/app-YYYY-MM-DD.log` has the message. Usually a wrong `db_*` value or PHP < 8.2. |
| "Already installed" but you never installed | an old `storage/app/installed.lock` was uploaded; delete it and reload `/setup`. |
| Every page is 404 except the home page | `.htaccess` was not uploaded (hidden file) or `mod_rewrite` is off. |
| Emails not arriving | Settings → Email → *Send test*; check Admin → System → Email queue for the error text; verify SPF/DKIM. |
| Payment returns "expired" | `site_url` must be exactly the https address the bank redirects to. |
| Backups fail with "folder cannot be created" | set `backup_path` to a folder under `/home/u123456/` that exists; `mkdir` it in File Manager. |
| Uploads fail | `storage/uploads` and `uploads` must be writable (755 is enough on Hostinger — PHP runs as your user). |

Support: keep this file with the ZIP; the full developer notes are in
`README.md` and `docs/`.
