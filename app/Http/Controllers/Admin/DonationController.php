<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Core\View;
use App\Http\Controllers\Controller;
use App\Repositories\DonationRepository;
use App\Repositories\PaymentRepository;
use App\Services\AuthService;
use App\Services\DonationService;
use InvalidArgumentException;

/**
 * Donations, transactions and gateway callbacks.
 *
 * Refunds and manual reconciliation both ask the provider server-to-server and
 * write the outcome through PaymentStatusService — the admin screen cannot
 * simply declare a payment successful.
 */
final class DonationController extends Controller
{
    public function __construct(
        View $view,
        Session $session,
        Csrf $csrf,
        AuthService $auth,
        private Database $db,
        private DonationRepository $donations,
        private PaymentRepository $payments,
        private DonationService $service
    ) {
        parent::__construct($view, $session, $csrf, $auth);
    }

    public function index(Request $request): Response
    {
        $filters = [
            'q'        => trim((string) $request->input('q', '')),
            'status'   => (string) $request->input('status', ''),
            'gateway'  => (string) $request->input('gateway', ''),
            'date_from'=> (string) $request->input('date_from', ''),
            'date_to'  => (string) $request->input('date_to', ''),
            'purpose_id' => (int) $request->input('purpose_id', 0),
            'source'   => (string) $request->input('source', ''),
        ];

        foreach (['donor_name','phone','email','amount_min','amount_max','receipt_number','transaction_id','payment_method','verification_status','recipient','referrer','staff_member'] as $key) {
            $filters[$key] = trim((string) $request->input($key, ''));
        }

        $page = max(1, (int) $request->input('page', 1));
        $perPage = 25;

        $result = $this->donations->search($filters, $page, $perPage);

        return $this->render('admin/donations/index', [
            'pageTitle' => 'Donations',
            'donations' => $result['rows'],
            'total'     => $result['total'],
            'sums'      => $result['sums'],
            'page'      => $page,
            'lastPage'  => max(1, (int) ceil($result['total'] / $perPage)),
            'filters'   => $filters,
            'totals'    => $this->donations->totalsByStatus(),
            'counts'    => $this->payments->statusCounts(),
            'purposes'  => $this->db->select('SELECT id, name FROM donation_purposes ORDER BY sort_order, name'),
        ], 'layouts/admin');
    }

    public function show(Request $request): Response
    {
        $donation = $this->donations->find((int) $request->routeParam('id', 0));

        if ($donation === null) {
            abort(404, 'Donation not found.');
        }

        $transactions = $this->payments->forDonation((int) $donation['id']);

        $callbacks = [];
        foreach ($transactions as $transaction) {
            $callbacks[(int) $transaction['id']] = $this->payments->callbacksFor((int) $transaction['id']);
        }

        $refunds = [];
        foreach ($transactions as $transaction) {
            $refunds[(int) $transaction['id']] = $this->payments->refundsFor((int) $transaction['id']);
        }

        return $this->render('admin/donations/show', [
            'pageTitle'    => 'Donation ' . (string) $donation['public_reference'],
            'donation'     => $donation,
            'transactions' => $transactions,
            'callbacks'    => $callbacks,
            'refunds'      => $refunds,
            'auditTrail'   => $this->db->select("SELECT a.created_at, a.action, u.email AS actor FROM audit_logs a LEFT JOIN users u ON u.id=a.user_id WHERE a.entity_type='donation' AND a.entity_id=:id ORDER BY a.id DESC LIMIT 100", ['id' => (string) $donation['id']]),
            'reviewHistory' => \app(\App\Services\DonationReviewService::class)->history((int) $donation['id']),
            'duplicates' => \app(\App\Services\DonationReviewService::class)->possibleDuplicates($donation),
            'emailHistory' => $this->db->select("SELECT id, event_key, recipient_email, status, attempts, last_error, sent_at, created_at FROM email_jobs WHERE dedupe_key LIKE :key ORDER BY id DESC", ['key' => 'donation.receipt:' . $donation['id'] . '%']),
            'reviewStages' => \app(\App\Services\DonationReviewService::class)->stages(),
            'staffIdentity' => $this->db->selectOne('SELECT creator.email AS created_by, verifier.email AS verified_by FROM donations d LEFT JOIN users creator ON creator.id=d.recorded_by LEFT JOIN users verifier ON verifier.id=d.verified_by WHERE d.id=:id', ['id' => $donation['id']]),
        ], 'layouts/admin');
    }

    /** Ask the provider what happened to a transaction we never heard back on. */
    public function reconcile(Request $request): Response
    {
        $donation = $this->donations->find((int) $request->routeParam('id', 0));

        if ($donation === null) {
            abort(404, 'Donation not found.');
        }

        $transactions = $this->payments->forDonation((int) $donation['id']);

        if ($transactions === []) {
            $this->flashError('There is no payment transaction for this donation yet.');
            return $this->redirect('/admin/donations/' . $donation['id']);
        }

        $result = $this->service->reconcile((int) $transactions[0]['id'], (int) $this->auth->id());

        $this->flashSuccess($result['message'] ?? 'Reconciliation finished.');
        return $this->redirect('/admin/donations/' . $donation['id']);
    }

    public function reconcileTransaction(Request $request): Response
    {
        $result = $this->service->reconcile((int) $request->routeParam('id', 0), (int) $this->auth->id());

        if (!empty($result['donation_id'])) {
            $this->flashSuccess($result['message'] ?? 'Reconciliation finished.');
            return $this->redirect('/admin/donations/' . $result['donation_id']);
        }

        $this->flashError($result['message'] ?? 'Reconciliation could not be completed.');
        return $this->redirect('/admin/donations');
    }

