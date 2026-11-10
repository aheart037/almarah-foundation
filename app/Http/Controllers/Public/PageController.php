<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Exceptions\HttpException;
use App\Http\Controllers\Controller;
use App\Services\AuthService;
use App\Services\CmsService;

/**
 * Every content page of the website. The page map below is the single
 * source of truth for URL → view → default title; SEO fields can be
 * overridden per URL from Admin → SEO (seo_meta), and the body of a page
 * can be replaced from Admin → Pages (cms_pages, template "html").
 *
 * URLs are kept identical to the WordPress site so no link or ranking is
 * lost (see analysis/url_inventory.csv).
 */
final class PageController extends Controller
{
    /**
     * slug => [view, title, description, body-class, sitemap priority, changefreq]
     * @var array<string,array{0:string,1:string,2:string,3:string,4:string,5:string}>
     */
    public const PAGES = [
        'about'                 => ['about', 'About Us', 'Almarah Foundation is a Lahore-based non-profit caring for orphaned, abandoned and vulnerable children and families across Pakistan since 2021.', 'page-about', '0.9', 'monthly'],
        'chairpersons-message'  => ['chairpersons-message', "Chairperson's Message", 'A message from the Chairperson of Almarah Foundation.', 'page-message', '0.6', 'yearly'],
        'ceos-message'          => ['ceos-message', "CEO's Message", 'A message from the Chief Executive Officer of Almarah Foundation.', 'page-message', '0.6', 'yearly'],
        'members'               => ['members', 'Board Members', 'Meet the board and leadership team of Almarah Foundation.', 'page-members', '0.6', 'monthly'],
        'financial-reports'     => ['financial-reports', 'Financial Reports', 'Audited financial statements and annual reports of Almarah Foundation.', 'page-reports', '0.7', 'monthly'],
        'registrations-mou'     => ['registrations-mou', 'Certificates & Registrations', 'Government registrations, tax-exemption and compliance certificates of Almarah Foundation.', 'page-registrations', '0.6', 'yearly'],
        'mous'                  => ['mous', 'MoU', 'Memoranda of Understanding signed by Almarah Foundation with partner institutions.', 'page-mous', '0.6', 'monthly'],
        'projects'              => ['projects', 'Our Projects', 'Parent the Orphan, Feed the Hungry, Izzat Ki Roti, Educate Pakistan and Compassionate Haven — the programmes of Almarah Foundation.', 'page-projects', '0.9', 'monthly'],
        'parent-the-orphan'     => ['parent-the-orphan', 'Parent the Orphan', 'Sponsor the home, schooling, health and wellbeing of an orphaned child at Almarah Foundation.', 'page-program', '0.8', 'monthly'],
        'feed-the-hungry-2'     => ['feed-the-hungry', 'Feed the Hungry', 'Cooked meals and monthly ration for families facing hunger across Lahore and beyond.', 'page-program', '0.8', 'monthly'],
        'izat-ki-roti'          => ['izat-ki-roti', 'Izzat Ki Roti', 'Dignified daily bread for deserving families through Almarah Foundation tandoors.', 'page-program', '0.8', 'monthly'],
        'educate-pakistan'      => ['educate-pakistan', 'Educate Pakistan', 'School fees, uniforms, books and mentoring for underprivileged students.', 'page-program', '0.8', 'monthly'],
        'e-book'                => ['e-book', 'E-Book', 'Read and download the Almarah Foundation book and profile.', 'page-ebook', '0.5', 'yearly'],
        'photos'                => ['photos', 'Photos', 'Photo gallery from Almarah Foundation programmes and events.', 'page-photos', '0.6', 'weekly'],
        'videos'                => ['videos', 'Videos', 'Videos from Almarah Foundation programmes, events and stories.', 'page-videos', '0.6', 'weekly'],
        'become-a-volunteer'    => ['become-a-volunteer', 'Become a Volunteer', 'Register as a volunteer with Almarah Foundation and give your time to children and families in need.', 'page-volunteer', '0.7', 'monthly'],
        'careers'               => ['careers', 'Careers', 'Join the Almarah Foundation team — current openings and how to apply.', 'page-careers', '0.6', 'weekly'],
        'newsletter'            => ['newsletter', 'Newsletter Signup', 'Subscribe to Almarah Foundation updates, stories and impact reports.', 'page-newsletter', '0.5', 'yearly'],
        'banks'                 => ['banks', 'Bank Details', 'Bank account details for donating to Almarah Foundation in Pakistan, the UK and the US.', 'page-banks', '0.7', 'monthly'],
        'contact'               => ['contact', 'Contact Us', 'Get in touch with Almarah Foundation — 3 Canal Park, Gulberg-II, Lahore. Phone +92 326 8050116.', 'page-contact', '0.8', 'yearly'],
    ];

