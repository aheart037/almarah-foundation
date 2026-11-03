<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Str;
use App\Mail\Mailer;
use App\Repositories\DonationRepository;
use App\Repositories\DonorRepository;
use App\Repositories\PaymentRepository;
use InvalidArgumentException;

/**
 * Finance-manager tooling that sits on top of the payment engine:
 *
 *  - reconciliation runs (compare donations vs gateway transactions for a
 *    period and store every discrepancy as an actionable item),
 *  - manual adjustments with a mandatory reason + evidence trail,
 *  - offline donations (bank transfer / cash / cheque) recorded by staff,
 *  - monthly summaries by purpose and payment method.
 *
 * Nothing here talks to a gateway directly — the automatic verification of a
 * pending transaction remains DonationService::reconcile(); this class only
 * reports and records human decisions.
 */
final class FinanceService
{
    public const ADJUSTMENT_TYPES = [
        'mark_completed'    => 'Mark as completed (verified bank credit)',
        'mark_failed'       => 'Mark as failed / cancelled',
        'amount_correction' => 'Correct the amount',
        'refund_recorded'   => 'Record an external refund',
    ];

    public const OFFLINE_SOURCES = [
        'bank_transfer' => 'Bank transfer',
        'cash'          => 'Cash',
        'cheque'        => 'Cheque / pay order',
        'zelle'         => 'Zelle (USA)',
        'other'         => 'Other',
    ];

    public const ISSUES = [
        'donation_without_transaction'   => 'Completed donation has no completed gateway transaction',
        'transaction_without_completion' => 'Gateway transaction completed but donation is not',
        'amount_mismatch'                => 'Donation and transaction amounts differ',
        'stale_pending'                  => 'Pending for more than 24 hours',
        'duplicate_transaction'          => 'More than one completed transaction for a donation',
        'refund_without_status'          => 'Refund completed but donation not marked refunded',
    ];

    public function __construct(
        private Database $db,
        private DonationRepository $donations,
        private DonorRepository $donors,
        private PaymentRepository $payments,
        private AuditService $audit,
        private Mailer $mailer
    ) {
    }

    // ------------------------------------------------------------ summaries

    /** Totals for a calendar month grouped by purpose and by method. */
    public function monthlySummary(string $month): array
    {
        [$start, $end] = $this->monthBounds($month);
        $params = ['from' => $start, 'to' => $end];

        $byPurpose = $this->db->select(
            "SELECT COALESCE(dp.name, 'General Fund') AS label, COUNT(*) AS n, SUM(d.amount_minor) AS total
             FROM donations d LEFT JOIN donation_purposes dp ON dp.id = d.purpose_id
             WHERE d.status = 'completed' AND COALESCE(d.completed_at, d.created_at) BETWEEN :from AND :to
             GROUP BY label ORDER BY total DESC",
            $params
        );

        $byMethod = $this->db->select(
            "SELECT COALESCE(g.name, d.source) AS label, COUNT(*) AS n, SUM(d.amount_minor) AS total
             FROM donations d LEFT JOIN gateways g ON g.id = d.gateway_id
             WHERE d.status = 'completed' AND COALESCE(d.completed_at, d.created_at) BETWEEN :from AND :to
             GROUP BY label ORDER BY total DESC",
            $params
        );

        $byStatus = $this->db->select(
            "SELECT d.status, COUNT(*) AS n, SUM(d.amount_minor) AS total
             FROM donations d WHERE d.created_at BETWEEN :from AND :to GROUP BY d.status",
            $params
        );

        $completed = 0;
        $count = 0;
        foreach ($byStatus as $row) {
            if ($row['status'] === 'completed') {
                $completed = (int) $row['total'];
                $count = (int) $row['n'];
            }
        }

        return [
            'month'      => $month,
            'start'      => $start,
            'end'        => $end,
            'completed'  => $completed,
            'count'      => $count,
            'by_purpose' => $byPurpose,
            'by_method'  => $byMethod,
            'by_status'  => $byStatus,
        ];
    }

    /** @return array<int,array<string,mixed>> last 12 months of completed totals */
    public function monthlyTrend(int $months = 12): array
    {
        return $this->db->select(
            "SELECT DATE_FORMAT(COALESCE(completed_at, created_at), '%Y-%m') AS ym,
                    COUNT(*) AS n, SUM(amount_minor) AS total
             FROM donations
             WHERE status = 'completed'
               AND COALESCE(completed_at, created_at) >= DATE_SUB(CURDATE(), INTERVAL :m MONTH)
             GROUP BY ym ORDER BY ym",
            ['m' => $months]
        );
    }

