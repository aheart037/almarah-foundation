-- Part 9: forward-only integrity hardening. No data changes or deletions.
-- These existing historical payment rows must survive accidental parent deletes.
ALTER TABLE `payment_transactions`
  DROP FOREIGN KEY `fk_pt_donation`,
  ADD CONSTRAINT `fk_pt_donation_preserved` FOREIGN KEY (`donation_id`) REFERENCES `donations` (`id`) ON DELETE RESTRICT;

ALTER TABLE `payment_callbacks`
  DROP FOREIGN KEY `fk_pc_transaction`,
  ADD CONSTRAINT `fk_pc_transaction_preserved` FOREIGN KEY (`payment_transaction_id`) REFERENCES `payment_transactions` (`id`) ON DELETE RESTRICT;

ALTER TABLE `payment_refunds`
  DROP FOREIGN KEY `fk_pr_transaction`,
  DROP FOREIGN KEY `fk_pr_donation`,
  ADD CONSTRAINT `fk_pr_transaction_preserved` FOREIGN KEY (`payment_transaction_id`) REFERENCES `payment_transactions` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_pr_donation_preserved` FOREIGN KEY (`donation_id`) REFERENCES `donations` (`id`) ON DELETE RESTRICT;

-- Historical adjustments already carry a transaction id, but earlier schemas
-- did not constrain it. A preflight must confirm no orphan IDs before applying.
ALTER TABLE `financial_adjustments`
  ADD INDEX `fa_payment_transaction_idx` (`payment_transaction_id`),
  ADD CONSTRAINT `fk_fadj_transaction` FOREIGN KEY (`payment_transaction_id`) REFERENCES `payment_transactions` (`id`) ON DELETE RESTRICT;
