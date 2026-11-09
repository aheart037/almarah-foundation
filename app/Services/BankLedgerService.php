<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

/**
 * Per-bank ledgers (read-only). Groups donations by the bank account they were paid into; donations paid through
 * a payment gateway have no bank account and form one extra "Online payment gateway" ledger (id 0).
 * "Confirmed" uses the same definition as DonationLedgerService so totals always agree with the main ledger.
 */
final class BankLedgerService
{
    public const MODES = [
        'bank_transfer' => 'Bank transfer / deposit', 'online_transfer' => 'Online / mobile banking',
        'cash' => 'Cash', 'cheque' => 'Cheque', 'other' => 'Other',
    ];

    public function __construct(private Database $db, private DonationLedgerService $ledger)
    {
    }

    public static function modeLabel(string $source, ?string $gatewayCode): string
    {
        if ($gatewayCode !== null && $gatewayCode !== '') {
            return 'Online payment (' . $gatewayCode . ')';
        }
        return self::MODES[$source] ?? ucfirst(str_replace('_', ' ', $source));
    }

    /** One row per bank account + currency, plus the gateway ledger, with confirmed/pending/rejected counts and totals. */
    public function summary(array $f): array
    {
        [$where, $params] = $this->dateWhere($f);
        $c = DonationLedgerService::CONFIRMED;
        $rows = $this->db->select(
            "SELECT COALESCE(d.bank_account_id, 0) AS bank_id, COALESCE(b.bank_name, 'Online payment gateway') AS bank_name,
                    b.account_title, b.account_number, d.currency,
                    SUM(CASE WHEN {$c} THEN 1 ELSE 0 END) AS confirmed_count,
                    COALESCE(SUM(CASE WHEN {$c} THEN d.amount_minor ELSE 0 END), 0) AS confirmed_minor,
                    SUM(CASE WHEN NOT {$c} AND d.verification_status = 'submitted' THEN 1 ELSE 0 END) AS pending_count,
                    COALESCE(SUM(CASE WHEN NOT {$c} AND d.verification_status = 'submitted' THEN d.amount_minor ELSE 0 END), 0) AS pending_minor,
                    SUM(CASE WHEN d.verification_status = 'rejected' THEN 1 ELSE 0 END) AS rejected_count,
                    MAX(CASE WHEN {$c} THEN COALESCE(d.transaction_at, d.created_at) END) AS last_confirmed_at
             FROM donations d LEFT JOIN bank_accounts b ON b.id = d.bank_account_id
             WHERE {$where} AND (d.bank_account_id IS NOT NULL OR d.gateway_id IS NOT NULL)
             GROUP BY bank_id, bank_name, b.account_title, b.account_number, d.currency
             ORDER BY bank_id = 0, bank_name, d.currency",
            $params
        );
        // Confirmed donations by mode, per bank
        $modes = [];
        foreach ($this->db->select(
            "SELECT COALESCE(d.bank_account_id, 0) AS bank_id, d.currency, d.source, g.code AS gateway_code, COUNT(*) AS n
             FROM donations d LEFT JOIN gateways g ON g.id = d.gateway_id
             WHERE {$where} AND {$c} GROUP BY bank_id, d.currency, d.source, g.code",
            $params
        ) as $m) {
            $modes[$m['bank_id'] . '|' . $m['currency']][self::modeLabel((string) $m['source'], $m['gateway_code'])] = (int) $m['n'];
        }
        foreach ($rows as &$r) {
            $r['modes'] = $modes[$r['bank_id'] . '|' . $r['currency']] ?? [];
        }
        return $rows;
    }