    public function stalePendingCount(): int
    {
        return $this->db->int(
            "SELECT COUNT(*) FROM donations
             WHERE status IN ('pending','processing') AND created_at < DATE_SUB(UTC_TIMESTAMP(), INTERVAL 24 HOUR)"
        );
    }

    public function openIssueCount(): int
    {
        return $this->db->int('SELECT COUNT(*) FROM reconciliation_items WHERE resolved_at IS NULL');
    }

    // ------------------------------------------------------- reconciliation

    /**
     * Compare donations with gateway transactions for a period and persist a
     * run with one item per discrepancy. Returns the new run id.
     */
    public function runReconciliation(string $periodType, string $start, string $end, ?int $gatewayId, ?int $actorId): int
    {
        if (!in_array($periodType, ['daily', 'monthly', 'custom'], true)) {
            throw new InvalidArgumentException('Unknown period type.');
        }
        $from = $start . ' 00:00:00';
        $to = $end . ' 23:59:59';
        if ($from > $to) {
            throw new InvalidArgumentException('The period end is before its start.');
        }

        $gw = $gatewayId ? ' AND d.gateway_id = :gw' : '';
        $params = ['from' => $from, 'to' => $to] + ($gatewayId ? ['gw' => $gatewayId] : []);

        $stats = $this->db->selectOne(
            "SELECT COUNT(*) AS donations_count, COALESCE(SUM(d.amount_minor),0) AS donations_minor,
                    SUM(d.status = 'completed') AS verified_count,
                    COALESCE(SUM(CASE WHEN d.status = 'completed' THEN d.amount_minor END),0) AS verified_minor,
                    SUM(d.status IN ('pending','processing')) AS pending_count,
                    COALESCE(SUM(CASE WHEN d.status IN ('pending','processing') THEN d.amount_minor END),0) AS pending_minor,
                    SUM(d.status IN ('failed','cancelled','abandoned')) AS failed_count
             FROM donations d WHERE d.created_at BETWEEN :from AND :to{$gw}",
            $params
        ) ?? [];

        $issues = [];

        // 1. completed donation with no completed transaction (online only)
        foreach ($this->db->select(
            "SELECT d.id FROM donations d
             WHERE d.status = 'completed' AND d.gateway_id IS NOT NULL AND d.created_at BETWEEN :from AND :to{$gw}
               AND NOT EXISTS (SELECT 1 FROM payment_transactions pt WHERE pt.donation_id = d.id AND pt.status = 'completed')",
            $params
        ) as $r) {
            $issues[] = ['donation_id' => (int) $r['id'], 'txn' => null, 'issue' => 'donation_without_transaction', 'details' => 'No gateway transaction in completed state backs this donation.'];
        }

        // 2. completed transaction but donation not completed/refunded
        foreach ($this->db->select(
            "SELECT d.id, pt.id AS txn, d.status FROM donations d
             JOIN payment_transactions pt ON pt.donation_id = d.id AND pt.status = 'completed'
             WHERE d.status NOT IN ('completed','refunded') AND d.created_at BETWEEN :from AND :to{$gw}",
            $params
        ) as $r) {
            $issues[] = ['donation_id' => (int) $r['id'], 'txn' => (int) $r['txn'], 'issue' => 'transaction_without_completion', 'details' => 'Gateway reports success; donation is "' . $r['status'] . '".'];
        }

        // 3. amount mismatch
        foreach ($this->db->select(
            "SELECT d.id, pt.id AS txn, d.amount_minor AS a, pt.amount_minor AS b FROM donations d
             JOIN payment_transactions pt ON pt.donation_id = d.id AND pt.status = 'completed'
             WHERE pt.amount_minor <> d.amount_minor AND d.created_at BETWEEN :from AND :to{$gw}",
            $params
        ) as $r) {
            $issues[] = ['donation_id' => (int) $r['id'], 'txn' => (int) $r['txn'], 'issue' => 'amount_mismatch', 'details' => sprintf('Donation %s vs transaction %s.', money((int) $r['a']), money((int) $r['b']))];
        }

        // 4. stale pending
        foreach ($this->db->select(
            "SELECT d.id FROM donations d
             WHERE d.status IN ('pending','processing') AND d.created_at BETWEEN :from AND :to{$gw}
               AND d.created_at < DATE_SUB(UTC_TIMESTAMP(), INTERVAL 24 HOUR)",
            $params
        ) as $r) {
            $issues[] = ['donation_id' => (int) $r['id'], 'txn' => null, 'issue' => 'stale_pending', 'details' => 'Still pending after 24 hours — verify with the gateway or mark failed.'];
        }

        // 5. duplicate completed transactions
        foreach ($this->db->select(
            "SELECT d.id, COUNT(pt.id) AS c FROM donations d
             JOIN payment_transactions pt ON pt.donation_id = d.id AND pt.status = 'completed'
             WHERE d.created_at BETWEEN :from AND :to{$gw} GROUP BY d.id HAVING c > 1",
            $params
        ) as $r) {
            $issues[] = ['donation_id' => (int) $r['id'], 'txn' => null, 'issue' => 'duplicate_transaction', 'details' => $r['c'] . ' completed transactions — the donor may have been charged twice.'];
        }

        // 6. completed refund but donation not refunded
        foreach ($this->db->select(
            "SELECT d.id, pt.id AS txn FROM donations d
             JOIN payment_transactions pt ON pt.donation_id = d.id
             JOIN payment_refunds pr ON pr.payment_transaction_id = pt.id AND pr.status = 'completed'
             WHERE d.status <> 'refunded' AND d.created_at BETWEEN :from AND :to{$gw}",
            $params
        ) as $r) {
            $issues[] = ['donation_id' => (int) $r['id'], 'txn' => (int) $r['txn'], 'issue' => 'refund_without_status', 'details' => 'A refund completed but the donation status was not updated.'];
        }

        $byIssue = [];
        foreach ($issues as $i) {
            $byIssue[$i['issue']] = ($byIssue[$i['issue']] ?? 0) + 1;
        }

        $runId = $this->db->insert('reconciliation_runs', [
            'period_type'     => $periodType,
            'period_start'    => $from,
            'period_end'      => $to,
            'gateway_id'      => $gatewayId,
            'donations_count' => (int) ($stats['donations_count'] ?? 0),
            'donations_minor' => (int) ($stats['donations_minor'] ?? 0),
            'verified_count'  => (int) ($stats['verified_count'] ?? 0),
            'verified_minor'  => (int) ($stats['verified_minor'] ?? 0),
            'pending_count'   => (int) ($stats['pending_count'] ?? 0),
            'pending_minor'   => (int) ($stats['pending_minor'] ?? 0),
            'failed_count'    => (int) ($stats['failed_count'] ?? 0),
            'mismatch_count'  => count($issues),
            'summary_json'    => json_encode(['issues' => $byIssue], JSON_UNESCAPED_SLASHES) ?: '{}',
            'run_by'          => $actorId,
        ]);

        foreach ($issues as $i) {
            $this->db->insert('reconciliation_items', [
                'run_id'                 => $runId,
                'donation_id'            => $i['donation_id'],
                'payment_transaction_id' => $i['txn'],
                'issue'                  => $i['issue'],
                'details'                => $i['details'],
            ]);
        }

        $this->audit->log('finance.reconciliation.run', 'reconciliation_run', $runId, [
            'period' => [$from, $to], 'gateway_id' => $gatewayId, 'issues' => count($issues),
        ], $actorId);

        return $runId;
    }