    public function __construct(
        View $view,
        Session $session,
        Csrf $csrf,
        AuthService $auth,
        private CmsService $cms
    ) {
        parent::__construct($view, $session, $csrf, $auth);
    }

    /** @return array<string,array{0:string,1:string}> path => [priority, changefreq] */
    public static function sitemapPages(): array
    {
        $out = ['/' => ['1.0', 'weekly'], '/donate' => ['0.9', 'monthly']];
        foreach (self::PAGES as $slug => $def) {
            $out['/' . $slug] = [$def[4], $def[5]];
        }
        return $out;
    }

    public function show(Request $request): Response
    {
        $slug = trim((string) $request->routeParam('slug', ''), '/');
        $cmsPage = preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) ? $this->cms->page($slug) : null;

        if (!isset(self::PAGES[$slug])) {
            // Pages created from Admin → Pages.
            if ($cmsPage === null) {
                throw new HttpException(404);
            }
            return $this->renderCms($cmsPage, (string) $cmsPage['title'], null, 'page-cms page-' . $slug);
        }
        [$viewName, $title, $description, $bodyClass] = self::PAGES[$slug];

        // Admin override: a published CMS page with the same slug and a body
        // replaces the shipped design entirely (used for legal pages & edits).
        if ($cmsPage !== null && trim((string) $cmsPage['body_html']) !== '') {
            return $this->renderCms($cmsPage, (string) ($cmsPage['title'] ?: $title), $description, $bodyClass);
        }

        if (!$this->view->exists('public/pages/' . $viewName)) {
            throw new HttpException(404);
        }

        return $this->render('public/pages/' . $viewName, [
            'pageTitle'       => $title,
            'metaDescription' => $description,
            'bodyClass'       => $bodyClass,
            'slug'            => $slug,
        ] + $this->dataFor($slug));
    }

    /** @param array<string,mixed> $page */
    private function renderCms(array $page, string $title, ?string $description, string $bodyClass): Response
    {
        $description ??= mb_substr(trim(preg_replace('/\s+/', ' ', strip_tags((string) ($page['hero_subtitle'] ?: $page['body_html'] ?: ''))) ?? ''), 0, 160);
        return $this->render('public/pages/_cms', [
            'pageTitle'       => $title,
            'metaDescription' => $description,
            'bodyClass'       => trim($bodyClass . ' tpl-' . (string) ($page['template'] ?? 'page')),
            'page'            => $page,
        ]);
    }

    /**
     * Board Members page: every published profile grouped by category, plus the
     * admin-editable headings. Admin → Board & Team controls all of it.
     *
     * @return array<string,mixed>
     */
    private function membersData(): array
    {
        $groups = [];
        foreach ($this->cms->teamProfiles() as $profile) {
            $groups[(string) $profile['group_key']][] = $profile;
        }
        return [
            'teamGroups'   => $groups,
            'teamSections' => $this->cms->teamSections(),
        ];
    }

    /** Dynamic data some pages need (all from MySQL). @return array<string,mixed> */
    private function dataFor(string $slug): array
    {
        return match ($slug) {
            'banks'             => ['banks' => $this->cms->bankAccounts()],
            'financial-reports' => ['reports' => $this->cms->financialReports()],
            'members'           => $this->membersData(),
            'mous'              => ['mous' => $this->cms->partners('mou')],
            'registrations-mou' => ['certificates' => $this->cms->partnersOfKinds(['certificate', 'registration'])],
            'careers'           => ['jobs' => $this->cms->openJobs()],
            'photos'            => ['photos' => $this->cms->media('photo', isset($_GET['album']) ? (string) $_GET['album'] : null), 'albums' => $this->cms->mediaAlbums('photo'), 'album' => isset($_GET['album']) ? (string) $_GET['album'] : ''],
            'videos'            => ['videos' => $this->cms->media('video')],
            'e-book'            => ['ebooks' => $this->cms->media('ebook')],
            default             => [],
        };
    }
}
