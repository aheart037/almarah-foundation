<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use Throwable;

/**
 * Permanent deletion of junk records: test / spam / abandoned donations and in-kind submissions.
 *
 * Money safety rules (enforced here, in the database transaction, not just in the UI):
 *   - A donation that was ever confirmed (completed / refunded / verified / receipt issued), or that has
 *     refunds, financial adjustments, reconciliation entries or an issued certificate, is a financial
 *     record and is NEVER deleted. The schema also protects those rows with ON DELETE RESTRICT.
 *   - Online payments that may still be in flight (processing, or pending and under an hour old) are
 *     blocked, so a late gateway callback can never point at a deleted row.
 *   - An in-kind donation that has been verified (a certificate has been issued) is never deleted.
 *   - Every delete needs a written reason (5-500 characters). It is saved with a snapshot of what was removed in
 *     the `deleted_records` table (shown on Admin > Deleted records) and in the audit record.
 *
 * Form entries (contact, newsletter, volunteers, careers, fundraising) are handled by InboxController
 * because the inbox is table-driven; it uses {@see self::deleteFile()} for attachments.
 */
final class RecordDeletionService
{
    /** Donation statuses that can be deleted (no money was ever confirmed). */
    public const DELETABLE_DONATION_STATUSES = ['pending', 'failed', 'cancelled', 'abandoned'];
    /** In-kind statuses that can be deleted (verified ones have an issued certificate). */
    public const DELETABLE_INKIND_STATUSES = ['pending', 'rejected'];
    /** Most rows one bulk request may touch. */
    public const BULK_LIMIT = 200;

    public function __construct(
        private Database $db,
        private UploadService $uploads,
        private AuditService $audit
    ) {
    }

    // ------------------------------------------------------------------ reason + deleted-records log

    public const REASON_MIN = 5;
    public const REASON_MAX = 500;

    /** @throws \InvalidArgumentException when the reason is missing or out of range */
    public static function cleanReason(mixed $raw): string
    {
        $reason = is_scalar($raw) ? trim((string) $raw) : '';
        $len = mb_strlen($reason);
        if ($len < self::REASON_MIN) {
            throw new \InvalidArgumentException('Enter the reason for deleting (at least ' . self::REASON_MIN . ' characters).');
        }
        if ($len > self::REASON_MAX) {
            throw new \InvalidArgumentException('The reason is too long (' . self::REASON_MAX . ' characters at most).');
        }
        return $reason;
    }