    /** @return array<int,array<string,mixed>> */
    public function runs(int $limit = 20): array
    {
        return $this->db->select(
            "SELECT r.*, CONCAT_WS(' ', u.first_name, u.last_name) AS run_by_name, g.name AS gateway_name,
                    (SELECT COUNT(*) FROM reconciliation_items i WHERE i.run_id = r.id AND i.resolved_at IS NULL) AS open_items
             FROM reconciliation_runs r
             LEFT JOIN users u ON u.id = r.run_by
             LEFT JOIN gateways g ON g.id = r.gateway_id
             ORDER BY r.id DESC LIMIT {$limit}"
        );
    }

    public function run(int $id): ?array
    {
        return $this->db->selectOne(
            'SELECT r.*, CONCAT_WS(" ", u.first_name, u.last_name) AS run_by_name, g.name AS gateway_name FROM reconciliation_runs r
             LEFT JOIN users u ON u.id = r.run_by LEFT JOIN gateways g ON g.id = r.gateway_id WHERE r.id = :id',
            ['id' => $id]
        );
    }

    /** @return array<int,array<string,mixed>> */
    public function items(int $runId): array
    {
        return $this->db->select(
            'SELECT i.*, d.public_reference, d.status AS donation_status, d.amount_minor, d.currency,
                    dn.name AS donor_name, CONCAT_WS(" ", u.first_name, u.last_name) AS resolved_by_name
             FROM reconciliation_items i
             JOIN donations d ON d.id = i.donation_id
             LEFT JOIN donors dn ON dn.id = d.donor_id
             LEFT JOIN users u ON u.id = i.resolved_by
             WHERE i.run_id = :run ORDER BY i.resolved_at IS NOT NULL, i.id',
            ['run' => $runId]
        );
    }

