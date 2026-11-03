<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

/**
 * Read-side helpers for the website CMS tables (migration 0009):
 * slides, banners, pages, team profiles, partners, reports, bank accounts,
 * media items and donation purposes. Writes live in the admin controllers.
 *
 * Every query here is safe to call on a request path — small tables,
 * indexed columns, and the results are cached per request.
 */
final class CmsService
{
    /** @var array<string,mixed> */
    private array $cache = [];

    public function __construct(private Database $db)
    {
    }

    // ------------------------------------------------------------ slider

    /** @return list<array<string,mixed>> */
    public function activeSlides(): array
    {
        return $this->remember('slides', fn () => $this->db->select(
            'SELECT * FROM cms_slides WHERE is_active = 1 ORDER BY sort_order ASC, id ASC'
        ));
    }

    // ------------------------------------------------------------ banners

    /** @return array<string,mixed>|null */
    public function activeBanner(string $placement): ?array
    {
        return $this->remember('banner:' . $placement, fn () => $this->db->selectOne(
            "SELECT * FROM cms_banners
              WHERE placement = :p AND is_active = 1
                AND (starts_at IS NULL OR starts_at <= NOW())
                AND (ends_at IS NULL OR ends_at >= NOW())
              ORDER BY id DESC LIMIT 1",
            ['p' => $placement]
        ));
    }

    // -------------------------------------------------------------- pages

    /** @return list<array<string,mixed>> slug + updated_at of every published page */
    public function publishedPages(): array
    {
        return $this->remember('pages:published', fn () => $this->db->select(
            "SELECT slug, title, updated_at FROM cms_pages WHERE status = 'published' ORDER BY slug ASC"
        ));
    }

    /** @return array<string,mixed>|null */
    public function page(string $slug, bool $publishedOnly = true): ?array
    {
        $sql = 'SELECT * FROM cms_pages WHERE slug = :slug';
        if ($publishedOnly) {
            $sql .= " AND status = 'published'";
        }
        return $this->db->selectOne($sql, ['slug' => $slug]);
    }

    // ------------------------------------------------------------ people

    /** @return list<array<string,mixed>> */
    public function teamProfiles(?string $group = null): array
    {
        $sql = "SELECT * FROM team_profiles WHERE status = 'published'";
        $params = [];
        if ($group !== null) {
            $sql .= ' AND group_key = :g';
            $params['g'] = $group;
        }
        return $this->db->select($sql . ' ORDER BY sort_order ASC, id ASC', $params);
    }

    /** @return list<array<string,mixed>> */
    public function partners(?string $type = null): array
    {
        $sql = "SELECT * FROM partners WHERE status = 'published'";
        $params = [];
        if ($type !== null) {
            $sql .= ' AND kind = :t';
            $params['t'] = $type;
        }
        return $this->db->select($sql . ' ORDER BY sort_order ASC, id ASC', $params);
    }

    // --------------------------------------------------- documents & banks

    /** @return list<array<string,mixed>> */
    public function financialReports(): array
    {
        return $this->db->select(
            "SELECT * FROM financial_reports WHERE status = 'published' ORDER BY fiscal_year DESC, sort_order ASC, id DESC"
        );
    }

    /** @return list<array<string,mixed>> */
    public function bankAccounts(): array
    {
        return $this->remember('banks', fn () => $this->db->select(
            "SELECT * FROM bank_accounts WHERE status = 'published' ORDER BY sort_order ASC, id ASC"
        ));
    }

    // -------------------------------------------------------------- media

    /** @return list<array<string,mixed>> */
    public function media(string $type, ?string $album = null, int $limit = 200): array
    {
        $sql = "SELECT * FROM media_items WHERE status = 'published' AND kind = :type";
        $params = ['type' => $type];
        if ($album !== null && $album !== '') {
            $sql .= ' AND album = :album';
            $params['album'] = $album;
        }
        $sql .= ' ORDER BY is_featured DESC, sort_order ASC, id DESC LIMIT ' . max(1, min(500, $limit));
        return $this->db->select($sql, $params);
    }

    /** @return list<string> */
    public function mediaAlbums(string $type): array
    {
        $rows = $this->db->select(
            "SELECT album, COUNT(*) AS n FROM media_items
              WHERE status = 'published' AND kind = :type AND album IS NOT NULL AND album <> ''
              GROUP BY album ORDER BY MIN(sort_order) ASC, album ASC",
            ['type' => $type]
        );
        return array_map(static fn (array $r): string => (string) $r['album'], $rows);
    }

    // ----------------------------------------------------- donation purposes

    /** @return list<array<string,mixed>> */
    public function donationPurposes(): array
    {
        return $this->remember('purposes', fn () => $this->db->select(
            "SELECT * FROM donation_purposes WHERE status = 'active' ORDER BY sort_order ASC, id ASC"
        ));
    }

    /** @return array<string,mixed>|null */
    public function donationPurpose(string $slug): ?array
    {
        foreach ($this->donationPurposes() as $p) {
            if ($p['slug'] === $slug) {
                return $p;
            }
        }
        return null;
    }

    // ------------------------------------------------------------- careers

    /** @return list<array<string,mixed>> */
    public function openJobs(): array
    {
        return $this->db->select(
            "SELECT * FROM career_jobs
              WHERE status = 'open' AND (closes_at IS NULL OR closes_at >= CURDATE())
              ORDER BY sort_order ASC, created_at DESC"
        );
    }

    /** @return array<string,mixed>|null */
    public function job(string $slug): ?array
    {
        return $this->db->selectOne(
            "SELECT * FROM career_jobs WHERE slug = :slug AND status = 'open'",
            ['slug' => $slug]
        );
    }

    // --------------------------------------------------------------- misc

    /**
     * @template T
     * @param callable():T $fn
     * @return T
     */
    private function remember(string $key, callable $fn): mixed
    {
        if (!array_key_exists($key, $this->cache)) {
            $this->cache[$key] = $fn();
        }
        return $this->cache[$key];
    }
}
