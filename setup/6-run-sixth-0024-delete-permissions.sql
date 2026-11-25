-- RUN SIXTH: copy of database/migrations/0024_delete_permissions.sql (run once in phpMyAdmin / SQL console).
-- 0024: permissions for deleting junk records (test / spam / abandoned entries).
--
--   donations.delete  delete a donation that never became a confirmed payment (pending, failed, cancelled,
--                     abandoned and not verified). Paid / refunded / verified / receipted donations can
--                     NEVER be deleted, whatever the permission - the application and the database refuse.
--   inkind.delete     delete a pending or rejected in-kind (goods) submission and its photos.
--   forms.delete      delete contact, newsletter, volunteer, job-application and fundraising-enquiry entries
--                     (and an attached CV). Before this migration any staff member could delete some of these;
--                     now it is a permission you can grant per role.
--
-- Idempotent. Super Admin always has every permission. To avoid surprising anyone:
--   * forms.delete is granted to every role that already holds forms.view or volunteers.manage;
--   * inkind.delete is granted to roles holding inkind.verify;
--   * donations.delete is granted to Super Admin only (tick it for other roles on Roles & permissions > Edit).

INSERT IGNORE INTO `permissions` (`name`, `group_name`, `label`) VALUES
  ('donations.delete', 'donations', 'Delete junk donations (never paid, verified or refunded ones)'),
  ('inkind.delete',    'in_kind',   'Delete pending or rejected in-kind donations'),
  ('forms.delete',     'forms',     'Delete website form entries (contact, newsletter, volunteers, careers, fundraising)');

-- The reserved donations.delete label from the original seeder said records could not be deleted; update it.
UPDATE `permissions` SET `label` = 'Delete junk donations (never paid, verified or refunded ones)' WHERE `name` = 'donations.delete';

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r JOIN `permissions` p ON p.name IN ('donations.delete', 'inkind.delete', 'forms.delete')
WHERE r.name = 'super_admin';

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT DISTINCT rp.role_id, np.id FROM `role_permissions` rp
JOIN `permissions` op ON op.id = rp.permission_id AND op.name IN ('forms.view', 'volunteers.manage')
JOIN `permissions` np ON np.name = 'forms.delete';

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT DISTINCT rp.role_id, np.id FROM `role_permissions` rp
JOIN `permissions` op ON op.id = rp.permission_id AND op.name = 'inkind.verify'
JOIN `permissions` np ON np.name = 'inkind.delete';
