<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

/**
 * Central donation ledger (read-only). One definition of "confirmed":
 *  - a bank-slip / offline donation verified by finance (verification_status = verified, status = completed), or
 *  - a gateway donation the gateway has completed (gateways are treated as verified).
 * Everything else (pending, rejected, legacy/unreviewed, refunded, failed) never counts toward confirmed totals.
 */
final class DonationLedgerService
{
    public const CONFIRMED = "(d.status = 'completed' AND (d.verification_status = 'verified' OR d.gateway_id IS NOT NULL))";
    public const LEDGER_STATUSES = ['pending_verification', 'verified', 'rejected', 'unreviewed', 'gateway_unconfirmed'];

    public function __construct(private Database $db)
    {
    }

    public function statusExpr(): string
    {
        return "CASE WHEN " . self::CONFIRMED . " THEN 'verified'
                     WHEN d.verification_status = 'rejected' THEN 'rejected'
                     WHEN d.gateway_id IS NOT NULL THEN 'gateway_unconfirmed'
                     WHEN d.verification_status = 'submitted' THEN 'pending_verification'
                     WHEN d.verification_status = 'verified' THEN 'verified'
                     ELSE 'unreviewed' END";
    }

    private const FROM = "FROM donations d
        LEFT JOIN donors dn ON dn.id = d.donor_id
        LEFT JOIN bank_accounts b ON b.id = d.bank_account_id
        LEFT JOIN gateways g ON g.id = d.gateway_id
        LEFT JOIN users vu ON vu.id = d.verified_by
        LEFT JOIN users ru ON ru.id = d.recorded_by";

    /** @return array{0:string,1:array<string,mixed>} */
    private function where(array $f): array
    {
        $w = ['1 = 1'];
        $p = [];
        $q = trim((string) ($f['q'] ?? ''));
        if ($q !== '') {
            $w[] = '(d.public_reference LIKE :q1 OR dn.name LIKE :q2 OR dn.email LIKE :q3 OR dn.phone LIKE :q4 OR d.bank_reference LIKE :q5)';
            foreach (['q1', 'q2', 'q3', 'q4', 'q5'] as $k) {
                $p[$k] = '%' . addcslashes($q, '%_\\') . '%';
            }
        }
        $channel = (string) ($f['channel'] ?? '');
        if ($channel === 'online') $w[] = 'd.recorded_by IS NULL';
        if ($channel === 'offline') $w[] = 'd.recorded_by IS NOT NULL';
        if ((int) ($f['recorded_by'] ?? 0) > 0) {
            $w[] = 'd.recorded_by = :rec';
            $p['rec'] = (int) $f['recorded_by'];
        }
        if ((int) ($f['bank_account_id'] ?? 0) > 0) {
            $w[] = 'd.bank_account_id = :bank';
            $p['bank'] = (int) $f['bank_account_id'];
        }
        $status = (string) ($f['status'] ?? '');
        if (in_array($status, self::LEDGER_STATUSES, true)) {
            $w[] = '(' . $this->statusExpr() . ') = :lstatus';
            $p['lstatus'] = $status;
        }
        $cur = strtoupper((string) ($f['currency'] ?? ''));
        if (preg_match('/^[A-Z]{3}$/', $cur)) {
            $w[] = 'd.currency = :cur';
            $p['cur'] = $cur;
        }
        foreach (['date_from' => ['>=', ' 00:00:00'], 'date_to' => ['<=', ' 23:59:59']] as $key => [$op, $time]) {
            $v = (string) ($f[$key] ?? '');
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) {
                $w[] = "COALESCE(d.transaction_at, d.created_at) {$op} :{$key}";
                $p[$key] = $v . $time;
            }
        }
        return [implode(' AND ', $w), $p];
    }

    /** @return array{rows:list<array<string,mixed>>,total:int,confirmed:array<string,int>,pending:array<string,int>} */
    public function search(array $filters, int $page, int $perPage = 25): array
    {
        [$where, $params] = $this->where($filters);
        $offset = max(0, ($page - 1) * $perPage);
        $total = $this->db->int('SELECT COUNT(*) ' . self::FROM . " WHERE {$where}", $params);
        $rows = $this->db->select(
            "SELECT d.id, d.public_reference, d.amount_minor, d.currency, d.status, d.verification_status, d.source, d.bank_reference,
                    d.payment_proof_path, d.verified_at, d.created_at, d.gateway_id, d.recorded_by,
                    COALESCE(d.transaction_at, d.created_at) AS donation_date,
                    dn.name AS donor_name, dn.email AS donor_email, dn.phone AS donor_phone, d.anonymous,
                    b.bank_name, g.code AS gateway_code, vu.email AS verified_by_email, ru.email AS recorded_by_email,
                    CASE WHEN d.gateway_id IS NOT NULL THEN 'gateway' WHEN d.recorded_by IS NULL THEN 'online_slip' ELSE 'offline' END AS channel,
                    " . $this->statusExpr() . " AS ledger_status
             " . self::FROM . " WHERE {$where} ORDER BY d.id DESC LIMIT {$perPage} OFFSET {$offset}",
            $params
        );
        $confirmed = [];
        $pending = [];
        foreach ($this->db->select(
            "SELECT d.currency, (" . self::CONFIRMED . ") AS ok, COALESCE(SUM(d.amount_minor),0) AS total
             " . self::FROM . " WHERE {$where} AND (" . self::CONFIRMED . " OR d.verification_status = 'submitted') GROUP BY d.currency, ok",
            $params
        ) as $r) {
            if ((int) $r['ok'] === 1) $confirmed[(string) $r['currency']] = (int) $r['total'];
            else $pending[(string) $r['currency']] = (int) $r['total'];
        }
        return ['rows' => $rows, 'total' => $total, 'confirmed' => $confirmed, 'pending' => $pending];
    }

    public function banks(): array
    {
        return $this->db->select('SELECT id, bank_name, currency FROM bank_accounts ORDER BY sort_order, bank_name');
    }
}
