-- =============================================================================
-- 0009 — Main website CMS, forms, SEO, reconciliation, backups, permissions
--
-- ADDITIVE ONLY. Migrations 0001-0008 are kept identical to the fundraising
-- platform so the payment code is reused unchanged. This file adds the tables
-- the public website and the unified admin need. No existing table is dropped,
-- renamed or re-typed; the only change to an existing table is a handful of
-- NULLABLE columns on `donations` (website donor details and receipt number).
-- =============================================================================

-- ---------------------------------------------------------------- donations +
ALTER TABLE `donations`
  ADD COLUMN `purpose_id`     INT UNSIGNED NULL AFTER `team_id`,
  ADD COLUMN `donor_country`  VARCHAR(80)  NULL AFTER `donor_message`,
  ADD COLUMN `donor_city`     VARCHAR(120) NULL AFTER `donor_country`,
  ADD COLUMN `donor_address`  VARCHAR(255) NULL AFTER `donor_city`,
  ADD COLUMN `donation_type`  ENUM('one_time','monthly') NOT NULL DEFAULT 'one_time' AFTER `donor_address`,
  ADD COLUMN `source`         VARCHAR(30)  NOT NULL DEFAULT 'website' AFTER `donation_type`,
  ADD COLUMN `receipt_number` VARCHAR(40)  NULL AFTER `source`,
  ADD COLUMN `legacy_order_id` VARCHAR(64) NULL AFTER `receipt_number`;

CREATE INDEX `donations_purpose_idx` ON `donations` (`purpose_id`, `status`);
CREATE INDEX `donations_source_idx`  ON `donations` (`source`);
CREATE UNIQUE INDEX `donations_receipt_unique` ON `donations` (`receipt_number`);
CREATE UNIQUE INDEX `donations_legacy_unique`  ON `donations` (`source`, `legacy_order_id`);

