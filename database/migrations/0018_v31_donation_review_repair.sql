-- 0018: v3.1 schema repair for the donation-review workflow (idempotent).
--
-- Some databases never received migration 0012 (donation review) and fail with
-- "Unknown column 'd.verification_status'". This file adds ONLY what is missing, using
-- INFORMATION_SCHEMA checks, so it is safe to run on a database that has none, some, or
-- all of those changes, and safe to run twice. It does not modify, delete or recompute
-- any existing donation, amount, status or transaction.
--
-- Existing rows get verification_status = 'legacy' (the value the application already
-- treats as "created before the review workflow") and certificate_status = 'not_issued'.
-- Decide separately, under your own policy, whether any legacy donation should be marked verified.


SET @s = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `donations` ADD COLUMN `transaction_at` DATETIME NULL', 'DO 0') FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'donations' AND column_name = 'transaction_at');
PREPARE st FROM @s;
EXECUTE st;
DEALLOCATE PREPARE st;

SET @s = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `donations` ADD COLUMN `payment_method` VARCHAR(40) NULL', 'DO 0') FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'donations' AND column_name = 'payment_method');
PREPARE st FROM @s;
EXECUTE st;
DEALLOCATE PREPARE st;

SET @s = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `donations` ADD COLUMN `payment_proof_path` VARCHAR(255) NULL', 'DO 0') FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'donations' AND column_name = 'payment_proof_path');
PREPARE st FROM @s;
EXECUTE st;
DEALLOCATE PREPARE st;

SET @s = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `donations` ADD COLUMN `acknowledgment_self` TINYINT(1) NOT NULL DEFAULT 1', 'DO 0') FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'donations' AND column_name = 'acknowledgment_self');
PREPARE st FROM @s;
EXECUTE st;
DEALLOCATE PREPARE st;

SET @s = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `donations` ADD COLUMN `recipient_name` VARCHAR(160) NULL', 'DO 0') FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'donations' AND column_name = 'recipient_name');
PREPARE st FROM @s;
EXECUTE st;
DEALLOCATE PREPARE st;

SET @s = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `donations` ADD COLUMN `recipient_phone` VARCHAR(40) NULL', 'DO 0') FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'donations' AND column_name = 'recipient_phone');
PREPARE st FROM @s;
EXECUTE st;
DEALLOCATE PREPARE st;

SET @s = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `donations` ADD COLUMN `recipient_email` VARCHAR(190) NULL', 'DO 0') FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'donations' AND column_name = 'recipient_email');
PREPARE st FROM @s;
EXECUTE st;
DEALLOCATE PREPARE st;

SET @s = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `donations` ADD COLUMN `recipient_address` VARCHAR(500) NULL', 'DO 0') FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'donations' AND column_name = 'recipient_address');
PREPARE st FROM @s;
EXECUTE st;
DEALLOCATE PREPARE st;

SET @s = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `donations` ADD COLUMN `acknowledgment_instructions` VARCHAR(1000) NULL', 'DO 0') FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'donations' AND column_name = 'acknowledgment_instructions');
PREPARE st FROM @s;
EXECUTE st;
DEALLOCATE PREPARE st;

SET @s = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `donations` ADD COLUMN `referral_enabled` TINYINT(1) NOT NULL DEFAULT 0', 'DO 0') FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'donations' AND column_name = 'referral_enabled');
PREPARE st FROM @s;
EXECUTE st;
DEALLOCATE PREPARE st;

SET @s = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `donations` ADD COLUMN `referrer_name` VARCHAR(160) NULL', 'DO 0') FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'donations' AND column_name = 'referrer_name');
PREPARE st FROM @s;
EXECUTE st;
DEALLOCATE PREPARE st;

SET @s = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `donations` ADD COLUMN `referrer_phone` VARCHAR(40) NULL', 'DO 0') FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'donations' AND column_name = 'referrer_phone');
PREPARE st FROM @s;
EXECUTE st;
DEALLOCATE PREPARE st;

SET @s = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `donations` ADD COLUMN `referral_notes` VARCHAR(1000) NULL', 'DO 0') FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'donations' AND column_name = 'referral_notes');
PREPARE st FROM @s;
EXECUTE st;
DEALLOCATE PREPARE st;

SET @s = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `donations` ADD COLUMN `verification_status` VARCHAR(40) NOT NULL DEFAULT ''legacy''', 'DO 0') FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'donations' AND column_name = 'verification_status');
PREPARE st FROM @s;
EXECUTE st;
DEALLOCATE PREPARE st;

SET @s = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `donations` ADD COLUMN `verified_by` INT UNSIGNED NULL', 'DO 0') FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'donations' AND column_name = 'verified_by');
PREPARE st FROM @s;
EXECUTE st;
DEALLOCATE PREPARE st;

SET @s = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `donations` ADD COLUMN `verified_at` DATETIME NULL', 'DO 0') FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'donations' AND column_name = 'verified_at');
PREPARE st FROM @s;
EXECUTE st;
DEALLOCATE PREPARE st;

SET @s = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `donations` ADD COLUMN `certificate_status` VARCHAR(30) NOT NULL DEFAULT ''not_issued''', 'DO 0') FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'donations' AND column_name = 'certificate_status');
PREPARE st FROM @s;
EXECUTE st;
DEALLOCATE PREPARE st;

SET @s = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `donations` ADD INDEX `donations_verification_idx` (`verification_status`, `status`)', 'DO 0') FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'donations' AND index_name = 'donations_verification_idx');
PREPARE st FROM @s;
EXECUTE st;
DEALLOCATE PREPARE st;

CREATE TABLE IF NOT EXISTS `donation_review_events` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `donation_id` BIGINT UNSIGNED NOT NULL,
  `actor_user_id` INT UNSIGNED NULL,
  `stage_key` VARCHAR(60) NOT NULL,
  `decision` VARCHAR(30) NOT NULL,
  `comment` VARCHAR(1000) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `donation_review_events_donation_idx` (`donation_id`, `id`),
  KEY `donation_review_events_actor_idx` (`actor_user_id`),
  CONSTRAINT `donation_review_events_donation_fk` FOREIGN KEY (`donation_id`) REFERENCES `donations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `donation_review_events_actor_fk` FOREIGN KEY (`actor_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
