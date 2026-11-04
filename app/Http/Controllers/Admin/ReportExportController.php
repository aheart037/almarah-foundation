<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Http\Controllers\Controller;
use App\Services\AuditService;
use App\Services\AuthService;
use App\Services\ReportExportService;
use App\Services\ReportService;

/**
 * CSV and Excel export of the administrative reports (v3.1).
 *
 * The report pages already link to /admin/reports/{slug}/csv and /xlsx, and the
 * routes existed, but no controller action answered them (HTTP 500). Kept in its
 * own controller so the existing ReportController is not touched.
 * Access is guarded by the same `reports.view` route middleware as the PDF.
 */
final class ReportExportController extends Controller
{
    public function __construct(
        View $view,
        Session $session,
        Csrf $csrf,
        AuthService $auth,
        private ReportService $reports,
        private ReportExportService $export,
        private AuditService $audit
    ) {
        parent::__construct($view, $session, $csrf, $auth);
    }

    public function csv(Request $request): Response
    {
        return $this->spreadsheet($request, 'csv');
    }

    public function xlsx(Request $request): Response
    {
        return $this->spreadsheet($request, 'xlsx');
    }

    private function spreadsheet(Request $request, string $format): Response
    {
        $slug = (string) $request->routeParam('slug', '');
        if (!ReportService::exists($slug)) {
            abort(404, 'Report not found.');
        }

        $report = $this->reports->run($slug, $request->all(), 1, true);
        $user = $this->auth->user() ?? [];
        $sheet = $this->export->sheet(
            $report,
            $this->reports->describeFilters($report),
            (string) ($report['filters']['currency'] ?? $this->reports->defaultCurrency()),
            (string) \App\Core\Config::get('app.name', 'Almarah Foundation')
        );

        // Same audit trail as the PDF: exports contain donor and financial data.
        $this->audit->log('report.exported', 'report', $slug, [
            'format'  => $format,
            'rows'    => min((int) $report['total'], ReportService::EXPORT_LIMIT),
            'range'   => ReportService::rangeText($report['filters']),
            'filters' => array_filter(array_diff_key($report['filters'], array_flip(['sort', 'dir', 'dir_given', 'date_from', 'date_to'])), static fn ($v): bool => $v !== '' && $v !== null),
        ], isset($user['id']) ? (int) $user['id'] : null);

        $name = 'almarah-report-' . preg_replace('/[^a-z0-9-]/', '-', strtolower($slug)) . '-' . gmdate('Ymd-His') . '.' . $format;
        $binary = $format === 'xlsx' ? $this->export->xlsx($sheet, (string) $report['title']) : $this->export->csv($sheet);
        $mime = $format === 'xlsx' ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' : 'text/csv; charset=utf-8';

        return new Response($binary, 200, [
            'Content-Type'        => $mime,
            'Content-Disposition' => 'attachment; filename="' . $name . '"',
            'Content-Length'      => (string) strlen($binary),
            'Cache-Control'       => 'private, no-store, max-age=0',
            'X-Robots-Tag'        => 'noindex, nofollow',
        ]);
    }
}
