<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Core\Database;
use App\Core\Logger;

/**
 * Reference content for the main website (tables from migration 0009).
 *
 * Everything here is REAL content taken from almarah.org at the time of the
 * migration — slider images, donation purposes, bank accounts and published
 * financial reports. No demo data. The seeder is idempotent: a row is only
 * inserted when its natural key is missing, so re-running it after an admin
 * has edited content never overwrites their changes.
 */
final class SiteContentSeeder
{
    public function __construct(private Database $db, private Logger $logger)
    {
    }

    public function run(): int
    {
        $count = 0;
        $count += $this->slides();
        $count += $this->purposes();
        $count += $this->banks();
        $count += $this->reports();
        $count += $this->permissions();
        $count += $this->photos();
        return $count;
    }

    private function slides(): int
    {
        if ($this->db->int('SELECT COUNT(*) FROM cms_slides') > 0) {
            return 0;
        }
        $n = 0;
        foreach ([1, 2, 3, 4] as $i) {
            $this->db->insert('cms_slides', [
                'title'      => null,
                'image_path' => 'assets/images/banners/Header-' . $i . '.jpg',
                'image_alt'  => 'Al Marah Foundation',
                'is_active'  => 1,
                'sort_order' => $i,
            ]);
            $n++;
        }
        return $n;
    }

    private function purposes(): int
    {
        $rows = [
            ['general-fund',        'General Fund',        'Where it is needed most across all our programmes.', 1, 1],
            ['zakat',               'Zakat',               'Shariah-compliant Zakat, distributed to eligible recipients only.', 1, 2],
            ['sadaqah',             'Sadaqah',             'Voluntary charity for any of our programmes.', 1, 3],
            ['parent-the-orphan',   'Parent the Orphan',   'Sponsor the care, schooling and wellbeing of an orphaned child.', 1, 4],
            ['feed-the-hungry',     'Feed the Hungry',     'Cooked meals and monthly ration packs for families in need.', 1, 5],
            ['izzat-ki-roti',       'Izzat Ki Roti',       'Dignified daily bread for the deserving.', 1, 6],
            ['educate-pakistan',    'Educate Pakistan',    'School fees, uniforms and books for underprivileged students.', 1, 7],
            ['compassionate-haven', 'Compassionate Haven', 'Shelter and care for the homeless and abandoned.', 1, 8],
            ['qurbani',             'Qurbani',             'Qurbani performed and distributed on your behalf.', 0, 9],
        ];
        $n = 0;
        foreach ($rows as [$slug, $name, $desc, $zakat, $sort]) {
            if ($this->db->int('SELECT COUNT(*) FROM donation_purposes WHERE slug = :s', ['s' => $slug]) > 0) {
                continue;
            }
            $this->db->insert('donation_purposes', [
                'slug' => $slug, 'name' => $name, 'description' => $desc,
                'is_zakat_eligible' => $zakat, 'sort_order' => $sort, 'status' => 'active',
            ]);
            $n++;
        }
        return $n;
    }

    private function banks(): int
    {
        // bank_name, account_type, account_number, iban, swift/sort, currency, logo, international, sort
        $rows = [
            ['JS Bank Limited',           'JSB · Conventional Banking', '1828004',          'PK65JSBL9561000001828004', null,       'PKR', 'assets/images/icons/jsbank.png',            0, 1],
            ['United Bank Limited',       'UBL · Conventional Banking', '1121289906918',    'PK54UNIL0109000289906918', null,       'PKR', 'assets/images/icons/ubl.jpg',               0, 2],
            ['National Bank of Pakistan', 'NBP · Conventional Banking', '3002565431',       'PK80NBPA2142003002565431', null,       'PKR', 'assets/images/icons/nationalbank.png',      0, 3],
            ['Soneri Bank Limited',       'SNBL · Conventional Banking', '20010876495',     'PK76SONE0025320010876495', null,       'PKR', 'assets/images/icons/Soneri_Bank.png',       0, 4],
            ['Allied Bank Limited',       'ABL · Conventional Banking', '0010129951530014', 'PK16ABPA0010129951530014', null,       'PKR', 'assets/images/icons/allied-bank.jpg',       0, 5],
            ['Bank Alfalah',              'BAFL · Conventional Banking', '56885002076517',  'PK98ALFH5688005002076517', null,       'PKR', 'assets/images/icons/Bank-Alfalah.jpg',      0, 6],
            ['Bank Islami Pakistan',      'BIPL · Islamic Banking',     '216300085400001',  'PK11BKIP0216300085400001', null,       'PKR', 'assets/images/icons/bankislami.png',        0, 7],
            ['Meezan Bank',               'MEBL · General Donations',   '02160113593207',   'PK71MEZN0002160113593207', null,       'PKR', 'assets/images/icons/meezan-logo.png',       0, 8],
            ['Meezan Bank',               'MEBL · Zakat Account',       '02160113593207',   'PK71MEZN0002160113593207', null,       'PKR', 'assets/images/icons/meezan-logo.png',       0, 9],
            ['Metro Bank',                'United Kingdom · GBP',       '54233566',         'GB83MYMB23058054233566',   '23-05-80', 'GBP', 'assets/images/icons/Metro_Bank_Logo.png',   1, 10],
            ['Zelle',                     'United States · USD',        '+1 571 300 4508',  null,                       null,       'USD', 'assets/images/icons/image-600x338-1.jpg',   1, 11],
        ];
        $n = 0;
        foreach ($rows as [$bank, $type, $acc, $iban, $swift, $cur, $logo, $intl, $sort]) {
            $exists = $this->db->int(
                'SELECT COUNT(*) FROM bank_accounts WHERE bank_name = :b AND account_number = :a AND account_type = :t',
                ['b' => $bank, 'a' => $acc, 't' => $type]
            );
            if ($exists > 0) {
                continue;
            }
            $this->db->insert('bank_accounts', [
                'bank_name' => $bank, 'account_title' => 'Almarah Foundation', 'account_number' => $acc,
                'iban' => $iban, 'swift_code' => $swift, 'currency' => $cur, 'account_type' => $type,
                'logo_path' => $logo, 'is_international' => $intl, 'sort_order' => $sort, 'status' => 'published',
            ]);
            $n++;
        }
        return $n;
    }

