-- 0021: V3.6 admin panel — Board & team and MOUs & certificates become fully database-driven;
--       inbox "unread" tracking.
--
-- Idempotent: every column/table is created only if missing, and the existing hard-coded website
-- content is copied into the database ONLY when the relevant group/kind has no rows yet, so it is
-- safe to run on a database that already holds board members or MOU records. Nothing is deleted.

-- ---------------------------------------------------------------- inbox: read tracking
SET @s = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `contact_messages` ADD COLUMN `read_at` DATETIME NULL', 'DO 0') FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'contact_messages' AND column_name = 'read_at');
PREPARE st FROM @s;
EXECUTE st;
DEALLOCATE PREPARE st;

SET @s = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `newsletter_subscribers` ADD COLUMN `read_at` DATETIME NULL', 'DO 0') FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'newsletter_subscribers' AND column_name = 'read_at');
PREPARE st FROM @s;
EXECUTE st;
DEALLOCATE PREPARE st;

SET @s = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `volunteers` ADD COLUMN `read_at` DATETIME NULL', 'DO 0') FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'volunteers' AND column_name = 'read_at');
PREPARE st FROM @s;
EXECUTE st;
DEALLOCATE PREPARE st;

SET @s = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `career_applications` ADD COLUMN `read_at` DATETIME NULL', 'DO 0') FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'career_applications' AND column_name = 'read_at');
PREPARE st FROM @s;
EXECUTE st;
DEALLOCATE PREPARE st;

SET @s = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `fundraising_applications` ADD COLUMN `read_at` DATETIME NULL', 'DO 0') FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'fundraising_applications' AND column_name = 'read_at');
PREPARE st FROM @s;
EXECUTE st;
DEALLOCATE PREPARE st;

-- Newsletter sign-ups are 'subscribed' for life, so every existing subscriber would otherwise count as
-- unread. Treat the current list as seen; only sign-ups received from now on show up as unread.
UPDATE `newsletter_subscribers` SET `read_at` = UTC_TIMESTAMP() WHERE `read_at` IS NULL;

-- ---------------------------------------------------------------- board & team
ALTER TABLE `team_profiles` MODIFY `group_key` ENUM('board','honorary','advisor','management','ambassador') NOT NULL DEFAULT 'board';

CREATE TABLE IF NOT EXISTS `team_sections` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `section_key` VARCHAR(30) NOT NULL,
  `tag` VARCHAR(120) NULL,
  `title` VARCHAR(160) NULL,
  `title_accent` VARCHAR(160) NULL,
  `subtitle` VARCHAR(300) NULL,
  `sort_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `status` ENUM('published','hidden') NOT NULL DEFAULT 'published',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `team_sections_key_unique` (`section_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `team_sections` (`section_key`,`tag`,`title`,`title_accent`,`subtitle`,`sort_order`,`status`)
SELECT 'hero','Our People','Our Distinguished','Leadership','Board of Directors · Honorary Members · Ambassadors',0,'published' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `team_sections` WHERE `section_key` = 'hero');
INSERT INTO `team_sections` (`section_key`,`tag`,`title`,`title_accent`,`subtitle`,`sort_order`,`status`)
SELECT 'board','Leadership & Governance','Board of','Directors','Visionary leaders steering Almarah Foundation''s mission forward',10,'published' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `team_sections` WHERE `section_key` = 'board');
INSERT INTO `team_sections` (`section_key`,`tag`,`title`,`title_accent`,`subtitle`,`sort_order`,`status`)
SELECT 'honorary','Distinguished Patrons','Honorary','Members','Distinguished leaders shaping Pakistan''s future',20,'published' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `team_sections` WHERE `section_key` = 'honorary');
INSERT INTO `team_sections` (`section_key`,`tag`,`title`,`title_accent`,`subtitle`,`sort_order`,`status`)
SELECT 'advisor','Guidance & Counsel','Advisory','Board','Experienced advisors guiding our work',30,'published' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `team_sections` WHERE `section_key` = 'advisor');
INSERT INTO `team_sections` (`section_key`,`tag`,`title`,`title_accent`,`subtitle`,`sort_order`,`status`)
SELECT 'management','Day-to-Day Leadership','Management','Team','The team that runs Almarah Foundation every day',40,'published' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `team_sections` WHERE `section_key` = 'management');
INSERT INTO `team_sections` (`section_key`,`tag`,`title`,`title_accent`,`subtitle`,`sort_order`,`status`)
SELECT 'ambassador','Faces of Almarah','Brand & Goodwill','Ambassadors','Celebrated personalities championing our cause',50,'published' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `team_sections` WHERE `section_key` = 'ambassador');

-- seed: board (0)
INSERT INTO `team_profiles` (`group_key`,`name`,`designation`,`bio`,`photo_path`,`sort_order`,`status`)
SELECT x.* FROM (

) x WHERE NOT EXISTS (SELECT 1 FROM `team_profiles` WHERE `group_key` = 'board');

-- seed: honorary (0)
INSERT INTO `team_profiles` (`group_key`,`name`,`designation`,`bio`,`photo_path`,`sort_order`,`status`)
SELECT x.* FROM (

) x WHERE NOT EXISTS (SELECT 1 FROM `team_profiles` WHERE `group_key` = 'honorary');

-- seed: ambassador (0)
INSERT INTO `team_profiles` (`group_key`,`name`,`designation`,`bio`,`photo_path`,`sort_order`,`status`)
SELECT x.* FROM (

) x WHERE NOT EXISTS (SELECT 1 FROM `team_profiles` WHERE `group_key` = 'ambassador');

-- ---------------------------------------------------------------- MOUs & certificates
SET @s = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `partners` ADD COLUMN `category` VARCHAR(160) NULL', 'DO 0') FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'partners' AND column_name = 'category');
PREPARE st FROM @s;
EXECUTE st;
DEALLOCATE PREPARE st;

SET @s = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `partners` ADD COLUMN `subtitle` VARCHAR(255) NULL', 'DO 0') FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'partners' AND column_name = 'subtitle');
PREPARE st FROM @s;
EXECUTE st;
DEALLOCATE PREPARE st;

SET @s = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `partners` ADD COLUMN `body_html` MEDIUMTEXT NULL', 'DO 0') FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'partners' AND column_name = 'body_html');
PREPARE st FROM @s;
EXECUTE st;
DEALLOCATE PREPARE st;

SET @s = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `partners` ADD COLUMN `vision_text` TEXT NULL', 'DO 0') FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'partners' AND column_name = 'vision_text');
PREPARE st FROM @s;
EXECUTE st;
DEALLOCATE PREPARE st;

SET @s = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `partners` ADD COLUMN `date_label` VARCHAR(60) NULL', 'DO 0') FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'partners' AND column_name = 'date_label');
PREPARE st FROM @s;
EXECUTE st;
DEALLOCATE PREPARE st;

-- seed: mou (0)
INSERT INTO `partners` (`kind`,`name`,`category`,`subtitle`,`body_html`,`vision_text`,`date_label`,`logo_path`,`sort_order`,`status`)
SELECT x.* FROM (

) x WHERE NOT EXISTS (SELECT 1 FROM `partners` WHERE `kind` = 'mou');

-- seed: certificates & registrations (0)
INSERT INTO `partners` (`kind`,`name`,`document_path`,`sort_order`,`status`)
SELECT x.* FROM (

) x WHERE NOT EXISTS (SELECT 1 FROM `partners` WHERE `kind` IN ('certificate','registration'));
