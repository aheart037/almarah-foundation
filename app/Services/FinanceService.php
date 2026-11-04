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
        'online_transfer' => 'Online transfer',
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

    private function requireFinanceActor(int $actorId): void
    {
        $permitted=$this->db->int("SELECT COUNT(*) FROM users u JOIN user_roles ur ON ur.user_id=u.id JOIN roles r ON r.id=ur.role_id LEFT JOIN role_permissions rp ON rp.role_id=r.id LEFT JOIN permissions p ON p.id=rp.permission_id WHERE u.id=:id AND u.status='active' AND u.deleted_at IS NULL AND (r.name='super_admin' OR p.name='finance.manage')",['id'=>$actorId]);
        if ($permitted===0) { throw new InvalidArgumentException('Only active finance-authorized staff may modify financial records.'); }
    }

    // ------------------------------------------------------------ summaries

    /** Totals for a calendar month grouped by purpose and by method. */
    public function monthlySummary(string $month): array
    {
        [$start,$end]=$this->monthBounds($month);
        $params=['from'=>$start,'to'=>$end];
        // Only independently verified PKR gifts are shown as confirmed totals.
        $confirmed="d.status='completed' AND d.verification_status='verified'";
        $byPurpose=$this->db->select("SELECT COALESCE(dp.name,'General Fund') AS label, COUNT(*) AS n, SUM(d.amount_minor) AS total FROM donations d LEFT JOIN donation_purposes dp ON dp.id=d.purpose_id WHERE {$confirmed} AND d.currency='PKR' AND COALESCE(d.completed_at,d.created_at) BETWEEN :from AND :to GROUP BY label ORDER BY total DESC",$params);
        $byMethod=$this->db->select("SELECT COALESCE(g.name,d.source) AS label, COUNT(*) AS n, SUM(d.amount_minor) AS total FROM donations d LEFT JOIN gateways g ON g.id=d.gateway_id WHERE {$confirmed} AND d.currency='PKR' AND COALESCE(d.completed_at,d.created_at) BETWEEN :from AND :to GROUP BY label ORDER BY total DESC",$params);
        $byCurrency=$this->db->select("SELECT d.currency,COUNT(*) AS n,SUM(d.amount_minor) AS total FROM donations d WHERE {$confirmed} AND COALESCE(d.completed_at,d.created_at) BETWEEN :from AND :to GROUP BY d.currency ORDER BY d.currency",$params);
        $byStatus=$this->db->select("SELECT d.status,d.currency,COUNT(*) AS n,SUM(d.amount_minor) AS total FROM donations d WHERE d.created_at BETWEEN :from AND :to GROUP BY d.status,d.currency ORDER BY d.status,d.currency",$params);
        $pkr=null;
        foreach ($byCurrency as $row) { if ($row['currency']==='PKR') { $pkr=$row; break; } }
        return ['month'=>$month,'start'=>$start,'end'=>$end,'completed'=>(int)($pkr['total'] ?? 0),'count'=>(int)($pkr['n'] ?? 0),
            'by_purpose'=>$byPurpose,'by_method'=>$byMethod,'by_currency'=>$byCurrency,'by_status'=>$byStatus];
    }

    /** Last 12 months of independently verified PKR totals. */
    public function monthlyTrend(int $months = 12): array
    {
        return $this->db->select("SELECT DATE_FORMAT(COALESCE(completed_at,created_at),'%Y-%m') AS ym,COUNT(*) AS n,SUM(amount_minor) AS total FROM donations WHERE status='completed' AND verification_status='verified' AND currency='PKR' AND COALESCE(completed_at,created_at)>=DATE_SUB(CURDATE(),INTERVAL :m MONTH) GROUP BY ym ORDER BY ym",['m'=>$months]);
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
            "SELECT COUNT(*) AS donations_count, COALESCE(SUM(CASE WHEN d.currency='PKR' THEN d.amount_minor END),0) AS donations_minor,
                    SUM(d.status='completed' AND d.verification_status='verified' AND d.currency='PKR') AS verified_count,
                    COALESCE(SUM(CASE WHEN d.status='completed' AND d.verification_status='verified' AND d.currency='PKR' THEN d.amount_minor END),0) AS verified_minor,
                    SUM(d.status IN ('pending','processing') AND d.currency='PKR') AS pending_count,
                    COALESCE(SUM(CASE WHEN d.status IN ('pending','processing') AND d.currency='PKR' THEN d.amount_minor END),0) AS pending_minor,
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
        $note=trim($note);
        if (mb_strlen($note)<5 || mb_strlen($note)>500) { throw new InvalidArgumentException('Provide a resolution note of 5–500 characters.'); }
        return $this->db->transaction(function () use ($itemId,$note,$actorId): bool {
            $this->requireFinanceActor($actorId);
            $item=$this->db->selectOne('SELECT * FROM reconciliation_items WHERE id=:id FOR UPDATE',['id'=>$itemId]);
            if ($item===null || $item['resolved_at']!==null) { return false; }
            $this->db->update('reconciliation_items',[
                'resolved_at'=>gmdate('Y-m-d H:i:s'),'resolved_by'=>$actorId,'resolution_note'=>$note,
            ],'id=:id',['id'=>$itemId]);
            $this->audit->logRequired('finance.reconciliation.resolve','donation',(int)$item['donation_id'],[
                'item'=>$itemId,'issue'=>$item['issue'],'note'=>$note,
            ],$actorId);
            return true;
        });
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
        $result = $this->db->transaction(function () use ($donationId,$type,$data,$actorId): array {
        $this->db->selectOne('SELECT id FROM donations WHERE id=:id FOR UPDATE',['id'=>$donationId]);
        $this->requireFinanceActor($actorId);
        $donation = $this->donations->find($donationId);
        if ($donation === null) {
            throw new InvalidArgumentException('Donation not found.');
        }
        $reason = trim((string) ($data['reason'] ?? ''));
        if (mb_strlen($reason) < 10 || mb_strlen($reason) > 500) {
            throw new InvalidArgumentException('Please give a reason of at least 10 characters.');
        }

        $evidence=trim((string)($data['evidence_reference'] ?? ''));
        if (mb_strlen($evidence)>160) { throw new InvalidArgumentException('Evidence reference is too long.'); }
        $linkedId=(int)($data['payment_transaction_id'] ?? 0);
        if ($linkedId>0 && $this->db->int('SELECT COUNT(*) FROM payment_transactions WHERE id=:t AND donation_id=:d',['t'=>$linkedId,'d'=>$donationId])===0) {
            throw new InvalidArgumentException('The selected payment transaction does not belong to this donation.');
        }
        if (in_array($type,['refund_recorded','amount_correction'],true) && $evidence==='') {
            throw new InvalidArgumentException('An evidence reference is required for a refund or amount correction.');
        }
        $old = (string) $donation['status'];
        $oldAmount = (int) $donation['amount_minor'];
        $new = $old;
        $newAmount = $oldAmount;
        $now = gmdate('Y-m-d H:i:s');

        switch ($type) {
            case 'mark_completed':
                if (($donation['verification_status'] ?? 'legacy') !== 'verified' || $donation['gateway_id'] !== null) {
                    throw new InvalidArgumentException('New donations must pass independent staged verification; manual completion is prohibited.');
                }
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
                if ($donation['gateway_id'] !== null) { throw new InvalidArgumentException('Online payment status must be verified with the provider.'); }
                if (in_array($old, ['completed', 'refunded'], true)) {
                    throw new InvalidArgumentException('Completed donations cannot be marked failed — record a refund instead.');
                }
                $new = 'failed';
                $this->donations->updateStatus($donationId, 'failed');
                break;

            case 'amount_correction':
                if ($donation['gateway_id'] !== null) { throw new InvalidArgumentException('Gateway payment amounts cannot be manually changed.'); }
                if (in_array($old, ['completed','refunded'], true) || ($donation['verification_status'] ?? 'legacy') === 'legacy' || ($donation['verification_status'] ?? '') === 'verified') {
                    throw new InvalidArgumentException('Historical, completed and verified amounts cannot be edited; use an independently reviewed correction.');
                }
                $newAmount = (int) ($data['new_amount_minor'] ?? 0);
                if ($newAmount < 100 || $newAmount > (int) \App\Core\Config::get('app.donations.max_minor', 500000000) || $newAmount === $oldAmount) {
                    throw new InvalidArgumentException('Enter a different, positive amount.');
                }
                $this->db->update('donations', ['amount_minor' => $newAmount, 'verification_status' => ($donation['verification_status'] ?? 'legacy') === 'legacy' ? 'legacy' : 'submitted'], 'id = :id', ['id' => $donationId]);
                if (($donation['verification_status'] ?? 'legacy') !== 'legacy') {
                    $this->db->insert('donation_review_events', ['donation_id' => $donationId, 'actor_user_id' => $actorId, 'stage_key' => 'correction', 'decision' => 'reset', 'comment' => mb_substr($reason, 0, 1000)]);
                }
                break;

            case 'refund_recorded':
                if ($old !== 'completed') {
                    throw new InvalidArgumentException('Only completed donations can be refunded.');
                }
                if ($donation['gateway_id'] !== null) { throw new InvalidArgumentException('Online refunds require verified provider confirmation.'); }
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
            'evidence_reference'     => $evidence ?: null,
            'actor_user_id'          => $actorId,
            'ip_address'             => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);

        $this->audit->logRequired('finance.adjustment.' . $type, 'donation', $donationId, [
            'adjustment_id' => $adjustmentId, 'from' => $old, 'to' => $new,
            'old_amount' => $oldAmount, 'new_amount' => $newAmount, 'evidence' => $data['evidence_reference'] ?? null,
        ], $actorId);

        return ['id' => $adjustmentId, 'from' => $old, 'to' => $new];
        });
        if ($type === 'mark_completed' && !empty($data['send_receipt'])) {
            $this->sendReceipt($donationId);
        }
        return $result;
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
        // Keep the existing finance API, but never mark unreviewed gifts completed.
        return app(DonationReviewService::class)->submit($in, null, $actorId)['id'];
    }

    // ------------------------------------------------------------ receipts

    /** Receipt numbers are sequential per year: R2026-000123. */
    public function assignReceiptNumber(int $donationId): string
    {
        $donation = $this->donations->find($donationId);
        if ($donation !== null && !empty($donation['receipt_number'])) {
            return (string) $donation['receipt_number'];
        }
        if ($donation === null || !in_array($donation['verification_status'] ?? 'legacy', ['verified', 'legacy'], true) || $donation['status'] !== 'completed') {
            throw new InvalidArgumentException('An unverified donation cannot receive an official receipt.');
        }
        // New references are unique by database constraint; no COUNT(*) race.
        if (($donation['verification_status'] ?? 'legacy') === 'verified') {
            $number = 'R-' . $donation['public_reference'];
            $this->db->update('donations', ['receipt_number' => $number, 'receipt_generated_at' => gmdate('Y-m-d H:i:s')], 'id = :id AND receipt_number IS NULL', ['id' => $donationId]);
            return (string) $this->donations->find($donationId)['receipt_number'];
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

    public function sendReceipt(int $donationId, bool $resend = false): bool
    {
        $donation = $this->db->selectOne(
            'SELECT d.*, dn.name AS donor_name, dn.email AS donor_email, dp.name AS purpose_name, g.name AS gateway_name
             FROM donations d LEFT JOIN donors dn ON dn.id = d.donor_id
             LEFT JOIN donation_purposes dp ON dp.id = d.purpose_id LEFT JOIN gateways g ON g.id = d.gateway_id
             WHERE d.id = :id',
            ['id' => $donationId]
        );
        if ($donation === null || $donation['status'] !== 'completed' || !in_array($donation['verification_status'] ?? 'legacy', ['verified', 'legacy'], true) || empty($donation['donor_email']) || str_ends_with((string) $donation['donor_email'], '@almarah.local')) {
            return false;
        }
        $this->assignReceiptNumber($donationId);
        $method = $donation['gateway_name'] ?: (self::OFFLINE_SOURCES[$donation['source']] ?? ucfirst((string) $donation['source']));
        $donation['_fundraiser_url'] = base_url('donate');
        $this->mailer->donationReceipt(
            (string) $donation['donor_email'],
            (string) $donation['donor_name'],
            $donation,
            (string) ($donation['purpose_name'] ?? 'General Fund'),
            $method,
            $resend ? Str::randomHex(8) : null
        );
        if (!$donation['acknowledgment_self'] && !empty($donation['recipient_email']) &&
            filter_var($donation['recipient_email'], FILTER_VALIDATE_EMAIL) &&
            strcasecmp((string) $donation['recipient_email'], (string) $donation['donor_email']) !== 0) {
            $this->mailer->donationReceipt((string) $donation['recipient_email'],
                (string) ($donation['recipient_name'] ?: 'Friend'), $donation,
                (string) ($donation['purpose_name'] ?? 'General Fund'), $method,
                'recipient' . ($resend ? ':' . Str::randomHex(8) : ''));
        }
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
