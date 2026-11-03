<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Http\Controllers\Controller;
use App\Repositories\DonationRepository;
use App\Repositories\PaymentRepository;
use App\Services\AuthService;
use App\Services\DonationService;
use App\Services\FinanceService;
use InvalidArgumentException;
use Throwable;

/**
 * Finance manager workspace: reconciliation, adjustments, offline donations
 * and monthly exports. Every route requires the finance.manage permission.
 */
final class FinanceController extends Controller
{
    public function __construct(
        View $view,
        Session $session,
        Csrf $csrf,
        AuthService $auth,
        private Database $db,
        private FinanceService $finance,
        private DonationRepository $donations,
        private PaymentRepository $payments,
        private DonationService $donationService
    ) {
        parent::__construct($view, $session, $csrf, $auth);
    }

    public function index(Request $request): Response
    {
        $month = (string) $request->input('month', gmdate('Y-m'));
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            $month = gmdate('Y-m');
        }

        return $this->render('admin/finance/index', [
            'pageTitle'    => 'Finance',
            'month'        => $month,
            'summary'      => $this->finance->monthlySummary($month),
            'trend'        => $this->finance->monthlyTrend(12),
            'runs'         => $this->finance->runs(10),
            'adjustments'  => $this->finance->recentAdjustments(10),
            'stalePending' => $this->finance->stalePendingCount(),
            'openIssues'   => $this->finance->openIssueCount(),
            'gateways'     => $this->db->select('SELECT id, name FROM gateways ORDER BY id'),
            'pendingTxns'  => count($this->payments->pendingForReconciliation(200, 10)),
        ], 'layouts/admin');
    }

    public function reconcile(Request $request): Response
    {
        $type = (string) $request->input('period_type', 'monthly');
        $today = gmdate('Y-m-d');

        switch ($type) {
            case 'daily':
                $start = $end = (string) $request->input('day', $today);
                break;
            case 'monthly':
                $month = (string) $request->input('month', gmdate('Y-m'));
                [$s, $e] = $this->finance->monthBounds($month);
                $start = substr($s, 0, 10);
                $end = substr($e, 0, 10);
                break;
            default:
                $type = 'custom';
                $start = (string) $request->input('start', $today);
                $end = (string) $request->input('end', $today);
        }

        foreach ([$start, $end] as $d) {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) || strtotime($d) === false) {
                $this->flashError('Please enter valid dates.');
                return $this->redirect('/admin/finance');
            }
        }

        $gatewayId = (int) $request->input('gateway_id', 0) ?: null;

        try {
            $runId = $this->finance->runReconciliation($type, $start, $end, $gatewayId, $this->auth->id());
        } catch (InvalidArgumentException $e) {
            $this->flashError($e->getMessage());
            return $this->redirect('/admin/finance');
        }

        $this->flashSuccess('Reconciliation run #' . $runId . ' completed.');
        return $this->redirect('/admin/finance/reconciliations/' . $runId);
    }

    public function showRun(Request $request): Response
    {
        $id = (string) $request->routeParam('id', 0);
        $run = $this->finance->run((int) $id);
        if ($run === null) {
            $this->flashError('Reconciliation run not found.');
            return $this->redirect('/admin/finance');
        }

        return $this->render('admin/finance/run', [
            'pageTitle' => 'Reconciliation #' . $run['id'],
            'run'       => $run,
            'items'     => $this->finance->items((int) $id),
            'issues'    => FinanceService::ISSUES,
            'summary'   => json_decode((string) ($run['summary_json'] ?? '{}'), true) ?: [],
        ], 'layouts/admin');
    }

    public function resolveItem(Request $request): Response
    {
        $id = (string) $request->routeParam('id', 0);
        $note = trim((string) $request->input('note', ''));
        $runId = (int) $request->input('run_id', 0);
        if (strlen($note) < 5) {
            $this->flashError('Add a short resolution note (at least 5 characters).');
            return $this->redirect('/admin/finance/reconciliations/' . $runId);
        }
        $ok = $this->finance->resolveItem((int) $id, $note, (int) $this->auth->id());
        $ok ? $this->flashSuccess('Item resolved.') : $this->flashError('Item not found or already resolved.');
        return $this->redirect('/admin/finance/reconciliations/' . $runId);
    }

    /** Ask the gateways about every pending transaction (same as the cron). */
    public function syncPending(Request $request): Response
    {
        $rows = $this->payments->pendingForReconciliation(50, 5);
        $changed = 0;
        $errors = 0;
        foreach ($rows as $row) {
            try {
                $result = $this->donationService->reconcile((int) $row['id'], $this->auth->id());
                if (!empty($result['changed'])) {
                    $changed++;
                }
            } catch (Throwable) {
                $errors++;
            }
        }
        $this->flashSuccess(sprintf('Checked %d pending transaction(s): %d updated, %d error(s).', count($rows), $changed, $errors));
        return $this->redirect('/admin/finance');
    }

    // ------------------------------------------------------------- offline

    public function offlineForm(Request $request): Response
    {
        return $this->render('admin/finance/offline', [
            'pageTitle' => 'Record an offline donation',
            'purposes'  => $this->db->select('SELECT id, name FROM donation_purposes ORDER BY sort_order, name'),
            'sources'   => FinanceService::OFFLINE_SOURCES,
            'banks'     => $this->db->select("SELECT id, bank_name, account_number FROM bank_accounts WHERE status = 'active' ORDER BY is_international, sort_order"),
        ], 'layouts/admin');
    }

    public function offlineStore(Request $request): Response
    {
        try {
            $id = $this->finance->recordOffline($request->all(), (int) $this->auth->id());
        } catch (InvalidArgumentException $e) {
            return $this->flashFormStateTo(['form' => $e->getMessage()], $request->all(), '/admin/finance/offline');
        }
        $this->flashSuccess('Offline donation recorded.');
        return $this->redirect('/admin/donations/' . $id);
    }

    // --------------------------------------------------------- adjustments

    public function adjustForm(Request $request): Response
    {
        $id = (string) $request->routeParam('id', 0);
        $donation = $this->donations->find((int) $id);
        if ($donation === null) {
            $this->flashError('Donation not found.');
            return $this->redirect('/admin/donations');
        }
        $donation = $this->db->selectOne(
            'SELECT d.*, dn.name AS donor_name, dn.email AS donor_email, dp.name AS purpose_name, g.name AS gateway_name
             FROM donations d LEFT JOIN donors dn ON dn.id = d.donor_id LEFT JOIN donation_purposes dp ON dp.id = d.purpose_id
             LEFT JOIN gateways g ON g.id = d.gateway_id WHERE d.id = :id',
            ['id' => (int) $id]
        ) ?? $donation;

        return $this->render('admin/finance/adjust', [
            'pageTitle'    => 'Adjust ' . $donation['public_reference'],
            'donation'     => $donation,
            'transactions' => $this->payments->forDonation((int) $id),
            'adjustments'  => $this->finance->adjustmentsFor((int) $id),
            'types'        => FinanceService::ADJUSTMENT_TYPES,
        ], 'layouts/admin');
    }

    public function adjustStore(Request $request): Response
    {
        $id = (string) $request->routeParam('id', 0);
        $type = (string) $request->input('adjustment_type', '');
        $data = [
            'reason'                 => $request->input('reason', ''),
            'evidence_reference'     => $request->input('evidence_reference', ''),
            'payment_transaction_id' => (int) $request->input('payment_transaction_id', 0) ?: null,
            'new_amount_minor'       => (int) round(((float) $request->input('new_amount', 0)) * 100),
            'send_receipt'           => $request->input('send_receipt', ''),
        ];

        try {
            $result = $this->finance->adjust((int) $id, $type, $data, (int) $this->auth->id());
        } catch (InvalidArgumentException $e) {
            return $this->flashFormStateTo(['form' => $e->getMessage()], $request->all(), '/admin/finance/donations/' . $id . '/adjust');
        }

        $this->flashSuccess(sprintf('Adjustment saved (%s → %s).', $result['from'], $result['to']));
        return $this->redirect('/admin/donations/' . $id);
    }

    public function sendReceipt(Request $request): Response
    {
        $id = (string) $request->routeParam('id', 0);
        $ok = $this->finance->sendReceipt((int) $id);
        $ok ? $this->flashSuccess('Receipt queued for delivery.') : $this->flashError('No donor email on file for this donation.');
        return $this->redirect('/admin/donations/' . $id);
    }

    // -------------------------------------------------------------- export

    public function export(Request $request): Response
    {
        $month = (string) $request->input('month', gmdate('Y-m'));
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            $month = gmdate('Y-m');
        }

        $csv = "Date,Reference,Receipt,Status,Amount,Currency,Purpose,Type,Method,Bank reference,Gateway txn,Donor,Donor email\n";
        foreach ($this->finance->exportRows($month) as $r) {
            $anon = !empty($r['anonymous']);
            $csv .= implode(',', array_map(
                static fn (mixed $v): string => '"' . str_replace('"', '""', (string) $v) . '"',
                [
                    $r['dated'], $r['public_reference'], $r['receipt_number'], $r['status'],
                    number_format(((int) $r['amount_minor']) / 100, 2, '.', ''), $r['currency'],
                    $r['purpose'], $r['donation_type'], $r['method'], $r['bank_reference'], $r['provider_transaction_id'],
                    $anon ? 'Anonymous' : $r['donor_name'], $anon ? '' : $r['donor_email'],
                ]
            )) . "\n";
        }

        return Response::download($csv, 'almarah-finance-' . $month . '.csv');
    }
}
