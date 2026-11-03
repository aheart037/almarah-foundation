<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

/**
 * Per-page SEO metadata (Admin → SEO) and the redirect table that preserves
 * the WordPress URLs. Everything read here is cached per request.
 */
final class SeoService
{
    /** @var array<string,array<string,mixed>|null> */
    private array $metaCache = [];

    public function __construct(private Database $db)
    {
    }

    /** Normalise a request path to the form stored in the tables: '/about'. */
    public static function normalise(string $path): string
    {
        $path = '/' . trim((string) parse_url($path, PHP_URL_PATH), '/');
        return $path === '' ? '/' : strtolower($path);
    }

    /** @return array<string,mixed>|null */
    public function metaFor(string $path): ?array
    {
        $key = self::normalise($path);
        if (array_key_exists($key, $this->metaCache)) {
            return $this->metaCache[$key];
        }
        try {
            $row = $this->db->selectOne('SELECT * FROM seo_meta WHERE route_path = :p LIMIT 1', ['p' => $key]);
        } catch (\Throwable) {
            $row = null;
        }
        return $this->metaCache[$key] = $row ?: null;
    }

    /** @return array<string,mixed>|null ['to' => url, 'code' => int] */
    public function redirectFor(string $path): ?array
    {
        $key = self::normalise($path);
        try {
            $row = $this->db->selectOne(
                'SELECT id, to_url, status_code FROM seo_redirects WHERE is_active = 1 AND is_regex = 0 AND from_path = :p LIMIT 1',
                ['p' => $key]
            );
            if ($row === null) {
                // Regex rules are few; evaluate in PHP.
                $rules = $this->db->select('SELECT id, from_path, to_url, status_code FROM seo_redirects WHERE is_active = 1 AND is_regex = 1');
                foreach ($rules as $rule) {
                    $pattern = '~' . str_replace('~', '\~', (string) $rule['from_path']) . '~i';
                    if (@preg_match($pattern, $key) === 1) {
                        $row = $rule;
                        $row['to_url'] = (string) preg_replace($pattern, (string) $rule['to_url'], $key);
                        break;
                    }
                }
            }
            if ($row === null) {
                return null;
            }
            $this->db->run('UPDATE seo_redirects SET hits = hits + 1, last_hit_at = NOW() WHERE id = :id', ['id' => (int) $row['id']]);
            $to = (string) ($row['to_url'] ?? '');
            $code = (int) ($row['status_code'] ?? 301);
            if ($code === 410 || $to === '') {
                return ['to' => '', 'code' => 410];
            }
            if (!preg_match('~^https?://~i', $to)) {
                $to = base_url(ltrim($to, '/'));
            }
            return ['to' => $to, 'code' => in_array($code, [301, 302, 307, 308], true) ? $code : 301];
        } catch (\Throwable) {
            return null;
        }
    }

    public function logNotFound(string $path, ?string $referer): void
    {
        $key = mb_substr(self::normalise($path), 0, 190);
        if (preg_match('~\.(php|asp|aspx|env|git|sql|bak|xml|json|txt|map|ico|png|jpe?g|gif|svg|css|js|woff2?)$~i', $key)) {
            return; // scanner noise and missing static files are not content 404s
        }
        try {
            $this->db->run(
                'INSERT INTO seo_not_found_log (path, referer, hits, last_hit_at) VALUES (:p, :r, 1, NOW())
                 ON DUPLICATE KEY UPDATE hits = hits + 1, last_hit_at = NOW(), referer = COALESCE(VALUES(referer), referer)',
                ['p' => $key, 'r' => $referer !== null ? mb_substr($referer, 0, 250) : null]
            );
        } catch (\Throwable) {
        }
    }

    /** @return array<int,array<string,mixed>> rows for the sitemap */
    public function sitemapEntries(): array
    {
        try {
            return $this->db->select('SELECT route_path, priority, changefreq, updated_at FROM seo_meta WHERE in_sitemap = 1 ORDER BY priority DESC, route_path ASC');
        } catch (\Throwable) {
            return [];
        }
    }
}
