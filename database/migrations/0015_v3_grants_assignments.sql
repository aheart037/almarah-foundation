-- New minimal grant register: no previous grant tables/records were present.
-- No amounts, payments or beneficiary data are inferred or imported.
CREATE TABLE IF NOT EXISTS `grants` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `reference` VARCHAR(50) NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `description` TEXT NULL,
  `status` ENUM('draft','active','closed') NOT NULL DEFAULT 'draft',
  `created_by` INT UNSIGNED NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `grants_reference_unique` (`reference`),
  KEY `grants_status_idx` (`status`),
  CONSTRAINT `fk_grant_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `grant_user_assignments` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `grant_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `assigned_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `assigned_by` INT UNSIGNED NULL,
  `status` ENUM('active','on_hold','completed','removed') NOT NULL DEFAULT 'active',
  `responsibilities` VARCHAR(1000) NULL,
  `notes` TEXT NULL,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `grant_user_unique` (`grant_id`,`user_id`),
  KEY `grant_assignment_user_idx` (`user_id`,`status`),
  KEY `grant_assignment_actor_idx` (`assigned_by`),
  CONSTRAINT `fk_assignment_grant` FOREIGN KEY (`grant_id`) REFERENCES `grants` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_assignment_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_assignment_actor` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Immutable snapshots of assignment changes; reassignment can safely reuse the
-- unique grant/user pair without erasing earlier responsibilities or notes.
CREATE TABLE IF NOT EXISTS `grant_assignment_events` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `assignment_id` BIGINT UNSIGNED NOT NULL,
  `actor_user_id` INT UNSIGNED NULL,
  `action` ENUM('assigned','updated','removed','reassigned','transferred') NOT NULL,
  `from_status` VARCHAR(20) NULL,
  `to_status` VARCHAR(20) NULL,
  `responsibilities_before` VARCHAR(1000) NULL,
  `responsibilities_after` VARCHAR(1000) NULL,
  `notes_before` TEXT NULL,
  `notes_after` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `grant_assignment_event_idx` (`assignment_id`,`id`),
  CONSTRAINT `fk_grant_event_assignment` FOREIGN KEY (`assignment_id`) REFERENCES `grant_user_assignments` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_grant_event_actor` FOREIGN KEY (`actor_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
