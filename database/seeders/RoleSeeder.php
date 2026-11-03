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
        $roles = [
            ['name' => 'super_admin', 'label' => 'Super Administrator', 'description' => 'Full access including settings, gateways and role management.'],
            ['name' => 'admin',       'label' => 'Administrator',       'description' => 'Moderates fundraisers, manages donations and users.'],
            ['name' => 'finance_manager',   'label' => 'Finance Manager',   'description' => 'Donations, reconciliation, financial adjustments and reports.'],
            ['name' => 'content_manager',   'label' => 'Content Manager',   'description' => 'Pages, slider, banners, menus, media and SEO.'],
            ['name' => 'volunteer_manager', 'label' => 'Volunteer Manager', 'description' => 'Volunteer applications and volunteer records.'],
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

        return $count;
    }
}