    /**
     * Record one deletion in `deleted_records`. Call inside the delete transaction so the row is removed and
     * logged together, or neither happens.
     *
     * @param array<string,mixed> $snapshot what was removed (kept small; no message bodies)
     */
    public function logDeleted(string $type, int $recordId, ?string $reference, string $summary, string $reason, int $actor, array $snapshot = []): void
    {
        $this->db->insert('deleted_records', [
            'record_type' => $type,
            'record_id'   => $recordId,
            'reference'   => $reference !== null ? mb_substr($reference, 0, 80) : null,
            'summary'     => mb_substr($summary, 0, 500),
            'snapshot'    => $snapshot === [] ? null : json_encode($snapshot, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'reason'      => $reason,
            'deleted_by'  => $actor > 0 ? $actor : null,
        ]);
    }

    // ------------------------------------------------------------------ donations

    /**
     * Cheap, row-only check (no queries). Used by the list pages to disable checkboxes and by
     * {@see donationBlocker()} as its first step.
     *
     * @param array<string,mixed> $d a donations row
     */
    public static function quickDonationBlocker(array $d): ?string
    {
        $status = (string) ($d['status'] ?? '');
        if (in_array($status, ['completed', 'refunded'], true)) {
            return 'Confirmed or refunded donations are financial records and cannot be deleted.';
        }
        if ($status === 'processing') {
            return 'The payment is still processing. Reconcile it first, then delete it if it failed.';
        }
        if (!in_array($status, self::DELETABLE_DONATION_STATUSES, true)) {
            return 'This donation status cannot be deleted.';
        }
        if ((string) ($d['verification_status'] ?? '') === 'verified') {
            return 'Verified donations are financial records and cannot be deleted.';
        }
        if (trim((string) ($d['receipt_number'] ?? '')) !== '') {
            return 'A receipt number was issued for this donation, so it cannot be deleted.';
        }
        if (!empty($d['gateway_id']) && $status === 'pending') {
            $created = strtotime((string) ($d['created_at'] ?? '') . ' UTC');
            if ($created !== false && $created > time() - 3600) {
                return 'This online payment is less than an hour old and may still be completing. Try again later.';
            }
        }
        return null;
    }

    /**
     * Full check including related records.
     *
     * @param array<string,mixed> $d a donations row
     * @return string|null reason it must not be deleted, or null when deletion is allowed
     */
    public function donationBlocker(array $d): ?string
    {
        $quick = self::quickDonationBlocker($d);
        if ($quick !== null) {
            return $quick;
        }
        $id = (int) $d['id'];
        $checks = [
            'SELECT COUNT(*) FROM payment_refunds WHERE donation_id = :id'                 => 'It has refund records.',
            'SELECT COUNT(*) FROM financial_adjustments WHERE donation_id = :id'          => 'It has financial adjustments.',
            'SELECT COUNT(*) FROM reconciliation_items WHERE donation_id = :id'           => 'It appears in a reconciliation run.',
            "SELECT COUNT(*) FROM donation_certificates WHERE donation_id = :id AND status = 'generated'" => 'A certificate was issued for it.',
        ];
        foreach ($checks as $sql => $why) {
            if ($this->db->int($sql, ['id' => $id]) > 0) {
                return $why . ' Financial records cannot be deleted.';
            }
        }
        return null;
    }

    /**
     * @return array{ok:bool,message:string,reference?:string}
     */
    public function deleteDonation(int $id, int $actor, string $reason): array
    {
        try {
            $reason = self::cleanReason($reason);
        } catch (\InvalidArgumentException $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
        $proof = null;
        try {
            $result = $this->db->transaction(function () use ($id, $actor, $reason, &$proof): array {
                $d = $this->db->selectOne(
                    'SELECT d.*, dn.name AS donor_name, dn.email AS donor_email
                       FROM donations d LEFT JOIN donors dn ON dn.id = d.donor_id
                      WHERE d.id = :id' . $this->lock(),
                    ['id' => $id]
                );
                if ($d === null) {
                    return ['ok' => false, 'message' => 'Donation not found (it may already be deleted).'];
                }
                $why = $this->donationBlocker($d);
                if ($why !== null) {
                    return ['ok' => false, 'message' => $why, 'reference' => (string) $d['public_reference']];
                }

                $transactions = $this->db->int('SELECT COUNT(*) FROM payment_transactions WHERE donation_id = :id', ['id' => $id]);
                // Children first: the schema deliberately restricts deleting parents that still have history.
                $this->db->run(
                    'DELETE FROM payment_callbacks WHERE payment_transaction_id IN (SELECT id FROM payment_transactions WHERE donation_id = :id)',
                    ['id' => $id]
                );
                $this->db->delete('payment_transactions', 'donation_id = :id', ['id' => $id]);
                $this->db->delete('donation_review_events', 'donation_id = :id', ['id' => $id]);
                $this->db->delete('donation_certificates', 'donation_id = :id', ['id' => $id]);
                $this->db->delete('donations', 'id = :id', ['id' => $id]);

                $this->logDeleted('donation', $id, (string) $d['public_reference'],
                    trim((string) $d['currency'] . ' ' . number_format(((int) $d['amount_minor']) / 100, 2)) . ' · ' . ((string) ($d['donor_name'] ?? '') !== '' ? $d['donor_name'] : 'no donor') . ' · ' . $d['status'],
                    $reason, $actor, ['status' => $d['status'], 'verification_status' => $d['verification_status'] ?? null, 'source' => $d['source'] ?? null, 'bank_reference' => $d['bank_reference'] ?? null, 'payment_attempts_removed' => $transactions, 'created_at' => $d['created_at']]);
                $this->audit->logRequired('donation.deleted', 'donation', $id, [
                    'reason'              => $reason,
                    'reference'           => $d['public_reference'],
                    'status'              => $d['status'],
                    'verification_status' => $d['verification_status'] ?? null,
                    'amount_minor'        => (int) $d['amount_minor'],
                    'currency'            => $d['currency'],
                    'donor_name'          => $d['donor_name'] ?? null,
                    'donor_email'         => $d['donor_email'] ?? null,
                    'source'              => $d['source'] ?? null,
                    'gateway_id'          => $d['gateway_id'],
                    'bank_reference'      => $d['bank_reference'] ?? null,
                    'payment_attempts_removed' => $transactions,
                    'created_at'          => $d['created_at'],
                ], $actor);

                $proof = $d['payment_proof_path'] ?? null;
                return ['ok' => true, 'message' => 'Donation ' . $d['public_reference'] . ' deleted.', 'reference' => (string) $d['public_reference']];
            });
        } catch (Throwable $e) {
            return ['ok' => false, 'message' => 'Could not delete this donation: ' . $this->safeMessage($e)];
        }
        if ($result['ok'] && is_string($proof) && $proof !== '') {
            $this->deleteFile($proof);
        }
        return $result;
    }

    // ------------------------------------------------------------------ in-kind

    /** @param array<string,mixed> $row an inkind_donations row */
    public static function inkindBlocker(array $row): ?string
    {
        if (!in_array((string) ($row['status'] ?? ''), self::DELETABLE_INKIND_STATUSES, true)) {
            return 'Verified donations have an issued certificate and cannot be deleted.';
        }
        return null;
    }

    /** @return array{ok:bool,message:string,reference?:string} */
    public function deleteInKind(int $id, int $actor, string $reason): array
    {
        try {
            $reason = self::cleanReason($reason);
        } catch (\InvalidArgumentException $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
        $photos = [];
        try {
            $result = $this->db->transaction(function () use ($id, $actor, $reason, &$photos): array {
                $row = $this->db->selectOne('SELECT * FROM inkind_donations WHERE id = :id' . $this->lock(), ['id' => $id]);
                if ($row === null) {
                    return ['ok' => false, 'message' => 'In-kind donation not found (it may already be deleted).'];
                }
                $why = self::inkindBlocker($row);
                if ($why === null && $this->db->int("SELECT COUNT(*) FROM inkind_certificates WHERE inkind_id = :id AND status = 'generated'", ['id' => $id]) > 0) {
                    $why = 'A certificate was issued for it, so it cannot be deleted.';
                }
                if ($why !== null) {
                    return ['ok' => false, 'message' => $why, 'reference' => (string) $row['public_reference']];
                }

                $items = $this->db->int('SELECT COUNT(*) FROM inkind_donation_items WHERE inkind_id = :id', ['id' => $id]);
                $this->db->delete('inkind_certificates', 'inkind_id = :id', ['id' => $id]);
                $this->db->delete('inkind_events', 'inkind_id = :id', ['id' => $id]);
                $this->db->delete('inkind_donation_items', 'inkind_id = :id', ['id' => $id]);
                $this->db->delete('inkind_donations', 'id = :id', ['id' => $id]);

                $this->logDeleted('inkind', $id, (string) $row['public_reference'],
                    ((string) ($row['donor_name'] ?? '') !== '' ? $row['donor_name'] : 'no donor') . ' · ' . $items . ' item line(s) · ' . $row['status'],
                    $reason, $actor, ['status' => $row['status'], 'items' => $items, 'created_at' => $row['created_at']]);
                $this->audit->logRequired('inkind.deleted', 'inkind_donation', $id, [
                    'reason'      => $reason,
                    'reference'   => $row['public_reference'],
                    'status'      => $row['status'],
                    'donor_name'  => $row['donor_name'] ?? null,
                    'donor_email' => $row['donor_email'] ?? null,
                    'items'       => $items,
                    'created_at'  => $row['created_at'],
                ], $actor);

                $decoded = json_decode((string) ($row['photos_json'] ?? ''), true);
                $photos = is_array($decoded) ? array_values(array_filter($decoded, 'is_string')) : [];
                return ['ok' => true, 'message' => 'In-kind donation ' . $row['public_reference'] . ' deleted.', 'reference' => (string) $row['public_reference']];
            });
        } catch (Throwable $e) {
            return ['ok' => false, 'message' => 'Could not delete this donation: ' . $this->safeMessage($e)];
        }
        if ($result['ok']) {
            foreach ($photos as $path) {
                $this->deleteFile($path);
            }
        }
        return $result;
    }

    // ------------------------------------------------------------------ bulk + files

    /**
     * Run one of the delete methods over many ids.
     *
     * @param list<int> $ids
     * @param callable(int,int):array{ok:bool,message:string,reference?:string} $one
     * @return array{deleted:int,skipped:list<string>}
     */
    public function bulk(array $ids, int $actor, callable $one): array
    {
        $deleted = 0;
        $skipped = [];
        foreach (array_slice(self::cleanIds($ids), 0, self::BULK_LIMIT) as $id) {
            $r = $one($id, $actor);
            if ($r['ok']) {
                $deleted++;
            } else {
                $skipped[] = ($r['reference'] ?? ('#' . $id)) . ': ' . $r['message'];
            }
        }
        return ['deleted' => $deleted, 'skipped' => $skipped];
    }

    /** @return list<int> positive, unique integers */
    public static function cleanIds(mixed $raw): array
    {
        if (!is_array($raw)) {
            return [];
        }
        $out = [];
        foreach ($raw as $v) {
            if (is_scalar($v) && ctype_digit((string) $v) && (int) $v > 0) {
                $out[(int) $v] = (int) $v;
            }
        }
        return array_values($out);
    }

    /** Remove a private upload (bank slip, goods photo, CV) if it exists. Never throws. */
    public function deleteFile(?string $relativePath): void
    {
        try {
            $abs = $this->uploads->privatePath($relativePath);
            if ($abs !== null && is_file($abs)) {
                @unlink($abs);
            }
        } catch (Throwable) {
            // The database row is already gone; a leftover file is harmless and never web-accessible.
        }
    }

    // ------------------------------------------------------------------ internals

    /** Row lock on MySQL; SQLite (used only by automated tests) has no FOR UPDATE. */
    private function lock(): string
    {
        return $this->db->pdo()->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'mysql' ? ' FOR UPDATE' : '';
    }

    private function safeMessage(Throwable $e): string
    {
        // Foreign-key failures mean something unexpected still references the row: refuse, do not force.
        if ($e instanceof \PDOException && in_array((string) $e->getCode(), ['23000', '23503'], true)) {
            return 'other records still depend on it. Nothing was deleted.';
        }
        return 'an unexpected error occurred. Nothing was deleted.';
    }
}
