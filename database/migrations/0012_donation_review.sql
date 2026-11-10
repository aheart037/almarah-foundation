-- Additive: legacy rows are explicitly marked legacy; new submissions set submitted.
-- Keep the existing unique public_reference and nullable unique receipt_number.
ALTER TABLE `donations`
  ADD COLUMN `transaction_at` DATETIME NULL,
  ADD COLUMN `payment_method` VARCHAR(30) NULL,
  ADD COLUMN `payment_proof_path` VARCHAR(255) NULL,
  ADD COLUMN `acknowledgment_self` TINYINT(1) NOT NULL DEFAULT 1,
  ADD COLUMN `recipient_name` VARCHAR(160) NULL,
  ADD COLUMN `recipient_phone` VARCHAR(40) NULL,
  ADD COLUMN `recipient_email` VARCHAR(191) NULL,
  ADD COLUMN `recipient_address` VARCHAR(500) NULL,
  ADD COLUMN `acknowledgment_instructions` VARCHAR(1000) NULL,
  ADD COLUMN `referral_enabled` TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN `referrer_name` VARCHAR(160) NULL,
  ADD COLUMN `referrer_phone` VARCHAR(40) NULL,
  ADD COLUMN `referral_notes` VARCHAR(1000) NULL,
  ADD COLUMN `verification_status` VARCHAR(40) NOT NULL DEFAULT 'legacy',
  ADD COLUMN `verified_by` INT UNSIGNED NULL,
  ADD COLUMN `verified_at` DATETIME NULL,
  ADD COLUMN `receipt_generated_at` DATETIME NULL,
  ADD COLUMN `certificate_status` VARCHAR(20) NOT NULL DEFAULT 'not_issued',
  ADD INDEX `donation_review_status_idx` (`verification_status`, `created_at`),
  ADD INDEX `donation_transaction_ref_idx` (`bank_reference`),
  ADD INDEX `donation_referrer_idx` (`referrer_name`),
  ADD INDEX `donation_recipient_idx` (`recipient_name`),
  ADD INDEX `donation_verified_by_idx` (`verified_by`),
  ADD CONSTRAINT `fk_donation_verified_by` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

-- Immutable review history; the audit log also receives summary events.
CREATE TABLE IF NOT EXISTS `donation_review_events` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `donation_id` BIGINT UNSIGNED NOT NULL,
  `actor_user_id` INT UNSIGNED NULL,
  `stage_key` VARCHAR(40) NOT NULL,
  `decision` VARCHAR(30) NOT NULL,
  `comment` VARCHAR(1000) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `review_donation_idx` (`donation_id`,`id`),
  INDEX `review_actor_idx` (`actor_user_id`,`created_at`),
  CONSTRAINT `fk_review_donation` FOREIGN KEY (`donation_id`) REFERENCES `donations` (`id`),
  CONSTRAINT `fk_review_actor` FOREIGN KEY (`actor_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
