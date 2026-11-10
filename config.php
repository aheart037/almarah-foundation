<?php

/**
 * ============================================================================
 *  ALMARAH FOUNDATION WEBSITE — YOUR CONFIGURATION FILE
 * ============================================================================
 *
 *  Fill in the database details from cPanel > Databases > MySQL Databases,
 *  then save this file and open https://your-domain/setup once.
 *
 *  This is the MAIN WEBSITE (almarah.org). It uses its OWN database and is
 *  completely independent from fundraise.almarah.org.
 *
 *  Never commit real values to Git. This file is refused by the web server
 *  (.htaccess) so it is never downloadable.
 */

return [

    // 1. Database name      e.g. 'almarf1_almarah_site'
    'db_name' => '',

    // 2. Database username  e.g. 'almarf1_site'
    'db_user' => '',

    // 3. Database password
    'db_pass' => '@123',

    // Database server. 'localhost' is correct on almost every cPanel host.
    'db_host' => 'localhost',

    // Your website address, starting with https:// (no trailing slash).
    // This is the address the banks send donors back to after a payment.
    'site_url' => 'https://softex.pk/donate',

    // Optional: time zone used for dates in the admin and emails.
    'timezone' => 'Asia/Karachi',

    /* ------------------------------------------------------------------------
     *  OPTIONAL — read-only link to the fundraise.almarah.org database.
     *  Create a MySQL user with SELECT-only rights on that database and the
     *  admin dashboard will show fundraising statistics. Leave empty to
     *  disable. The website never writes to that database.
     * ---------------------------------------------------------------------- */
    'fundraise_db_host' => 'localhost',
    'fundraise_db_name' => '',
    'fundraise_db_user' => '',
    'fundraise_db_pass' => '',
    'fundraise_url'     => 'https://fundraise.almarah.org',

    /* ------------------------------------------------------------------------
     *  OPTIONAL — backups. Backups are written OUTSIDE public_html.
     *  Encryption passphrase: keep a copy somewhere safe; without it a backup
     *  cannot be restored.
     * ---------------------------------------------------------------------- */
    'backup_path'        => '',   // default: ../almarah-backups (outside web root)
    'backup_passphrase'  => '',   // empty = backups are NOT encrypted
    'backup_keep_days'   => 30,
    'backup_offsite_cmd' => '',   // e.g. 'rclone copy {file} remote:almarah-backups'

    // LEAVE EMPTY unless the setup page tells you otherwise.
    'public_path' => '',
    'setup_lock'  => '',
];
