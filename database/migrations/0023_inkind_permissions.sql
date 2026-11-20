-- 0023: dedicated permissions for the In-kind (goods) donations section, so it can be granted to roles
--       on Admin > Roles & permissions > Edit (group "In Kind").
--
--   inkind.view    open the In-kind donations list and detail pages, download certificates and photos
--   inkind.verify  verify / reject / reopen a submission, generate and re-send certificates
--
-- Idempotent. Nothing is removed and nobody loses access they have today:
--   * super_admin and finance_manager receive both permissions;
--   * every role that already holds donations.view gets inkind.view, and every role that holds
--     donations.verify gets inkind.verify (this is how access worked before this change).
-- After running it, untick/tick the two new permissions per role as you wish.

INSERT IGNORE INTO `permissions` (`name`, `group_name`, `label`) VALUES
  ('inkind.view',   'in_kind', 'View in-kind (goods) donations'),
  ('inkind.verify', 'in_kind', 'Verify or reject in-kind donations and issue certificates');

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r JOIN `permissions` p ON p.name IN ('inkind.view', 'inkind.verify')
WHERE r.name IN ('super_admin', 'finance_manager');

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT rp.role_id, np.id FROM `role_permissions` rp
JOIN `permissions` op ON op.id = rp.permission_id AND op.name = 'donations.view'
JOIN `permissions` np ON np.name = 'inkind.view';

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT rp.role_id, np.id FROM `role_permissions` rp
JOIN `permissions` op ON op.id = rp.permission_id AND op.name = 'donations.verify'
JOIN `permissions` np ON np.name = 'inkind.verify';