    private function reports(): int
    {
        $rows = [
            ['Audit Report 2025–2026',          '2025-26', 'assets/documents/Audit-Report-Almarah-Foundation-2025-2026.pdf', 1],
            ['Financial Report 2024–2025',      '2024-25', 'assets/documents/Alamarah-Foundation-Financial-Report-24-25.pdf', 2],
            ['Audit Report & Financials 2024',  '2023-24', 'assets/documents/Audit-Report-and-Financial-June-2024.pdf', 3],
            ['Audit Report & Financials 2023',  '2022-23', 'assets/documents/Audit-Report-and-Financial-June-2023.pdf', 4],
            ['Audit Report & Financials 2022',  '2021-22', 'assets/documents/Audit-Report-and-Financial-June-2022.pdf', 5],
            ['Audit Report & Financials 2021',  '2020-21', 'assets/documents/Audit-Report-and-Financial-June-2021.pdf', 6],
        ];
        $n = 0;
        foreach ($rows as [$title, $year, $path, $sort]) {
            if ($this->db->int('SELECT COUNT(*) FROM financial_reports WHERE document_path = :p', ['p' => $path]) > 0) {
                continue;
            }
            $abs = dirname(__DIR__, 2) . '/' . $path;
            $this->db->insert('financial_reports', [
                'title' => $title, 'fiscal_year' => $year, 'document_path' => $path,
                'size_bytes' => is_file($abs) ? filesize($abs) : null, 'sort_order' => $sort, 'status' => 'published',
            ]);
            $n++;
        }
        return $n;
    }

    /** Starter photo gallery built from the photographs already published on the site. */
    private function photos(): int
    {
        if ($this->db->int("SELECT COUNT(*) FROM media_items WHERE kind = 'photo'") > 0) {
            return 0;
        }
        $rows = [
            ['Apna Ghar',          'assets/images/about/House-01.JPG-scaled.jpeg', 'Almarah Apna Ghar home'],
            ['Apna Ghar',          'assets/images/about/House-03-scaled.jpg', 'Almarah Apna Ghar home'],
            ['Apna Ghar',          'assets/images/about/House-07.JPG-scaled.jpeg', 'Almarah Apna Ghar home'],
            ['Apna Ghar',          'assets/images/about/House-09.JPG-scaled.jpeg', 'Almarah Apna Ghar home'],
            ['Apna Ghar',          'assets/images/about/ACH.JPG-scaled.jpeg', 'Almarah Children Home'],
            ['Parent the Orphan',  'assets/images/projects/574562735_780123825008305_8780815025921419235_n.jpg', 'Recreational outing'],
            ['Parent the Orphan',  'assets/images/projects/574608100_786752841012070_3088578215458389128_n.jpg', 'Sports competition'],
            ['Parent the Orphan',  'assets/images/projects/528195919_710043962016292_4721608384551703179_n.jpg', 'Creative activities'],
            ['Parent the Orphan',  'assets/images/projects/576552392_786752884345399_5457353216090500727_n.jpg', 'Emotional support programme'],
            ['Feed the Hungry',    'assets/images/projects/DSC05009-scaled.jpg', 'Feed the Hungry'],
            ['Feed the Hungry',    'assets/images/projects/hotmeal.jpg', 'Daily hot meals'],
            ['Feed the Hungry',    'assets/images/projects/DSC02236-1024x683-1.jpg', 'Monthly ration packs'],
            ['Feed the Hungry',    'assets/images/projects/7C6A2552-1024x683-1.jpg', 'Financial stipends'],
            ['Educate Pakistan',   'assets/images/projects/DSC05761-scaled.jpg', 'Almarah Grammar School'],
            ['Educate Pakistan',   'assets/images/projects/497903312_122208465626104528_6036840965113577816_n.jpg', 'Educate Pakistan'],
        ];
        $n = 0;
        foreach ($rows as $i => [$album, $path, $title]) {
            if (!is_file(dirname(__DIR__, 2) . '/' . $path)) {
                continue;
            }
            $this->db->insert('media_items', [
                'kind' => 'photo', 'title' => $title, 'file_path' => $path, 'alt_text' => $title,
                'album' => $album, 'status' => 'published', 'sort_order' => $i + 1,
            ]);
            $n++;
        }
        return $n;
    }

