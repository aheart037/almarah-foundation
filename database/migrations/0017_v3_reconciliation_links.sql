-- Part 9: non-destructive relationship hardening for reconciliation history.
-- Run preflight first; do not rewrite/erase existing orphaned rows.
ALTER TABLE `reconciliation_items`
  DROP FOREIGN KEY `fk_ri_run`,
  ADD CONSTRAINT `fk_ri_run_preserved` FOREIGN KEY (`run_id`) REFERENCES `reconciliation_runs` (`id`) ON DELETE RESTRICT,
  ADD INDEX `ri_donation_link_idx` (`donation_id`),
  ADD INDEX `ri_transaction_link_idx` (`payment_transaction_id`),
  ADD INDEX `ri_resolver_link_idx` (`resolved_by`),
  ADD CONSTRAINT `fk_ri_donation` FOREIGN KEY (`donation_id`) REFERENCES `donations` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_ri_transaction` FOREIGN KEY (`payment_transaction_id`) REFERENCES `payment_transactions` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_ri_resolver` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