    public function resolveItem(int $itemId, string $note, int $actorId): bool
    {
        $item = $this->db->selectOne('SELECT * FROM reconciliation_items WHERE id = :id', ['id' => $itemId]);
        if ($item === null || $item['resolved_at'] !== null) {
            return false;
        }
        $this->db->update('reconciliation_items', [
            'resolved_at'     => gmdate('Y-m-d H:i:s'),
            'resolved_by'     => $actorId,
            'resolution_note' => $note,
        ], 'id = :id', ['id' => $itemId]);

        $this->audit->log('finance.reconciliation.resolve', 'donation', (int) $item['donation_id'], [
            'item' => $itemId, 'issue' => $item['issue'], 'note' => $note,
        ], $actorId);

        return true;
    }

    // ---------------------------------------------------------- adjustments

    /**
     * Apply a manual adjustment. $data: reason (required), evidence_reference,
     * new_amount_minor (amount_correction only).
     */
    public function adjust(int $donationId, string $type, array $data, int $actorId): array
    {
        if (!isset(self::ADJUSTMENT_TYPES[$type])) {
            throw new InvalidArgumentException('Unknown adjustment type.');
        }
        $donation = $this->donations->find($donationId);
        if ($donation === null) {
            throw new InvalidArgumentException('Donation not found.');
        }
        $reason = trim((string) ($data['reason'] ?? ''));
        if (strlen($reason) < 10) {
            throw new InvalidArgumentException('Please give a reason of at least 10 characters.');
        }

        $old = (string) $donation['status'];
        $oldAmount = (int) $donation['amount_minor'];
        $new = $old;
        $newAmount = $oldAmount;
        $now = gmdate('Y-m-d H:i:s');

        switch ($type) {
            case 'mark_completed':
                if ($old === 'completed') {
                    throw new InvalidArgumentException('This donation is already completed.');
                }
                if ($old === 'refunded') {
                    throw new InvalidArgumentException('A refunded donation cannot be re-completed.');
                }
                $new = 'completed';
                $this->donations->updateStatus($donationId, 'completed', $now);
                $this->assignReceiptNumber($donationId);
                break;

            case 'mark_failed':
                if (in_array($old, ['completed', 'refunded'], true)) {
                    throw new InvalidArgumentException('Completed donations cannot be marked failed — record a refund instead.');
                }
                $new = 'failed';
                $this->donations->updateStatus($donationId, 'failed');
                break;

            case 'amount_correction':
                $newAmount = (int) ($data['new_amount_minor'] ?? 0);
                if ($newAmount < 100 || $newAmount === $oldAmount) {
                    throw new InvalidArgumentException('Enter a different, positive amount.');
                }
                $this->db->update('donations', ['amount_minor' => $newAmount], 'id = :id', ['id' => $donationId]);
                break;

            case 'refund_recorded':
                if ($old !== 'completed') {
                    throw new InvalidArgumentException('Only completed donations can be refunded.');
                }
                $new = 'refunded';
                $this->donations->updateStatus($donationId, 'refunded');
                break;
        }

        $adjustmentId = $this->db->insert('financial_adjustments', [
            'donation_id'            => $donationId,
            'payment_transaction_id' => $data['payment_transaction_id'] ?? null,
            'adjustment_type'        => $type,
            'old_status'             => $old,
            'new_status'             => $new,
            'old_amount_minor'       => $oldAmount,
            'new_amount_minor'       => $newAmount,
            'reason'                 => $reason,
            'evidence_reference'     => trim((string) ($data['evidence_reference'] ?? '')) ?: null,
            'actor_user_id'          => $actorId,
            'ip_address'             => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);

        $this->audit->log('finance.adjustment.' . $type, 'donation', $donationId, [
            'adjustment_id' => $adjustmentId, 'from' => $old, 'to' => $new,
            'old_amount' => $oldAmount, 'new_amount' => $newAmount, 'evidence' => $data['evidence_reference'] ?? null,
        ], $actorId);

        if ($type === 'mark_completed' && !empty($data['send_receipt'])) {
            $this->sendReceipt($donationId);
        }

        return ['id' => $adjustmentId, 'from' => $old, 'to' => $new];
    }

    /** @return array<int,array<string,mixed>> */
    public function adjustmentsFor(int $donationId): array
    {
        return $this->db->select(
            'SELECT a.*, CONCAT_WS(" ", u.first_name, u.last_name) AS actor_name FROM financial_adjustments a LEFT JOIN users u ON u.id = a.actor_user_id
             WHERE a.donation_id = :d ORDER BY a.id DESC',
            ['d' => $donationId]
        );
    }

    /** @return array<int,array<string,mixed>> */
    public function recentAdjustments(int $limit = 15): array
    {
        return $this->db->select(
            "SELECT a.*, CONCAT_WS(' ', u.first_name, u.last_name) AS actor_name, d.public_reference FROM financial_adjustments a
             LEFT JOIN users u ON u.id = a.actor_user_id JOIN donations d ON d.id = a.donation_id
             ORDER BY a.id DESC LIMIT {$limit}"
        );
    }

    // ------------------------------------------------------------- offline

    /**
     * Record a donation that arrived outside the website (bank credit, cash,
     * cheque, Zelle…). It is stored as completed immediately with the staff
     * member and bank reference recorded. Returns the donation id.
     */
    public function recordOffline(array $in, int $actorId): int
    {
        $source = (string) ($in['source'] ?? 'bank_transfer');
        if (!isset(self::OFFLINE_SOURCES[$source])) {
            throw new InvalidArgumentException('Unknown source.');
        }
        $amountMinor = (int) round(((float) ($in['amount'] ?? 0)) * 100);
        if ($amountMinor < 100) {
            throw new InvalidArgumentException('Amount must be at least 1.00.');
        }
        $currency = strtoupper(trim((string) ($in['currency'] ?? 'PKR')));
        if (!preg_match('/^[A-Z]{3}$/', $currency)) {
            throw new InvalidArgumentException('Currency must be a 3-letter code.');
        }
        $name = trim((string) ($in['donor_name'] ?? ''));
        $email = strtolower(trim((string) ($in['donor_email'] ?? '')));
        $anonymous = !empty($in['anonymous']);
        if ($name === '') {
            $name = $anonymous ? 'Anonymous donor' : '';
        }
        if ($name === '') {
            throw new InvalidArgumentException('Donor name is required (tick anonymous if unknown).');
        }
        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException('Donor email is not valid.');
        }
        $receivedAt = trim((string) ($in['received_at'] ?? ''));
        $receivedAt = $receivedAt !== '' ? $receivedAt . ' 12:00:00' : gmdate('Y-m-d H:i:s');
        if (strtotime($receivedAt) === false) {
            throw new InvalidArgumentException('Received date is not valid.');
        }

        $donorId = null;
        if ($email !== '') {
            $donorId = $this->donors->findOrCreate($name, $email, trim((string) ($in['donor_phone'] ?? '')) ?: null);
        } elseif (!$anonymous) {
            $donorId = $this->db->insert('donors', [
                'name'  => $name,
                'email' => 'offline+' . Str::randomHex(6) . '@almarah.local',
                'phone' => trim((string) ($in['donor_phone'] ?? '')) ?: null,
            ]);
        }

        $purposeId = (int) ($in['purpose_id'] ?? 0) ?: null;
        if ($purposeId && $this->db->selectOne('SELECT id FROM donation_purposes WHERE id = :id', ['id' => $purposeId]) === null) {
            throw new InvalidArgumentException('Unknown purpose.');
        }

        $donationId = $this->donations->create([
            'public_reference' => Str::publicReference('ALM'),
            'donor_id'         => $donorId,
            'purpose_id'       => $purposeId,
            'donation_type'    => ($in['donation_type'] ?? '') === 'monthly' ? 'monthly' : 'one_time',
            'source'           => $source,
            'gateway_id'       => null,
            'bank_reference'   => trim((string) ($in['bank_reference'] ?? '')) ?: null,
            'recorded_by'      => $actorId,
            'amount_minor'     => $amountMinor,
            'currency'         => $currency,
            'donor_message'    => trim((string) ($in['note'] ?? '')) ?: null,
            'anonymous'        => $anonymous,
            'status'           => 'completed',
        ]);

        $this->db->update('donations', ['completed_at' => $receivedAt, 'created_at' => $receivedAt], 'id = :id', ['id' => $donationId]);
        $this->assignReceiptNumber($donationId);

        $this->audit->log('finance.offline_donation.create', 'donation', $donationId, [
            'source' => $source, 'amount_minor' => $amountMinor, 'currency' => $currency,
            'bank_reference' => $in['bank_reference'] ?? null,
        ], $actorId);

        if ($email !== '' && !empty($in['send_receipt'])) {
            $this->sendReceipt($donationId);
        }

        return $donationId;
    }