    public function refund(Request $request): Response
    {
        $donation = $this->donations->find((int) $request->routeParam('id', 0));

        if ($donation === null) {
            abort(404, 'Donation not found.');
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'nullable',
            'reason' => 'required|string|min:5|max:500',
        ]);

        if ($validator->fails()) {
            return $this->flashFormState($validator->errors(), $request->all());
        }

        $transactions = $this->payments->forDonation((int) $donation['id']);

        if ($transactions === []) {
            $this->flashError('There is no payment transaction to refund.');
            return $this->redirect('/admin/donations/' . $donation['id']);
        }

        $amount = (string) $request->input('amount', '');
        $normalAmount = str_replace(',', '', trim($amount));
        if ($normalAmount !== '' && !preg_match('/^[0-9]{1,9}(?:\.[0-9]{1,2})?$/D', $normalAmount)) {
            $this->flashError('Enter a valid refund amount with up to two decimal places.');
            return $this->redirect('/admin/donations/' . $donation['id']);
        }
        $amountMinor = $normalAmount !== '' ? \App\Core\Money::fromProviderString($normalAmount) : null;

        try {
            $result = $this->service->refund(
                (int) $transactions[0]['id'],
                (int) $this->auth->id(),
                $amountMinor
            );
        } catch (InvalidArgumentException $e) {
            $this->flashError($e->getMessage());
            return $this->redirect('/admin/donations/' . $donation['id']);
        }

        if (!empty($result['ok'])) {
            $this->flashSuccess($result['message'] ?? 'Refund submitted to the gateway.');
        } else {
            $this->flashError($result['message'] ?? 'The refund could not be completed.');
        }

        return $this->redirect('/admin/donations/' . $donation['id']);
    }

    public function export(Request $request): Response
    {
        $filters = [
            'q'         => trim((string) $request->input('q', '')),
            'status'    => (string) $request->input('status', ''),
            'gateway'   => (string) $request->input('gateway', ''),
            'date_from' => (string) $request->input('date_from', ''),
            'date_to'   => (string) $request->input('date_to', ''),
            'purpose_id' => (int) $request->input('purpose_id', 0),
        ];

        foreach (['donor_name','phone','email','amount_min','amount_max','receipt_number','transaction_id','payment_method','verification_status','recipient','referrer','staff_member'] as $key) {
            $filters[$key] = trim((string) $request->input($key, ''));
        }
        $path = tempnam(sys_get_temp_dir(), 'almarah-ledger-');
        if ($path === false) throw new \RuntimeException('Private export storage is unavailable.');
        chmod($path, 0600);
        $handle = fopen($path, 'wb');
        if ($handle === false) { @unlink($path); throw new \RuntimeException('Could not open private export.'); }
        try {
            $total = 0;
            $this->db->transaction(function () use ($filters, $handle, &$total): void {
                $header = "Reference,Receipt,Transaction date,Status,Verification,Method,Amount,Currency,Donor,Donor phone,Donor email,Recipient,Referrer,Created by,Verified by,Provider transaction,Bank reference\n";
                if (fwrite($handle, $header) !== strlen($header)) throw new \RuntimeException('Could not write the donation export.');

                // Preserve the existing ledger columns and filters, without the
                // former silent first-5,000-rows limit. A DB snapshot holds rows
                // steady across pages and the file stays private until served.
                $page = 1;
                do {
                    $result = $this->donations->search($filters, $page++, 500);
                    $total = (int) $result['total'];
                    foreach ($result['rows'] as $row) {
                        $values = [
                            $row['public_reference'] ?? '', $row['receipt_number'] ?? '',
                            $row['transaction_at'] ?: $row['created_at'], $row['status'], $row['verification_status'],
                            $row['gateway_code'] ?: ($row['payment_method'] ?: ($row['source'] ?? 'offline')),
                            number_format(((int) ($row['amount_minor'] ?? 0)) / 100, 2, '.', ''),
                            $row['currency'] ?? 'PKR', $row['donor_name'] ?? '', $row['donor_phone'] ?? '',
                            $row['donor_email'] ?? '', $row['recipient_name'] ?? '', $row['referrer_name'] ?? '',
                            $row['created_by_email'] ?? '', $row['verified_by_email'] ?? '',
                            $row['provider_transaction_id'] ?? '', $row['bank_reference'] ?? '',
                        ];
                        $csv = implode(',', array_map(static function (mixed $value): string {
                            $text = (string) $value;
                            // Prevent spreadsheet formula execution in donor input.
                            if (preg_match('/^[\s\x00-\x1F]*[=+@-]/u', $text)) $text = "'" . $text;
                            return '"' . str_replace('"', '""', $text) . '"';
                        }, $values)) . "\n";
                        if (fwrite($handle, $csv) !== strlen($csv)) throw new \RuntimeException('Could not write the donation export.');
                    }
                } while (($page - 1) * 500 < $total);
            });
            if (!fclose($handle)) throw new \RuntimeException('Could not finish the donation export.');
            \App\Core\App::instance()->make(\App\Services\AuditService::class)->logRequired(
                'donation.ledger.exported', 'donations', null, ['rows' => $total], (int) $this->auth->id()
            );
            return Response::temporaryFile($path, 'almarah-donations-' . gmdate('Y-m-d') . '.csv', 'text/csv; charset=utf-8');
        } catch (\Throwable $e) {
            if (is_resource($handle)) fclose($handle);
            @unlink($path);
            throw $e;
        }
    }

    /** Raw provider traffic, for debugging a stuck payment. */
    public function callbacks(Request $request): Response
    {
        $gateway = (string) $request->input('gateway', '');

        return $this->render('admin/donations/callbacks', [
            'pageTitle' => 'Gateway callbacks',
            'callbacks' => $this->payments->recentCallbacks(80),
            'gateway'   => $gateway,
        ], 'layouts/admin');
    }
}