-- Donation purposes (projects / funds a donor can give to).
CREATE TABLE IF NOT EXISTS `donation_purposes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `slug` VARCHAR(140) NOT NULL,
  `description` VARCHAR(255) NULL,
  `campaign_id` INT UNSIGNED NULL,
  `is_zakat_eligible` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `status` ENUM('active','hidden') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `purposes_slug_unique` (`slug`),
  KEY `purposes_status_idx` (`status`, `sort_order`),
  CONSTRAINT `fk_purposes_campaign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------- permissions
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(80) NOT NULL,
  `group_name` VARCHAR(40) NOT NULL,
  `label` VARCHAR(120) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `role_permissions` (
  `role_id` INT UNSIGNED NOT NULL,
  `permission_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`role_id`, `permission_id`),
  CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rp_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------- CMS
CREATE TABLE IF NOT EXISTS `cms_pages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(160) NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `template` VARCHAR(60) NOT NULL DEFAULT 'page',
  `hero_title` VARCHAR(200) NULL,
  `hero_subtitle` VARCHAR(500) NULL,
  `hero_image` VARCHAR(255) NULL,
  `body_html` MEDIUMTEXT NULL,
  `blocks_json` JSON NULL,
  `menu_group` VARCHAR(40) NULL,
  `status` ENUM('published','draft') NOT NULL DEFAULT 'published',
  `show_breadcrumbs` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `created_by` INT UNSIGNED NULL,
  `updated_by` INT UNSIGNED NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cms_pages_slug_unique` (`slug`),
  KEY `cms_pages_status_idx` (`status`),
  CONSTRAINT `fk_cms_pages_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_cms_pages_updater` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cms_page_revisions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `page_id` INT UNSIGNED NOT NULL,
  `snapshot_json` JSON NOT NULL,
  `saved_by` INT UNSIGNED NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `revisions_page_idx` (`page_id`, `created_at`),
  CONSTRAINT `fk_revisions_page` FOREIGN KEY (`page_id`) REFERENCES `cms_pages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Homepage slider (replaces Slider Revolution).
CREATE TABLE IF NOT EXISTS `cms_slides` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(200) NULL,
  `subtitle` VARCHAR(500) NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `mobile_image_path` VARCHAR(255) NULL,
  `image_alt` VARCHAR(200) NULL,
  `button_label` VARCHAR(60) NULL,
  `button_url` VARCHAR(255) NULL,
  `button2_label` VARCHAR(60) NULL,
  `button2_url` VARCHAR(255) NULL,
  `text_position` ENUM('left','center','right') NOT NULL DEFAULT 'left',
  `overlay_opacity` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `slides_active_idx` (`is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Site-wide banners / announcement bars.
CREATE TABLE IF NOT EXISTS `cms_banners` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `placement` ENUM('top_bar','popup','sidebar') NOT NULL DEFAULT 'top_bar',
  `title` VARCHAR(200) NOT NULL,
  `body` VARCHAR(500) NULL,
  `link_label` VARCHAR(60) NULL,
  `link_url` VARCHAR(255) NULL,
  `bg_color` VARCHAR(9) NULL,
  `starts_at` DATETIME NULL,
  `ends_at` DATETIME NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `banners_active_idx` (`is_active`, `placement`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Media library: photos, videos (YouTube), e-books, documents.
CREATE TABLE IF NOT EXISTS `media_items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kind` ENUM('photo','video','ebook','document','image') NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `description` VARCHAR(1000) NULL,
  `file_path` VARCHAR(255) NULL,
  `thumbnail_path` VARCHAR(255) NULL,
  `external_url` VARCHAR(500) NULL,
  `mime_type` VARCHAR(100) NULL,
  `size_bytes` INT UNSIGNED NULL,
  `width` SMALLINT UNSIGNED NULL,
  `height` SMALLINT UNSIGNED NULL,
  `alt_text` VARCHAR(200) NULL,
  `album` VARCHAR(120) NULL,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `status` ENUM('published','hidden') NOT NULL DEFAULT 'published',
  `sort_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `uploaded_by` INT UNSIGNED NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `media_kind_idx` (`kind`, `status`, `sort_order`),
  KEY `media_album_idx` (`album`),
  CONSTRAINT `fk_media_user` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------- SEO
CREATE TABLE IF NOT EXISTS `seo_meta` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `route_path` VARCHAR(191) NOT NULL,
  `title` VARCHAR(200) NULL,
  `description` VARCHAR(320) NULL,
  `keywords` VARCHAR(255) NULL,
  `canonical_url` VARCHAR(255) NULL,
  `og_title` VARCHAR(200) NULL,
  `og_description` VARCHAR(320) NULL,
  `og_image` VARCHAR(255) NULL,
  `twitter_card` VARCHAR(30) NOT NULL DEFAULT 'summary_large_image',
  `robots` VARCHAR(80) NULL,
  `schema_json` JSON NULL,
  `priority` DECIMAL(2,1) NOT NULL DEFAULT 0.6,
  `changefreq` VARCHAR(10) NOT NULL DEFAULT 'monthly',
  `in_sitemap` TINYINT(1) NOT NULL DEFAULT 1,
  `updated_by` INT UNSIGNED NULL,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `seo_meta_route_unique` (`route_path`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `seo_redirects` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `from_path` VARCHAR(191) NOT NULL,
  `to_url` VARCHAR(500) NULL,
  `status_code` SMALLINT UNSIGNED NOT NULL DEFAULT 301,
  `is_regex` TINYINT(1) NOT NULL DEFAULT 0,
  `hits` INT UNSIGNED NOT NULL DEFAULT 0,
  `last_hit_at` TIMESTAMP NULL DEFAULT NULL,
  `note` VARCHAR(255) NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `redirects_from_unique` (`from_path`),
  KEY `redirects_active_idx` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 404 tracking so missing pages after migration can be found and fixed.
CREATE TABLE IF NOT EXISTS `seo_not_found_log` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `path` VARCHAR(191) NOT NULL,
  `referer` VARCHAR(255) NULL,
  `hits` INT UNSIGNED NOT NULL DEFAULT 1,
  `last_hit_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nf_path_unique` (`path`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------- forms
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(191) NOT NULL,
  `phone` VARCHAR(40) NULL,
  `subject` VARCHAR(200) NULL,
  `message` TEXT NOT NULL,
  `status` ENUM('new','read','replied','archived','spam') NOT NULL DEFAULT 'new',
  `admin_notes` TEXT NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` VARCHAR(255) NULL,
  `fingerprint` CHAR(64) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `contact_status_idx` (`status`, `created_at`),
  KEY `contact_fp_idx` (`fingerprint`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `newsletter_subscribers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NULL,
  `email` VARCHAR(191) NOT NULL,
  `status` ENUM('pending','subscribed','unsubscribed','bounced') NOT NULL DEFAULT 'subscribed',
  `confirm_token_hash` CHAR(64) NULL,
  `unsubscribe_token_hash` CHAR(64) NULL,
  `source` VARCHAR(40) NOT NULL DEFAULT 'website',
  `ip_address` VARCHAR(45) NULL,
  `confirmed_at` TIMESTAMP NULL DEFAULT NULL,
  `unsubscribed_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `newsletter_email_unique` (`email`),
  KEY `newsletter_status_idx` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `volunteers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `first_name` VARCHAR(80) NOT NULL,
  `last_name` VARCHAR(80) NOT NULL,
  `email` VARCHAR(191) NOT NULL,
  `phone` VARCHAR(40) NOT NULL,
  `alt_phone` VARCHAR(40) NULL,
  `date_of_birth` DATE NULL,
  `gender` ENUM('male','female','other','prefer_not') NULL,
  `cnic` VARCHAR(20) NULL,
  `address_line` VARCHAR(255) NULL,
  `city` VARCHAR(120) NULL,
  `state` VARCHAR(120) NULL,
  `country` VARCHAR(80) NULL,
  `occupation` VARCHAR(120) NULL,
  `organisation` VARCHAR(160) NULL,
  `skills` VARCHAR(500) NULL,
  `interests_json` JSON NULL,
  `availability` VARCHAR(120) NULL,
  `previous_experience` TEXT NULL,
  `motivation` TEXT NULL,
  `emergency_contact` VARCHAR(160) NULL,
  `status` ENUM('pending','approved','rejected','active','inactive') NOT NULL DEFAULT 'pending',
  `admin_notes` TEXT NULL,
  `reviewed_by` INT UNSIGNED NULL,
  `reviewed_at` TIMESTAMP NULL DEFAULT NULL,
  `ip_address` VARCHAR(45) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `volunteers_status_idx` (`status`, `created_at`),
  KEY `volunteers_email_idx` (`email`),
  CONSTRAINT `fk_volunteers_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `career_jobs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(160) NOT NULL,
  `slug` VARCHAR(180) NOT NULL,
  `department` VARCHAR(100) NULL,
  `location` VARCHAR(120) NULL,
  `employment_type` ENUM('full_time','part_time','contract','internship','volunteer') NOT NULL DEFAULT 'full_time',
  `summary` VARCHAR(500) NULL,
  `description_html` MEDIUMTEXT NULL,
  `closes_at` DATE NULL,
  `status` ENUM('open','closed','draft') NOT NULL DEFAULT 'open',
  `sort_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `jobs_slug_unique` (`slug`),
  KEY `jobs_status_idx` (`status`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `career_applications` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `job_id` INT UNSIGNED NULL,
  `position` VARCHAR(160) NOT NULL,
  `name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(191) NOT NULL,
  `phone` VARCHAR(40) NOT NULL,
  `city` VARCHAR(120) NULL,
  `cover_letter` TEXT NULL,
  `linkedin_url` VARCHAR(255) NULL,
  `resume_path` VARCHAR(255) NULL,
  `resume_original_name` VARCHAR(200) NULL,
  `status` ENUM('new','shortlisted','interview','rejected','hired','archived') NOT NULL DEFAULT 'new',
  `admin_notes` TEXT NULL,
  `ip_address` VARCHAR(45) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `applications_job_idx` (`job_id`, `status`),
  KEY `applications_status_idx` (`status`, `created_at`),
  CONSTRAINT `fk_applications_job` FOREIGN KEY (`job_id`) REFERENCES `career_jobs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- "Start Fundraising" interest form (the actual fundraiser is created on the
-- separate fundraise.almarah.org platform; this records the enquiry).
CREATE TABLE IF NOT EXISTS `fundraising_applications` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(191) NOT NULL,
  `phone` VARCHAR(40) NULL,
  `organisation` VARCHAR(160) NULL,
  `campaign_title` VARCHAR(180) NOT NULL,
  `cause` VARCHAR(120) NULL,
  `goal_amount` DECIMAL(14,2) NULL,
  `description` TEXT NULL,
  `status` ENUM('new','contacted','approved','rejected','archived') NOT NULL DEFAULT 'new',
  `admin_notes` TEXT NULL,
  `ip_address` VARCHAR(45) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fa_status_idx` (`status`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Generic submission log: one row per form post of any kind (spam analysis,
-- duplicate detection and the "all forms" admin view).
CREATE TABLE IF NOT EXISTS `form_submissions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `form_key` VARCHAR(40) NOT NULL,
  `record_id` INT UNSIGNED NULL,
  `fingerprint` CHAR(64) NOT NULL,
  `ip_address` VARCHAR(45) NULL,
  `outcome` ENUM('stored','duplicate','spam','invalid') NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fs_form_idx` (`form_key`, `created_at`),
  KEY `fs_fp_idx` (`fingerprint`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------- reconciliation
CREATE TABLE IF NOT EXISTS `reconciliation_runs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `period_type` ENUM('daily','monthly','custom') NOT NULL,
  `period_start` DATE NOT NULL,
  `period_end` DATE NOT NULL,
  `gateway_id` INT UNSIGNED NULL,
  `donations_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `donations_minor` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `verified_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `verified_minor` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `pending_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `pending_minor` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `failed_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `mismatch_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `summary_json` JSON NULL,
  `run_by` INT UNSIGNED NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `recon_period_idx` (`period_start`, `period_end`),
  CONSTRAINT `fk_recon_user` FOREIGN KEY (`run_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `reconciliation_items` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `run_id` INT UNSIGNED NOT NULL,
  `donation_id` BIGINT UNSIGNED NULL,
  `payment_transaction_id` BIGINT UNSIGNED NULL,
  `issue` VARCHAR(60) NOT NULL,
  `details` VARCHAR(500) NULL,
  `resolved_at` TIMESTAMP NULL DEFAULT NULL,
  `resolved_by` INT UNSIGNED NULL,
  `resolution_note` VARCHAR(500) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ri_run_idx` (`run_id`),
  KEY `ri_issue_idx` (`issue`, `resolved_at`),
  CONSTRAINT `fk_ri_run` FOREIGN KEY (`run_id`) REFERENCES `reconciliation_runs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Append-only ledger of manual financial adjustments. Historical donation
-- rows are never edited silently: every change goes through here.
CREATE TABLE IF NOT EXISTS `financial_adjustments` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `donation_id` BIGINT UNSIGNED NOT NULL,
  `payment_transaction_id` BIGINT UNSIGNED NULL,
  `adjustment_type` VARCHAR(40) NOT NULL,
  `old_status` VARCHAR(20) NULL,
  `new_status` VARCHAR(20) NULL,
  `old_amount_minor` BIGINT UNSIGNED NULL,
  `new_amount_minor` BIGINT UNSIGNED NULL,
  `reason` VARCHAR(500) NOT NULL,
  `evidence_reference` VARCHAR(160) NULL,
  `actor_user_id` INT UNSIGNED NULL,
  `ip_address` VARCHAR(45) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fa_donation_idx` (`donation_id`),
  CONSTRAINT `fk_fadj_donation` FOREIGN KEY (`donation_id`) REFERENCES `donations` (`id`),
  CONSTRAINT `fk_fadj_user` FOREIGN KEY (`actor_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------- backups
CREATE TABLE IF NOT EXISTS `backup_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kind` ENUM('database','uploads','full') NOT NULL,
  `file_name` VARCHAR(200) NOT NULL,
  `size_bytes` BIGINT UNSIGNED NULL,
  `checksum_sha256` CHAR(64) NULL,
  `encrypted` TINYINT(1) NOT NULL DEFAULT 0,
  `offsite_status` ENUM('not_configured','pending','uploaded','failed') NOT NULL DEFAULT 'not_configured',
  `status` ENUM('running','completed','failed','deleted') NOT NULL DEFAULT 'running',
  `message` VARCHAR(500) NULL,
  `started_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `finished_at` TIMESTAMP NULL DEFAULT NULL,
  `triggered_by` INT UNSIGNED NULL,
  PRIMARY KEY (`id`),
  KEY `backup_kind_idx` (`kind`, `started_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------- board & partners
CREATE TABLE IF NOT EXISTS `team_profiles` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `group_key` ENUM('board','advisor','management','ambassador') NOT NULL DEFAULT 'board',
  `name` VARCHAR(120) NOT NULL,
  `designation` VARCHAR(160) NULL,
  `bio` TEXT NULL,
  `photo_path` VARCHAR(255) NULL,
  `linkedin_url` VARCHAR(255) NULL,
  `sort_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `status` ENUM('published','hidden') NOT NULL DEFAULT 'published',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `profiles_group_idx` (`group_key`, `status`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `partners` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kind` ENUM('mou','certificate','registration','bank','sponsor') NOT NULL,
  `name` VARCHAR(160) NOT NULL,
  `description` VARCHAR(500) NULL,
  `logo_path` VARCHAR(255) NULL,
  `document_path` VARCHAR(255) NULL,
  `external_url` VARCHAR(255) NULL,
  `issued_on` DATE NULL,
  `expires_on` DATE NULL,
  `meta_json` JSON NULL,
  `sort_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `status` ENUM('published','hidden') NOT NULL DEFAULT 'published',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `partners_kind_idx` (`kind`, `status`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Financial report documents (audit reports etc.)
CREATE TABLE IF NOT EXISTS `financial_reports` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(160) NOT NULL,
  `fiscal_year` VARCHAR(20) NOT NULL,
  `document_path` VARCHAR(255) NOT NULL,
  `summary` VARCHAR(500) NULL,
  `size_bytes` INT UNSIGNED NULL,
  `sort_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `status` ENUM('published','hidden') NOT NULL DEFAULT 'published',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `reports_status_idx` (`status`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bank accounts shown on /banks/ and /donate/ (copy-to-clipboard cards).
CREATE TABLE IF NOT EXISTS `bank_accounts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `bank_name` VARCHAR(120) NOT NULL,
  `account_title` VARCHAR(160) NOT NULL,
  `account_number` VARCHAR(60) NULL,
  `iban` VARCHAR(60) NULL,
  `swift_code` VARCHAR(20) NULL,
  `branch` VARCHAR(160) NULL,
  `currency` CHAR(3) NOT NULL DEFAULT 'PKR',
  `account_type` VARCHAR(60) NULL,
  `logo_path` VARCHAR(255) NULL,
  `is_international` TINYINT(1) NOT NULL DEFAULT 0,
  `sort_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `status` ENUM('published','hidden') NOT NULL DEFAULT 'published',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `banks_status_idx` (`status`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reporting view used by the dashboard and the reconciliation module.
CREATE OR REPLACE VIEW `v_purpose_totals` AS
SELECT
  p.`id` AS `purpose_id`,
  p.`name`,
  COALESCE(SUM(CASE WHEN d.`status` = 'completed' THEN d.`amount_minor` ELSE 0 END), 0) AS `raised_minor`,
  COUNT(CASE WHEN d.`status` = 'completed' THEN 1 END) AS `donation_count`
FROM `donation_purposes` p
LEFT JOIN `donations` d ON d.`purpose_id` = p.`id`
GROUP BY p.`id`, p.`name`;
