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
use App\Services\BankLedgerService;

/** Read-only per-bank ledgers: summary of all banks, one bank's transactions, and a CSV download. */
final class BankLedgerController extends Controller
{
    public function __construct(View $view, Session $session, Csrf $csrf, AuthService $auth, private BankLedgerService $banks, private AuditService $audit)
    {
        parent::__construct($view, $session, $csrf, $auth);
    }

    private function filters(Request $request): array
    {
        $f = [];
        foreach (['q', 'status', 'mode', 'currency', 'date_from', 'date_to'] as $k) {
            $f[$k] = trim((string) $request->input($k, ''));
        }
        return $f;
    }

    public function index(Request $request): Response
    {
        $f = $this->filters($request);
        return $this->render('admin/ledger/banks', ['pageTitle' => 'Bank ledgers', 'filters' => $f, 'summary' => $this->banks->summary($f)], 'layouts/admin');
    }

    public function show(Request $request): Response
    {
        $id = (int) $request->routeParam('id', 0);
        $bank = $this->banks->bank($id);
        if (!$bank) {
            abort(404, 'Bank account not found.');
        }
        $f = $this->filters($request);
        $page = max(1, (int) $request->input('page', 1));
        $result = $this->banks->transactions($id, $f, $page, 50);
        return $this->render('admin/ledger/bank', [
            'pageTitle' => $bank['bank_name'] . ' ledger', 'bank' => $bank, 'filters' => $f, 'page' => $page,
            'lastPage' => max(1, (int) ceil($result['total'] / 50)), 'result' => $result,
        ], 'layouts/admin');
    }

    public function export(Request $request): Response
    {
        $id = (int) $request->routeParam('id', 0);
        $bank = $this->banks->bank($id);
        if (!$bank) {
            abort(404, 'Bank account not found.');
        }
        $result = $this->banks->transactions($id, $this->filters($request), 1, 0); // 0 = no paging
        $cell = static function (mixed $v): string {
            $s = str_replace(["\r", "\n"], ' ', (string) $v);
            if ($s !== '' && (str_contains("=+-@\t", $s[0]))) {
                $s = "'" . $s; // neutralise spreadsheet formulas typed by donors
            }
            return '"' . str_replace('"', '""', $s) . '"';
        };
        $out = "\xEF\xBB\xBF" . "Date and time,Bank,Website reference,Bank transaction ID / slip no.,Gateway transaction ID,Mode,Donor name,Donor email,Donor phone,Amount,Currency,Status,Verified at,Verified by\r\n";
        foreach ($result['rows'] as $r) {
            $out .= implode(',', array_map($cell, [
                $r['txn_at'], $bank['bank_name'], $r['public_reference'], $r['bank_reference'], $r['provider_txn'],
                BankLedgerService::modeLabel((string) $r['source'], $r['gateway_code']), $r['anonymous'] ? 'Anonymous' : $r['donor_name'],
                $r['donor_email'], $r['donor_phone'], number_format(((int) $r['amount_minor']) / 100, 2, '.', ''), $r['currency'],
                $r['ledger_status'], $r['verified_at'], $r['verified_by_email'],
            ])) . "\r\n";
        }
        $this->audit->log('ledger.bank.exported', 'bank_account', $id, ['rows' => count($result['rows'])], (int) $this->auth->id());
        return (new Response($out, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="bank-ledger-' . $id . '-' . gmdate('Ymd-His') . '.csv"',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]));
    }
}
