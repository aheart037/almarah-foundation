-- 0011: offline (bank transfer / cash / imported) donations have no payment
--       gateway, so gateway_id becomes optional; plus bank reference and the
--       staff member who recorded it. Single statement so a partial run can
--       never leave the table half-migrated. (Indexes on source/purpose
--       already exist from 0009.)
ALTER TABLE `donations`
  MODIFY `gateway_id` INT UNSIGNED NULL,
  ADD COLUMN `bank_reference` VARCHAR(120) NULL AFTER `legacy_order_id`,
  ADD COLUMN `recorded_by` INT UNSIGNED NULL AFTER `bank_reference`;
