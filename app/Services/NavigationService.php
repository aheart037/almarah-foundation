<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;

/**
 * Site navigation (header groups with dropdowns, footer columns, CTA buttons).
 *
 * Stored as JSON in site_settings under 'navigation.<location>'. Falls back to
 * the built-in structure when nothing is stored, so the website can never end
 * up without a menu. Two levels deep: group -> items.
 *
 * Item shape: ['label' => string, 'url' => string, 'newtab' => bool,
 *              'children' => [ ...items ]]
 * URLs may be absolute (https://...) or a site path ('about', 'donate').
 */
final class NavigationService
{
    public const LOCATIONS = [
        'header'       => 'Header (main menu)',
        'footer_quick' => 'Footer – Quick Links',
        'footer_more'  => 'Footer – Get Involved',
    ];

    public const MAX_DEPTH = 2;
    public const MAX_ITEMS = 60;

    /** @var array<string,array<int,array<string,mixed>>> */
    private array $cache = [];

    public function __construct(private SettingsService $settings)
    {
    }

    /** @return array<int,array<string,mixed>> */
    public function defaults(string $location): array
    {
        $fundraise = (string) Config::get('app.fundraise.url', 'https://fundraise.almarah.org');

        return match ($location) {
            'header' => [
                ['label' => 'About Us', 'url' => 'about', 'children' => [
                    ['label' => "Chairperson's Message",        'url' => 'chairpersons-message'],
                    ['label' => "CEO's Message",                'url' => 'ceos-message'],
                    ['label' => 'Board Members',                'url' => 'members'],
                    ['label' => 'Financial Reports',            'url' => 'financial-reports'],
                    ['label' => 'Certificates & Registrations', 'url' => 'registrations-mou'],
                    ['label' => 'Memorandum of Understanding',  'url' => 'mous'],
                ]],
                ['label' => 'Projects', 'url' => 'projects', 'children' => [
                    ['label' => 'Parent the Orphan',   'url' => 'parent-the-orphan'],
                    ['label' => 'Feed the Hungry',     'url' => 'feed-the-hungry-2'],
                    ['label' => 'Izzat Ki Roti',       'url' => 'izat-ki-roti'],
                    ['label' => 'Educate Pakistan',    'url' => 'educate-pakistan'],
                    ['label' => 'Compassionate Haven', 'url' => 'https://almarahhaven.org/', 'newtab' => true],
                ]],
                ['label' => 'Media', 'url' => '#', 'children' => [
                    ['label' => 'E-book', 'url' => 'e-book'],
                    ['label' => 'Photos', 'url' => 'photos'],
                    ['label' => 'Videos', 'url' => 'videos'],
                ]],
                ['label' => 'Get Involved', 'url' => '#', 'children' => [
                    ['label' => 'Become a Volunteer', 'url' => 'become-a-volunteer'],
                    ['label' => 'Start Fundraising',  'url' => $fundraise . '/', 'newtab' => true],
                    ['label' => 'Careers',            'url' => 'careers'],
                    ['label' => 'Newsletter Signup',  'url' => 'newsletter'],
                    ['label' => 'Bank Details',       'url' => 'banks'],
                ]],
                ['label' => 'Contact', 'url' => 'contact'],
            ],
            'footer_quick' => [
                ['label' => 'About Us',                 'url' => 'about'],
                ['label' => 'Our Projects',             'url' => 'projects'],
                ['label' => 'E-Book',                   'url' => 'e-book'],
                ['label' => 'Financial Reports',        'url' => 'financial-reports'],
                ['label' => 'Sophiya Warraich',         'url' => 'https://sophiyawaraich.com', 'newtab' => true],
                ['label' => 'Almarah Grammar School',   'url' => 'https://almarah.edu.pk/', 'newtab' => true],
                ['label' => 'Compassionate Haven',      'url' => 'https://almarahhaven.org/', 'newtab' => true],
            ],
            'footer_more' => [
                ['label' => 'Donate Now',          'url' => 'donate'],
                ['label' => 'Start Fundraising',   'url' => $fundraise . '/', 'newtab' => true],
                ['label' => 'Become a Volunteer',  'url' => 'become-a-volunteer'],
                ['label' => 'Careers',             'url' => 'careers'],
                ['label' => 'Newsletter',          'url' => 'newsletter'],
                ['label' => 'Bank Details',        'url' => 'banks'],
                ['label' => 'Contact Us',          'url' => 'contact'],
            ],
            default => [],
        };
    }

