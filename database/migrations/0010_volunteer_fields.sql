-- 0010: extra volunteer-registration fields present on the live form
--       (guardian name, student/job details, availability days).
ALTER TABLE `volunteers`
  ADD COLUMN `guardian_name` VARCHAR(120) NULL AFTER `last_name`,
  ADD COLUMN `details_json`  LONGTEXT     NULL AFTER `motivation`;
