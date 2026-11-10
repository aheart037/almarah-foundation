-- Extend the original inbox tables. Keep old ENUM values during transition so
-- historical rows can be mapped without data loss; never replace these tables.
ALTER TABLE `contact_messages`
  MODIFY COLUMN `status` ENUM('new','in_progress','responded','closed','spam','read','replied','archived') NOT NULL DEFAULT 'new',
  ADD COLUMN `assigned_to` INT UNSIGNED NULL,
  ADD INDEX `contact_assigned_idx` (`assigned_to`, `status`),
  ADD CONSTRAINT `fk_contact_assigned` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `volunteers`
  MODIFY COLUMN `status` ENUM('pending','under_review','approved','rejected','active','inactive') NOT NULL DEFAULT 'pending',
  ADD COLUMN `campaign_id` INT UNSIGNED NULL,
  ADD COLUMN `team_id` INT UNSIGNED NULL,
  ADD INDEX `volunteer_campaign_idx` (`campaign_id`, `status`),
  ADD INDEX `volunteer_team_idx` (`team_id`, `status`),
  ADD CONSTRAINT `fk_volunteer_campaign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_volunteer_team` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL;

CREATE TABLE IF NOT EXISTS `form_case_events` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `contact_id` INT UNSIGNED NULL,
  `volunteer_id` INT UNSIGNED NULL,
  `actor_user_id` INT UNSIGNED NULL,
  `event_type` VARCHAR(30) NOT NULL,
  `from_status` VARCHAR(30) NULL,
  `to_status` VARCHAR(30) NULL,
  `note` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `case_contact_idx` (`contact_id`, `id`),
  KEY `case_volunteer_idx` (`volunteer_id`, `id`),
  KEY `case_actor_idx` (`actor_user_id`, `created_at`),
  CONSTRAINT `fk_case_contact` FOREIGN KEY (`contact_id`) REFERENCES `contact_messages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_case_volunteer` FOREIGN KEY (`volunteer_id`) REFERENCES `volunteers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_case_actor` FOREIGN KEY (`actor_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `chk_case_one_owner` CHECK ((`contact_id` IS NULL) <> (`volunteer_id` IS NULL))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Preserve the state present at migration time before renaming old contact statuses.
INSERT INTO `form_case_events` (`contact_id`,`event_type`,`to_status`,`note`,`created_at`)
  SELECT `id`, 'imported', `status`, 'Pre-V2 contact state', `created_at` FROM `contact_messages`;
INSERT INTO `form_case_events` (`volunteer_id`,`event_type`,`to_status`,`note`,`created_at`)
  SELECT `id`, 'imported', `status`, 'Pre-V2 volunteer state', `created_at` FROM `volunteers`;
UPDATE `contact_messages` SET `status` = CASE `status`
  WHEN 'read' THEN 'in_progress' WHEN 'replied' THEN 'responded'
  WHEN 'archived' THEN 'closed' ELSE `status` END
  WHERE `status` IN ('read','replied','archived');