    /** @return array<int,array<string,mixed>> normalised items with resolved hrefs */
    public function items(string $location): array
    {
        if (isset($this->cache[$location])) {
            return $this->cache[$location];
        }

        $stored = null;
        try {
            $raw = $this->settings->get('navigation.' . $location);
            if (is_string($raw) && $raw !== '') {
                $decoded = json_decode($raw, true);
                if (is_array($decoded) && $decoded !== []) {
                    $stored = $decoded;
                }
            }
        } catch (\Throwable) {
            $stored = null;
        }

        $items = $this->normalise($stored ?? $this->defaults($location), 1);

        return $this->cache[$location] = $items;
    }

    /** Raw (unresolved) tree for the admin editor. */
    public function tree(string $location): array
    {
        try {
            $raw = $this->settings->get('navigation.' . $location);
            if (is_string($raw) && $raw !== '') {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }
        } catch (\Throwable) {
        }
        return $this->defaults($location);
    }

    /** @param array<int,array<string,mixed>> $tree */
    public function save(string $location, array $tree, ?int $userId): void
    {
        if (!isset(self::LOCATIONS[$location])) {
            throw new \InvalidArgumentException('Unknown menu location.');
        }
        $clean = $this->sanitise($tree, 1);
        $this->settings->set("navigation." . $location, json_encode($clean, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: "[]", false, $userId);
        unset($this->cache[$location]);
    }

    public function reset(string $location, ?int $userId): void
    {
        $this->settings->set("navigation." . $location, "", false, $userId);
        unset($this->cache[$location]);
    }

    public function isActive(array $item, string $currentPath): bool
    {
        $url = (string) ($item['url'] ?? '');
        if ($url === '' || $url === '#' || preg_match('~^https?://~i', $url)) {
            return false;
        }
        $path = '/' . trim($url, '/');
        return $path === '/' ? $currentPath === '/' : ($currentPath === $path || str_starts_with($currentPath, $path . '/'));
    }

    /** @param array<int,mixed> $items @return array<int,array<string,mixed>> */
    private function normalise(array $items, int $depth): array
    {
        $out = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $label = trim((string) ($item['label'] ?? ''));
            if ($label === '') {
                continue;
            }
            $url = trim((string) ($item['url'] ?? '#'));
            $href = $url === '' || $url === '#'
                ? '#'
                : (preg_match('~^(https?:)?//|^mailto:|^tel:~i', $url) ? $url : base_url(trim($url, '/')));
            $node = [
                'label'    => $label,
                'url'      => $url,
                'href'     => $href,
                'newtab'   => !empty($item['newtab']),
                'children' => [],
            ];
            if ($depth < self::MAX_DEPTH && !empty($item['children']) && is_array($item['children'])) {
                $node['children'] = $this->normalise($item['children'], $depth + 1);
            }
            $out[] = $node;
        }
        return $out;
    }

    /** @param array<int,mixed> $items @return array<int,array<string,mixed>> */
    private function sanitise(array $items, int $depth): array
    {
        $out = [];
        foreach ($items as $item) {
            if (!is_array($item) || count($out) >= self::MAX_ITEMS) {
                continue;
            }
            $label = mb_substr(trim(strip_tags((string) ($item['label'] ?? ''))), 0, 60);
            $url = trim((string) ($item['url'] ?? ''));
            if ($label === '') {
                continue;
            }
            if (!preg_match('~^(#|https?://[^\s"\'<>]+|[a-z0-9][a-z0-9/_.-]*|mailto:[^\s]+|tel:[^\s]+)?$~i', $url)) {
                throw new \InvalidArgumentException("The link for \"{$label}\" is not a valid page path or https:// address.");
            }
            $node = ['label' => $label, 'url' => $url === '' ? '#' : $url, 'newtab' => !empty($item['newtab'])];
            if ($depth < self::MAX_DEPTH && !empty($item['children']) && is_array($item['children'])) {
                $node['children'] = $this->sanitise($item['children'], $depth + 1);
            }
            $out[] = $node;
        }
        return $out;
    }
}