    /** Transactions of one ledger ($bankId 0 = gateway). @return array{rows:list<array>,total:int,confirmed:array<string,array{n:int,minor:int}>} */
    public function transactions(int $bankId, array $f, int $page, int $perPage): array
    {
        [$where, $params, $join] = $this->detailWhere($bankId, $f);
        $offset = max(0, ($page - 1) * $perPage);
        $select = "d.id, d.public_reference, d.bank_reference, d.amount_minor, d.currency, d.source, d.status, d.verified_at, d.anonymous,
                   COALESCE(d.transaction_at, d.created_at) AS txn_at, d.created_at AS reported_at,
                   dn.name AS donor_name, dn.email AS donor_email, dn.phone AS donor_phone, g.code AS gateway_code,
                   b.bank_name, vu.email AS verified_by_email,
                   (SELECT pt.provider_transaction_id FROM payment_transactions pt WHERE pt.donation_id = d.id AND pt.provider_transaction_id IS NOT NULL ORDER BY pt.id DESC LIMIT 1) AS provider_txn,
                   " . $this->ledger->statusExpr() . " AS ledger_status";
        $from = "FROM donations d LEFT JOIN donors dn ON dn.id = d.donor_id LEFT JOIN bank_accounts b ON b.id = d.bank_account_id
                 LEFT JOIN gateways g ON g.id = d.gateway_id LEFT JOIN users vu ON vu.id = d.verified_by";
        $total = $this->db->int("SELECT COUNT(*) {$from} WHERE {$where}", $params);
        $limit = $perPage > 0 ? " LIMIT {$perPage} OFFSET {$offset}" : '';
        $rows = $this->db->select("SELECT {$select} {$from} WHERE {$where} ORDER BY COALESCE(d.transaction_at, d.created_at) DESC, d.id DESC{$limit}", $params);
        $confirmed = [];
        foreach ($this->db->select(
            "SELECT d.currency, COUNT(*) AS n, COALESCE(SUM(d.amount_minor),0) AS minor {$from} WHERE {$where} AND " . DonationLedgerService::CONFIRMED . ' GROUP BY d.currency',
            $params
        ) as $r) {
            $confirmed[(string) $r['currency']] = ['n' => (int) $r['n'], 'minor' => (int) $r['minor']];
        }
        return ['rows' => $rows, 'total' => $total, 'confirmed' => $confirmed];
    }

    public function bank(int $bankId): ?array
    {
        return $bankId === 0
            ? ['id' => 0, 'bank_name' => 'Online payment gateway', 'account_title' => null, 'account_number' => null, 'currency' => null]
            : $this->db->selectOne('SELECT id, bank_name, account_title, account_number, iban, currency FROM bank_accounts WHERE id = :i', ['i' => $bankId]);
    }

    private function dateWhere(array $f): array
    {
        $w = ['1 = 1'];
        $p = [];
        foreach (['date_from' => ['>=', ' 00:00:00'], 'date_to' => ['<=', ' 23:59:59']] as $k => [$op, $t]) {
            $v = (string) ($f[$k] ?? '');
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) {
                $w[] = "COALESCE(d.transaction_at, d.created_at) {$op} :{$k}";
                $p[$k] = $v . $t;
            }
        }
        return [implode(' AND ', $w), $p];
    }

    private function detailWhere(int $bankId, array $f): array
    {
        [$where, $params] = $this->dateWhere($f);
        $where .= $bankId === 0 ? ' AND d.bank_account_id IS NULL AND d.gateway_id IS NOT NULL' : ' AND d.bank_account_id = :bank';
        if ($bankId > 0) {
            $params['bank'] = $bankId;
        }
        $status = (string) ($f['status'] ?? '');
        if (in_array($status, DonationLedgerService::LEDGER_STATUSES, true)) {
            $where .= ' AND (' . $this->ledger->statusExpr() . ') = :lstatus';
            $params['lstatus'] = $status;
        }
        $mode = (string) ($f['mode'] ?? '');
        if (isset(self::MODES[$mode])) {
            $where .= ' AND d.source = :mode';
            $params['mode'] = $mode;
        }
        $cur = strtoupper((string) ($f['currency'] ?? ''));
        if (preg_match('/^[A-Z]{3}$/', $cur)) {
            $where .= ' AND d.currency = :cur';
            $params['cur'] = $cur;
        }
        $q = trim((string) ($f['q'] ?? ''));
        if ($q !== '') {
            $where .= ' AND (d.public_reference LIKE :q1 OR dn.name LIKE :q2 OR dn.email LIKE :q3 OR dn.phone LIKE :q4 OR d.bank_reference LIKE :q5)';
            foreach (['q1', 'q2', 'q3', 'q4', 'q5'] as $k) {
                $params[$k] = '%' . addcslashes($q, '%_\\') . '%';
            }
        }
        return [$where, $params, ''];
    }
}
