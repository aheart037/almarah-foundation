<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Core\Csrf;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Http\Controllers\Controller;
use App\Services\AuthService;
use App\Services\CmsService;
use App\Services\SeoService;

/**
 * Search-engine files generated from the page map + seo_meta table:
 * /robots.txt and /sitemap.xml. Never cached longer than an hour.
 */
final class SeoController extends Controller
{
    public function __construct(
        View $view,
        Session $session,
        Csrf $csrf,
        AuthService $auth,
        private SeoService $seo,
        private CmsService $cms
    ) {
        parent::__construct($view, $session, $csrf, $auth);
    }

    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin/',
            'Disallow: /login',
            'Disallow: /donation/',
            'Disallow: /payments/',
            'Disallow: /forms/',
            'Disallow: /setup',
            '',
            'Sitemap: ' . base_url('sitemap.xml'),
            '',
        ];
        return (new Response(implode("\n", $lines), 200))
            ->withHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->withHeader('Cache-Control', 'public, max-age=3600');
    }

    public function sitemap(): Response
    {
        $urls = [];
        foreach (PageController::sitemapPages() as $path => [$priority, $freq]) {
            $urls[$path] = ['loc' => $this->loc($path), 'priority' => $priority, 'changefreq' => $freq, 'lastmod' => null];
        }
        // Published pages authored in Admin → Pages.
        foreach ($this->cms->publishedPages() as $row) {
            $path = '/' . trim((string) $row['slug'], '/');
            $urls[$path] = ['loc' => $this->loc($path), 'priority' => '0.5', 'changefreq' => 'monthly', 'lastmod' => !empty($row['updated_at']) ? date('Y-m-d', strtotime((string) $row['updated_at'])) : null];
        }
        foreach ($this->seo->sitemapEntries() as $row) {
            $path = '/' . trim((string) $row['route_path'], '/');
            $urls[$path] = [
                'loc'        => $this->loc($path),
                'priority'   => (string) ($row['priority'] ?? '0.5'),
                'changefreq' => (string) ($row['changefreq'] ?? 'monthly'),
                'lastmod'    => !empty($row['updated_at']) ? date('Y-m-d', strtotime((string) $row['updated_at'])) : null,
            ];
        }

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
        foreach ($urls as $u) {
            $xml .= "  <url>\n    <loc>" . htmlspecialchars($u['loc'], ENT_XML1) . "</loc>\n";
            if ($u['lastmod']) {
                $xml .= "    <lastmod>{$u['lastmod']}</lastmod>\n";
            }
            $xml .= "    <changefreq>{$u['changefreq']}</changefreq>\n    <priority>{$u['priority']}</priority>\n  </url>\n";
        }
        $xml .= "</urlset>\n";

        return (new Response($xml, 200))
            ->withHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->withHeader('Cache-Control', 'public, max-age=3600');
    }

    private function loc(string $path): string
    {
        $path = trim($path, '/');
        return $path === '' ? base_url() . '/' : base_url($path) . '/';
    }
}
