<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Database;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Http\Controllers\Controller;
use App\Repositories\DonationRepository;
use App\Repositories\PaymentRepository;
use App\Services\AuditService;
use App\Services\AuthService;
use App\Services\SettingsService;

/**
 * Admin overview for the unified almarah.org panel. Each block is only
 * computed (and shown) for roles holding the relevant permission; every
 * query is guarded so a problem in one block never blanks the page.
 */
final class DashboardController extends Controller
{
    public function __construct(
        View $view,
        Session $session,
        Csrf $csrf,
        AuthService $auth,
        private Database $db,
        private DonationRepository $donations,
        private PaymentRepository $payments,
        private AuditService $audit,
        private SettingsService $settings
    ) {
        parent::__construct($view, $session, $csrf, $auth);
    }

    public function index(): Response
    {
        $data = ['pageTitle' => 'Admin overview'];

        if ($this->auth->can('donations.view')) {
            $data['finance'] = $this->guard(function (): array {
                $daily = chart_fill_days($this->db->select(
                    "SELECT DATE(COALESCE(completed_at, created_at)) AS day, COALESCE(SUM(amount_minor), 0) AS minor, COUNT(*) AS cnt
                       FROM donations
                      WHERE status = 'completed' AND COALESCE(completed_at, created_at) >= DATE_SUB(UTC_DATE(), INTERVAL 59 DAY)
                      GROUP BY day"
                ), 60);
                $trend = array_slice($daily, 30);
                $prev = array_slice($daily, 0, 30);
                return [
                    'totals'        => $this->donations->totalsByStatus(),
                    'trend'         => $trend,
                    'trendPrevMinor' => array_sum(array_column($prev, 'minor')),
                    'trendPrevCount' => array_sum(array_column($prev, 'count')),
                    'month'         => $this->db->selectOne(
                        "SELECT COALESCE(SUM(amount_minor),0) AS minor, COUNT(*) AS cnt FROM donations
                          WHERE status = 'completed' AND DATE_FORMAT(COALESCE(completed_at, created_at), '%Y-%m') = DATE_FORMAT(UTC_DATE(), '%Y-%m')"
                    ) ?? ['minor' => 0, 'cnt' => 0],
                    'pending'       => $this->db->int("SELECT COUNT(*) FROM donations WHERE status IN ('pending','processing')"),
                    'failed'        => $this->db->int("SELECT COUNT(*) FROM donations WHERE status IN ('failed','cancelled') AND created_at >= DATE_SUB(UTC_DATE(), INTERVAL 30 DAY)"),
                    'byPurpose'     => $this->db->select(
                        "SELECT COALESCE(p.name, 'General Fund') AS name, COALESCE(SUM(d.amount_minor),0) AS minor, COUNT(d.id) AS cnt
                           FROM donations d LEFT JOIN donation_purposes p ON p.id = d.purpose_id
                          WHERE d.status = 'completed' AND COALESCE(d.completed_at, d.created_at) >= DATE_SUB(UTC_DATE(), INTERVAL 90 DAY)
                          GROUP BY COALESCE(p.name, 'General Fund') ORDER BY minor DESC LIMIT 6"
                    ),
                    'recent'        => $this->donations->recent(8),
                    'gatewayHealth' => $this->settings->gatewayStatus(),
                ];
            });
        }

        if ($this->auth->can('forms.view') || $this->auth->can('volunteers.manage')) {
            $data['inbox'] = $this->guard(function (): array {
                $boxes = [];
                foreach (InboxController::boxes() as $key => $box) {
                    if (!$this->auth->can($box['permission'])) {
                        continue;
                    }
                    $in = "'" . implode("','", $box['open']) . "'";
                    $boxes[$key] = [
                        'label' => $box['label'],
                        'open'  => $this->db->int("SELECT COUNT(*) FROM `{$box['table']}` WHERE status IN ({$in})"),
                        'week'  => $this->db->int("SELECT COUNT(*) FROM `{$box['table']}` WHERE created_at >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 7 DAY)"),
                        'total' => $this->db->int("SELECT COUNT(*) FROM `{$box['table']}`"),
                    ];
                }
                $latest = [];
                if ($this->auth->can('forms.view')) {
                    $latest = $this->db->select("SELECT id, name, subject, status, created_at FROM contact_messages ORDER BY id DESC LIMIT 6");
                }
                return ['boxes' => $boxes, 'latestContact' => $latest];
            });
        }

        if ($this->auth->can('content.manage')) {
            $data['content'] = $this->guard(fn (): array => [
                'pages'     => $this->db->int('SELECT COUNT(*) FROM cms_pages'),
                'drafts'    => $this->db->int("SELECT COUNT(*) FROM cms_pages WHERE status = 'draft'"),
                'slides'    => $this->db->int('SELECT COUNT(*) FROM cms_slides WHERE is_active = 1'),
                'media'     => $this->db->int("SELECT COUNT(*) FROM media_items WHERE status = 'published'"),
                'notFound'  => $this->db->select('SELECT path, hits FROM seo_not_found_log ORDER BY hits DESC LIMIT 5'),
                'redirects' => $this->db->int('SELECT COUNT(*) FROM seo_redirects WHERE is_active = 1'),
                'banner'    => $this->db->selectOne("SELECT title FROM cms_banners WHERE is_active = 1 AND (starts_at IS NULL OR starts_at <= NOW()) AND (ends_at IS NULL OR ends_at >= NOW()) ORDER BY id DESC LIMIT 1"),
            ]);
        }

        if ($this->auth->can('logs.view')) {
            $data['system'] = $this->guard(fn (): array => [
                'mailConfigured' => $this->settings->mailConfigured(),
                'mailQueued'     => $this->db->int("SELECT COUNT(*) FROM email_jobs WHERE status IN ('queued','sending')"),
                'mailFailed'     => $this->db->int("SELECT COUNT(*) FROM email_jobs WHERE status = 'failed'"),
                'callbacks'      => $this->payments->recentCallbacks(5),
                'recentAudit'    => $this->audit->recent(8),
                'lastBackup'     => $this->db->selectOne('SELECT * FROM backup_logs ORDER BY id DESC LIMIT 1'),
            ]);
        }

        return $this->render('admin/dashboard', $data, 'layouts/admin');
    }

    /** @return array<string,mixed>|null */
    private function guard(callable $fn): ?array
    {
        try {
            return $fn();
        } catch (\Throwable $e) {
            error_log('Dashboard block failed: ' . $e->getMessage());
            return null;
        }
    }
}
