-- 0020: Donation management Part 2 - tax exemption certificates (idempotent).
--  * donation_certificates : one row per donation (UNIQUE donation_id => no duplicate certificates),
--                            holds certificate number, PDF location/hash, generation + email status
--  * site_settings         : foundation registration/tax details used on the certificate. Seeded ONLY from the
--                            foundation's own published FBR approval order (assets/documents/Certificate-under-section-236-...pdf).
--                            Edit them in site_settings if the approval is renewed. INSERT IGNORE never overwrites edits.

CREATE TABLE IF NOT EXISTS `donation_certificates` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `donation_id` BIGINT UNSIGNED NOT NULL,
  `certificate_number` VARCHAR(40) NULL,
  `status` ENUM('pending','generated','failed') NOT NULL DEFAULT 'pending',
  `file_path` VARCHAR(255) NULL,
  `file_sha256` CHAR(64) NULL,
  `issued_at` DATETIME NULL,
  `generated_at` DATETIME NULL,
  `generated_by` INT UNSIGNED NULL,
  `generation_error` VARCHAR(500) NULL,
  `email_status` ENUM('not_sent','queued','sent','failed','skipped') NOT NULL DEFAULT 'not_sent',
  `email_to` VARCHAR(191) NULL,
  `email_job_id` BIGINT UNSIGNED NULL,
  `email_attempts` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `email_last_error` VARCHAR(500) NULL,
  `email_sent_at` DATETIME NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `donation_certificates_donation_unique` (`donation_id`),
  UNIQUE KEY `donation_certificates_number_unique` (`certificate_number`),
  KEY `donation_certificates_status_idx` (`status`, `email_status`),
  CONSTRAINT `fk_donation_certificates_donation` FOREIGN KEY (`donation_id`) REFERENCES `donations` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `site_settings` (`setting_key`, `setting_value`) VALUES
  ('certificate.org_name', 'Almarah Foundation'),
  ('certificate.org_address', '3-Canal Park, Gulberg-II, Lahore'),
  ('certificate.ntn', '4910219'),
  ('certificate.approval_text', 'Approved as a Non-Profit Organization under section 2(36) of the Income Tax Ordinance, 2001'),
  ('certificate.approval_valid_from', '2026-07-01'),
  ('certificate.approval_valid_until', '2028-06-30'),
  ('certificate.signatory_name', ''),
  ('certificate.signatory_title', 'Authorized Signatory');
