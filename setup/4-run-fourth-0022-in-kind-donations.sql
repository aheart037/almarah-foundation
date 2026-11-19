-- 0022: In-kind (goods) donations - idempotent.
--  * inkind_donations      : one row per submission (donor, hand-over details, acknowledgment recipient, referrer,
--                            status pending/verified/rejected, who reviewed it and why)
--  * inkind_donation_items : one row per item line (category, item, quantity, unit, details)
--  * inkind_events         : full history (submitted, verified, rejected, reopened, certificate generated/emailed)
--  * inkind_certificates   : certificate generation history; every (re)generation adds a version, nothing is overwritten
-- No monetary amount is stored: goods never enter the money ledger, reports or reconciliation.

CREATE TABLE IF NOT EXISTS `inkind_donations` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `public_reference` VARCHAR(32) NOT NULL,
  `donor_id` INT UNSIGNED NULL,
  `donor_name` VARCHAR(150) NOT NULL,
  `donor_email` VARCHAR(191) NOT NULL,
  `donor_phone` VARCHAR(40) NOT NULL,
  `donor_city` VARCHAR(100) NULL,
  `donor_address` VARCHAR(500) NULL,
  `handover_method` ENUM('drop_off','pickup') NOT NULL DEFAULT 'drop_off',
  `handover_date` DATE NULL,
  `pickup_address` VARCHAR(500) NULL,
  `description` TEXT NULL,
  `photos_json` TEXT NULL,
  `acknowledgment_self` TINYINT(1) NOT NULL DEFAULT 1,
  `recipient_name` VARCHAR(160) NULL,
  `recipient_phone` VARCHAR(40) NULL,
  `recipient_email` VARCHAR(191) NULL,
  `recipient_address` VARCHAR(500) NULL,
  `acknowledgment_instructions` VARCHAR(1000) NULL,
  `referral_enabled` TINYINT(1) NOT NULL DEFAULT 0,
  `referrer_name` VARCHAR(160) NULL,
  `referrer_phone` VARCHAR(40) NULL,
  `referral_notes` VARCHAR(1000) NULL,
  `status` ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending',
  `review_note` VARCHAR(1000) NULL,
  `reviewed_by` INT UNSIGNED NULL,
  `reviewed_at` DATETIME NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inkind_donations_reference_unique` (`public_reference`),
  KEY `inkind_donations_status_idx` (`status`, `created_at`),
  KEY `inkind_donations_email_idx` (`donor_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `inkind_donation_items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `inkind_id` INT UNSIGNED NOT NULL,
  `category` VARCHAR(60) NOT NULL,
  `item_name` VARCHAR(150) NOT NULL,
  `quantity` INT UNSIGNED NOT NULL DEFAULT 1,
  `unit` VARCHAR(30) NULL,
  `details` VARCHAR(500) NULL,
  `sort_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `inkind_items_donation_idx` (`inkind_id`),
  CONSTRAINT `fk_inkind_items_donation` FOREIGN KEY (`inkind_id`) REFERENCES `inkind_donations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `inkind_events` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `inkind_id` INT UNSIGNED NOT NULL,
  `action` VARCHAR(40) NOT NULL,
  `actor_user_id` INT UNSIGNED NULL,
  `note` VARCHAR(1000) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `inkind_events_donation_idx` (`inkind_id`, `id`),
  CONSTRAINT `fk_inkind_events_donation` FOREIGN KEY (`inkind_id`) REFERENCES `inkind_donations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `inkind_certificates` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `inkind_id` INT UNSIGNED NOT NULL,
  `version` SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  `certificate_number` VARCHAR(40) NOT NULL,
  `status` ENUM('generated','failed') NOT NULL DEFAULT 'generated',
  `file_path` VARCHAR(255) NULL,
  `file_sha256` CHAR(64) NULL,
  `certificate_name` VARCHAR(190) NULL,
  `generation_error` VARCHAR(500) NULL,
  `generated_by` INT UNSIGNED NULL,
  `email_status` ENUM('not_sent','queued','sent','failed','skipped') NOT NULL DEFAULT 'not_sent',
  `email_to` VARCHAR(191) NULL,
  `email_job_id` BIGINT UNSIGNED NULL,
  `email_attempts` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `email_last_error` VARCHAR(500) NULL,
  `email_sent_at` DATETIME NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inkind_certificates_version_unique` (`inkind_id`, `version`),
  KEY `inkind_certificates_number_idx` (`certificate_number`),
  CONSTRAINT `fk_inkind_certificates_donation` FOREIGN KEY (`inkind_id`) REFERENCES `inkind_donations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