    // ------------------------------------------------------------ receipts

    /** Receipt numbers are sequential per year: R2026-000123. */
    public function assignReceiptNumber(int $donationId): string
    {
        $donation = $this->donations->find($donationId);
        if ($donation !== null && !empty($donation['receipt_number'])) {
            return (string) $donation['receipt_number'];
        }
        $year = gmdate('Y');
        $seq = $this->db->int(
            "SELECT COUNT(*) FROM donations WHERE receipt_number LIKE :p",
            ['p' => 'R' . $year . '-%']
        ) + 1;
        $number = sprintf('R%s-%06d', $year, $seq);
        $this->db->update('donations', ['receipt_number' => $number], 'id = :id', ['id' => $donationId]);
        return $number;
    }

    public function sendReceipt(int $donationId): bool
    {
        $donation = $this->db->selectOne(
            'SELECT d.*, dn.name AS donor_name, dn.email AS donor_email, dp.name AS purpose_name, g.name AS gateway_name
             FROM donations d LEFT JOIN donors dn ON dn.id = d.donor_id
             LEFT JOIN donation_purposes dp ON dp.id = d.purpose_id LEFT JOIN gateways g ON g.id = d.gateway_id
             WHERE d.id = :id',
            ['id' => $donationId]
        );
        if ($donation === null || empty($donation['donor_email']) || str_ends_with((string) $donation['donor_email'], '@almarah.local')) {
            return false;
        }
        $method = $donation['gateway_name'] ?: (self::OFFLINE_SOURCES[$donation['source']] ?? ucfirst((string) $donation['source']));
        $donation['_fundraiser_url'] = base_url('donate');
        $this->mailer->donationReceipt(
            (string) $donation['donor_email'],
            (string) $donation['donor_name'],
            $donation,
            (string) ($donation['purpose_name'] ?? 'General Fund'),
            $method
        );
        $this->donations->setReceiptStatus($donationId, 'queued');
        return true;
    }