    /** Permission catalogue + role grants for the unified admin panel. */
    private function permissions(): int
    {
        $perms = [
            'dashboard.view'      => 'View dashboard',
            'content.manage'      => 'Manage pages, slider, banners, menus, media and SEO',
            'donations.view'      => 'View donations and transactions',
            'donations.manage'    => 'Reconcile, refund and adjust donations',
            'volunteers.manage'   => 'Manage volunteers and applications',
            'forms.view'          => 'View contact, newsletter, careers and fundraising submissions',
            'reports.view'        => 'View and export reports',
            'finance.manage'      => 'Run reconciliation and record financial adjustments',
            'users.manage'        => 'Manage admin users and roles',
            'settings.manage'     => 'Manage site settings, SMTP and payment configuration',
            'logs.view'           => 'View activity, email and payment logs',
            'backups.manage'      => 'Create and download backups',
        ];
        foreach (['dashboard','donations','donors','grants','grant_assignments','reports','finance','users','roles','settings'] as $group) {
            foreach (['view','create','edit','delete','manage'] as $action) {
                $key = $group . '.' . $action;
                $perms[$key] = ucfirst(str_replace('_', ' ', $action . ' ' . $group)) . (in_array($group,['grants','grant_assignments'],true) ? ' (reserved: no grant workflow)' : '');
            }
        }
        $perms['reports.export'] = 'Export reports';
        $perms['donations.record'] = 'Record offline donations';
        $perms['donations.verify'] = 'Verify or reject donations (finance)';
        $perms['users.delete'] = 'Delete users (reserved: accounts can only be suspended)';
        $perms['donations.delete'] = 'Delete donations (reserved: financial records cannot be deleted)';
        $perms['finance.delete'] = 'Delete financial records (reserved: records cannot be deleted)';
        $roles = [
            'super_admin'       => array_keys($perms),
            'admin'             => ['dashboard.view', 'content.manage', 'donations.view', 'donations.manage', 'donations.record', 'volunteers.manage', 'forms.view', 'reports.view', 'logs.view', 'users.view', 'users.create', 'users.edit', 'roles.view'],
            'finance_manager'   => ['dashboard.view', 'donations.view', 'donations.manage', 'donations.record', 'donations.verify', 'reports.view', 'finance.manage', 'logs.view'],
            'content_manager'   => ['dashboard.view', 'content.manage', 'forms.view'],
            'volunteer_manager' => ['dashboard.view', 'volunteers.manage', 'forms.view'],
            'initial_reviewer' => ['dashboard.view', 'donations.view', 'donations.record'],
            'initial_review' => ['dashboard.view', 'donations.view', 'donations.record'],
            'verification_officer' => ['dashboard.view', 'donations.view'],
            'viewer' => ['dashboard.view'],
            'grant_manager' => ['dashboard.view','grants.view','grants.create','grants.edit','grant_assignments.view','grant_assignments.manage','users.view'],
            'donation_manager' => ['dashboard.view','donations.view','donations.manage','donations.record','donors.view'],
            'reporting_officer' => ['dashboard.view','reports.view','reports.export'],
        ];
        $n = 0;
        foreach ($perms as $key => $label) {
            if ($this->db->int('SELECT COUNT(*) FROM permissions WHERE name = :k', ['k' => $key]) === 0) {
                $this->db->insert('permissions', ['name' => $key, 'group_name' => explode('.', $key)[0], 'label' => $label]);
                $n++;
            }
        }
        // Once seeded, never recreate an administrator's deliberately revoked grants.
        if ($this->db->int("SELECT COUNT(*) FROM site_settings WHERE setting_key = 'rbac.defaults_seeded'") > 0) {
            return $n;
        }
        foreach ($roles as $roleSlug => $keys) {
            $roleId = $this->db->scalar('SELECT id FROM roles WHERE name = :s', ['s' => $roleSlug]);
            if ($roleId === null) {
                continue;
            }
            foreach ($keys as $key) {
                $permId = $this->db->scalar('SELECT id FROM permissions WHERE name = :k', ['k' => $key]);
                if ($permId === null) {
                    continue;
                }
                $n += $this->db->insertIgnore('role_permissions', ['role_id' => (int) $roleId, 'permission_id' => (int) $permId]) ? 1 : 0;
            }
        }
        $this->db->insertIgnore('site_settings', ['setting_key' => 'rbac.defaults_seeded', 'setting_value' => '1']);
        return $n;
    }
}
