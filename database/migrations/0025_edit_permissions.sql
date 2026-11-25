-- 0025: permissions for editing records after they were entered.
--
--   donations.edit  edit a donation that staff recorded offline (bank transfer, cash, cheque, Zelle...).
--                   Until it is verified every field can be changed (review then restarts); once it is
--                   verified / receipted / refunded only the donor email, phone and internal note can be
--                   corrected. Online (gateway) payments can never be edited.
--   inkind.edit     edit an in-kind (goods) donation: donor, items, hand-over, acknowledgment, referrer.
--
-- Idempotent. Super Admin always has every permission. To avoid surprising anyone:
--   * donations.edit is granted to Super Admin only (tick it for other roles on Roles & permissions > Edit);
--   * inkind.edit is granted to roles that already hold inkind.verify.

INSERT IGNORE INTO `permissions` (`name`, `group_name`, `label`) VALUES
  ('donations.edit', 'donations', 'Edit offline (staff-recorded) donations'),
  ('inkind.edit',    'in_kind',   'Edit in-kind (goods) donations');

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r JOIN `permissions` p ON p.name IN ('donations.edit', 'inkind.edit')
WHERE r.name = 'super_admin';

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT DISTINCT rp.role_id, np.id FROM `role_permissions` rp
JOIN `permissions` op ON op.id = rp.permission_id AND op.name = 'inkind.verify'
JOIN `permissions` np ON np.name = 'inkind.edit';
