<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Core\Database;
use App\Core\Logger;

final class RoleSeeder
{
    public function __construct(private Database $db, private Logger $logger)
    {
    }

    public function run(): int
    {
        // Only seed the role catalogue once; never resurrect a deliberately deleted role.
        if ($this->db->int("SELECT COUNT(*) FROM site_settings WHERE setting_key='rbac.role_catalog_seeded'") > 0) {
            return 0;
        }
        $roles = [
            ['name' => 'super_admin', 'label' => 'Super Administrator', 'description' => 'Full access including settings, gateways and role management.'],
            ['name' => 'admin',       'label' => 'Administrator',       'description' => 'Moderates fundraisers, manages donations and users.'],
            ['name' => 'finance_manager',   'label' => 'Finance Manager',   'description' => 'Donations, reconciliation, financial adjustments and reports.'],
            ['name' => 'content_manager',   'label' => 'Content Manager',   'description' => 'Pages, slider, banners, menus, media and SEO.'],
            ['name' => 'volunteer_manager', 'label' => 'Volunteer Manager', 'description' => 'Volunteer applications and volunteer records.'],
            ['name' => 'initial_reviewer', 'label' => 'Initial Reviewer', 'description' => 'Existing first-pass reviewer role; retained for current staff.'],
            ['name' => 'initial_review', 'label' => 'Initial Review', 'description' => 'First-pass donation review role; equivalent to the existing initial_reviewer role.'],
            ['name' => 'verification_officer', 'label' => 'Verification Officer', 'description' => 'Verifies submitted donation evidence.'],
            ['name' => 'grant_manager', 'label' => 'Grant Manager', 'description' => 'Grant permissions reserved until a grant workflow exists.'],
            ['name' => 'donation_manager', 'label' => 'Donation Manager', 'description' => 'Donation review and donor read access.'],
            ['name' => 'reporting_officer', 'label' => 'Reporting Officer', 'description' => 'Reports and authorized exports.'],
            ['name' => 'viewer', 'label' => 'Viewer', 'description' => 'Dashboard read access.'],
            ['name' => 'donor',             'label' => 'Donor',             'description' => 'Can donate and view their own donation history.'],
        ];

        $count = 0;
        foreach ($roles as $role) {
            $exists = $this->db->int('SELECT COUNT(*) FROM roles WHERE name = :n', ['n' => $role['name']]);
            if ($exists > 0) {
                continue;
            }
            $this->db->insert('roles', $role);
            $count++;
        }

        $this->db->insertIgnore('site_settings', ['setting_key'=>'rbac.role_catalog_seeded','setting_value'=>'1']);
        return $count;
    }
}
