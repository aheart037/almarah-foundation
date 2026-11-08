-- 0019: Donation management Part 1 (idempotent; no existing donation, amount or status is modified).
--  * donations.bank_account_id  : bank account the donor paid into / staff deposited into
--  * donations.deposited_at     : cash deposit date (cash is collected and deposited in one entry)
--  * ledger indexes
--  * permissions donations.record (staff entry) and donations.verify (finance verification)

SET @s = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `donations` ADD COLUMN `bank_account_id` INT UNSIGNED NULL', 'DO 0') FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'donations' AND column_name = 'bank_account_id');
PREPARE st FROM @s;
EXECUTE st;
DEALLOCATE PREPARE st;

SET @s = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `donations` ADD COLUMN `deposited_at` DATE NULL', 'DO 0') FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'donations' AND column_name = 'deposited_at');
PREPARE st FROM @s;
EXECUTE st;
DEALLOCATE PREPARE st;

SET @s = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `donations` ADD INDEX `donations_bank_account_idx` (`bank_account_id`)', 'DO 0') FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'donations' AND index_name = 'donations_bank_account_idx');
PREPARE st FROM @s;
EXECUTE st;
DEALLOCATE PREPARE st;

SET @s = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `donations` ADD CONSTRAINT `fk_donations_bank_account` FOREIGN KEY (`bank_account_id`) REFERENCES `bank_accounts` (`id`) ON DELETE SET NULL', 'DO 0') FROM information_schema.table_constraints WHERE table_schema = DATABASE() AND table_name = 'donations' AND constraint_name = 'fk_donations_bank_account');
PREPARE st FROM @s;
EXECUTE st;
DEALLOCATE PREPARE st;

INSERT IGNORE INTO `permissions` (`name`, `group_name`, `label`) VALUES
  ('donations.record', 'donations', 'Record offline donations'),
  ('donations.verify', 'donations', 'Verify or reject donations (finance)');

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.name = 'donations.verify' WHERE r.name IN ('super_admin', 'finance_manager');

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.name = 'donations.record' WHERE r.name IN ('super_admin', 'admin', 'finance_manager', 'donation_manager', 'initial_reviewer', 'initial_review');