    // -------------------------------------------------------------- export

    /** @return array<int,array<string,mixed>> rows for the monthly CSV */
    public function exportRows(string $month): array
    {
        [$start, $end] = $this->monthBounds($month);
        return $this->db->select(
            "SELECT d.public_reference, d.receipt_number, COALESCE(d.completed_at, d.created_at) AS dated, d.status,
                    d.amount_minor, d.currency, d.donation_type, d.source, d.bank_reference, d.anonymous,
                    COALESCE(dp.name, 'General Fund') AS purpose, COALESCE(g.name, d.source) AS method,
                    dn.name AS donor_name, dn.email AS donor_email, pt.provider_transaction_id
             FROM donations d
             LEFT JOIN donation_purposes dp ON dp.id = d.purpose_id
             LEFT JOIN gateways g ON g.id = d.gateway_id
             LEFT JOIN donors dn ON dn.id = d.donor_id
             LEFT JOIN payment_transactions pt ON pt.donation_id = d.id AND pt.status = 'completed'
             WHERE d.status IN ('completed','refunded') AND COALESCE(d.completed_at, d.created_at) BETWEEN :from AND :to
             GROUP BY d.id ORDER BY dated",
            ['from' => $start, 'to' => $end]
        );
    }

    /** @return array{0:string,1:string} */
    public function monthBounds(string $month): array
    {
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            $month = gmdate('Y-m');
        }
        $start = $month . '-01 00:00:00';
        $end = date('Y-m-t', strtotime($start)) . ' 23:59:59';
        return [$start, $end];
    }
}
