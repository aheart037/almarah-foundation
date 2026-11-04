-- V3 Part 7: existing role/permission tables already support configurable RBAC.
-- Mark installations with legacy grants so a subsequent seeder cannot silently
-- restore permissions intentionally revoked by an administrator.
INSERT IGNORE INTO site_settings (setting_key, setting_value)
SELECT 'rbac.defaults_seeded', '1' WHERE EXISTS (SELECT 1 FROM roles);
