-- 0026: log of permanently deleted records, with the reason given by the person who deleted them.
--
-- Deleting a donation, an in-kind donation or a website form entry removes it from every normal screen.
-- This table keeps a short summary of what was removed, WHO removed it, WHEN, and WHY, and is shown on
-- Admin > Deleted records. Website form entries are logged by box and id only (no name, email or message),
-- so a data-removal request is still honoured.
--
-- Idempotent. Run it BEFORE using delete: a delete is refused (nothing is removed) if this table is missing.

CREATE TABLE IF NOT EXISTS `deleted_records` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `record_type` VARCHAR(30) NOT NULL,
  `record_id` INT UNSIGNED NOT NULL,
  `reference` VARCHAR(80) NULL,
  `summary` VARCHAR(500) NOT NULL,
  `snapshot` TEXT NULL,
  `reason` VARCHAR(500) NOT NULL,
  `deleted_by` INT UNSIGNED NULL,
  `deleted_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `deleted_records_type_idx` (`record_type`, `id`),
  KEY `deleted_records_when_idx` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
