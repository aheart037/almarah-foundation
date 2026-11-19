<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Str;
use App\Repositories\DonorRepository;
use InvalidArgumentException;
use Throwable;

/**
 * In-kind (goods) donations: public submission, finance review (pending -> verified / rejected) and history.
 * Goods never touch the money ledger; certificates are handled by InKindCertificateService.
 */
final class InKindDonationService
{
    public const CATEGORIES = [
        'clothing'   => 'Clothing & jackets',
        'footwear'   => 'Shoes & footwear',
        'food'       => 'Food & groceries',
        'bedding'    => 'Blankets & bedding',
        'education'  => 'Books & school supplies',
        'toys'       => 'Toys & games',
        'hygiene'    => 'Hygiene & personal care',
        'medical'    => 'Medical supplies',
        'household'  => 'Household items',
        'electronics' => 'Electronics',
        'other'      => 'Other',
    ];

    public const STATUSES = ['pending' => 'Pending', 'verified' => 'Verified', 'rejected' => 'Rejected'];
    public const MAX_ITEMS = 15;
    public const MAX_PHOTOS = 3;

    public function __construct(
        private Database $db,
        private DonorRepository $donors,
        private UploadService $uploads,
        private AuditService $audit
    ) {
    }

    // ------------------------------------------------------------------ submission

    /**
     * @param array<string,mixed> $in   posted form fields
     * @param array<string,array<string,mixed>|null> $photos uploaded files keyed photo_1..photo_3
     * @return array{id:int,reference:string}
     */
    public function submit(array $in, array $photos = []): array
    {
        $name = $this->value($in, 'donor_name', 150);
        $email = $this->value($in, 'donor_email', 191);
        $phone = $this->value($in, 'donor_phone', 40);
        if (!$name || !$email || !$phone || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Full name, a valid email and a mobile number are required.');
        }
        $items = $this->readItems($in);

        $method = (string) ($in['handover_method'] ?? 'drop_off');
        if (!in_array($method, ['drop_off', 'pickup'], true)) {
            throw new InvalidArgumentException('Please choose how the goods will reach us.');
        }
        $address = $this->value($in, 'donor_address', 500);
        $pickup = $method === 'pickup' ? ($this->value($in, 'pickup_address', 500) ?? $address) : null;
        if ($method === 'pickup' && !$pickup) {
            throw new InvalidArgumentException('Please enter the address where we should collect the goods.');
        }
        $date = $this->value($in, 'handover_date', 10);
        if ($date !== null) {
            $ts = preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ? strtotime($date . ' 00:00:00 UTC') : false;
            if ($ts === false || $ts < time() - 90 * 86400 || $ts > time() + 366 * 86400) {
                throw new InvalidArgumentException('Please enter a valid hand-over date.');
            }
        }

        // Acknowledgment recipient + referrer: same rules as bank-transfer donations.
        $self = (string) ($in['acknowledgment_self'] ?? '1') === '1';
        $recipient = $self ? null : $this->value($in, 'recipient_name', 160);
        if (!$self && !$recipient) {
            throw new InvalidArgumentException('Recipient full name is required.');
        }
        $recipientEmail = $self ? null : $this->value($in, 'recipient_email', 191);
        if ($recipientEmail && !filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Recipient email is invalid.');
        }
        $referral = (string) ($in['referral_enabled'] ?? '0') === '1';
        $referrer = $referral ? $this->value($in, 'referrer_name', 160) : null;
        if ($referral && !$referrer) {
            throw new InvalidArgumentException('Referrer name is required.');
        }

        $stored = [];
        try {
            foreach (array_slice($photos, 0, self::MAX_PHOTOS) as $file) {
                if (is_array($file) && (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                    $stored[] = $this->uploads->storePrivateImage($file, 'inkind-photos', 'photo');
                }
            }
            $result = $this->db->transaction(function () use ($in, $name, $email, $phone, $address, $method, $pickup, $date, $self, $recipient, $recipientEmail, $referral, $referrer, $items, $stored): array {
                $reference = Str::publicReference('IKD');
                $id = $this->db->insert('inkind_donations', [
                    'public_reference' => $reference,
                    'donor_id' => $this->donors->findOrCreate($name, strtolower($email), $phone),
                    'donor_name' => $name, 'donor_email' => strtolower($email), 'donor_phone' => $phone,
                    'donor_city' => $this->value($in, 'donor_city', 100), 'donor_address' => $address,
                    'handover_method' => $method, 'handover_date' => $date, 'pickup_address' => $pickup,
                    'description' => $this->value($in, 'description', 2000),
                    'photos_json' => $stored ? json_encode($stored, JSON_UNESCAPED_SLASHES) : null,
                    'acknowledgment_self' => (int) $self, 'recipient_name' => $recipient,
                    'recipient_phone' => $self ? null : $this->value($in, 'recipient_phone', 40),
                    'recipient_email' => $recipientEmail,
                    'recipient_address' => $self ? null : $this->value($in, 'recipient_address', 500),
                    'acknowledgment_instructions' => $this->value($in, 'acknowledgment_instructions', 1000),
                    'referral_enabled' => (int) $referral, 'referrer_name' => $referrer,
                    'referrer_phone' => $referral ? $this->value($in, 'referrer_phone', 40) : null,
                    'referral_notes' => $referral ? $this->value($in, 'referral_notes', 1000) : null,
                    'status' => 'pending',
                ]);
                foreach ($items as $i => $item) {
                    $this->db->insert('inkind_donation_items', $item + ['inkind_id' => $id, 'sort_order' => $i]);
                }
                $this->event($id, 'submitted', null, 'Goods donation submitted for verification.');
                return ['id' => $id, 'reference' => $reference];
            });
        } catch (Throwable $e) {
            foreach ($stored as $rel) {
                if ($path = $this->uploads->privatePath($rel)) {
                    @unlink($path);
                }
            }
            throw $e;
        }
        $this->audit->log('inkind.submitted', 'inkind_donation', $result['id'], ['items' => count($items)], null);
        return $result;
    }

    /** @return list<array{category:string,item_name:string,quantity:int,unit:?string,details:?string}> */
    private function readItems(array $in): array
    {
        $names = (array) ($in['item_name'] ?? []);
        $items = [];
        foreach ($names as $i => $rawName) {
            $name = is_scalar($rawName) ? trim((string) $rawName) : '';
            $qtyRaw = is_array($in['item_qty'] ?? null) ? ($in['item_qty'][$i] ?? '') : '';
            $qtyRaw = is_scalar($qtyRaw) ? trim((string) $qtyRaw) : '';
            if ($name === '' && $qtyRaw === '') {
                continue; // blank row
            }
            if ($name === '' || mb_strlen($name) > 150) {
                throw new InvalidArgumentException('Please describe each item (for example "Winter jackets") in 150 characters or fewer.');
            }
            if (!preg_match('/^\d{1,7}$/', $qtyRaw) || (int) $qtyRaw < 1) {
                throw new InvalidArgumentException('Please enter a whole-number quantity of at least 1 for "' . mb_substr($name, 0, 40) . '".');
            }
            $cat = is_array($in['item_category'] ?? null) ? (string) ($in['item_category'][$i] ?? 'other') : 'other';
            $unit = is_array($in['item_unit'] ?? null) && is_scalar($in['item_unit'][$i] ?? null) ? trim((string) $in['item_unit'][$i]) : '';
            $details = is_array($in['item_details'] ?? null) && is_scalar($in['item_details'][$i] ?? null) ? trim((string) $in['item_details'][$i]) : '';
            if (mb_strlen($unit) > 30 || mb_strlen($details) > 500) {
                throw new InvalidArgumentException('An item unit or description is too long.');
            }
            $items[] = [
                'category' => isset(self::CATEGORIES[$cat]) ? $cat : 'other',
                'item_name' => $name, 'quantity' => (int) $qtyRaw,
                'unit' => $unit !== '' ? $unit : null, 'details' => $details !== '' ? $details : null,
            ];
        }
        if ($items === []) {
            throw new InvalidArgumentException('Please add at least one item with its quantity.');
        }
        if (count($items) > self::MAX_ITEMS) {
            throw new InvalidArgumentException('You can list up to ' . self::MAX_ITEMS . ' items. Describe the rest in the notes.');
        }
        return $items;
    }

    private function value(array $in, string $key, int $limit): ?string
    {
        $raw = $in[$key] ?? '';
        if (!is_scalar($raw)) {
            throw new InvalidArgumentException($key . ' must be plain text.');
        }
        $value = trim((string) $raw);
        if (mb_strlen($value) > $limit) {
            throw new InvalidArgumentException($key . ' is too long.');
        }
        return $value === '' ? null : $value;
    }

    // ------------------------------------------------------------------ reading

    public function find(int $id): ?array
    {
        return $this->db->selectOne(
            "SELECT k.*, CONCAT_WS(' ', u.first_name, u.last_name) AS reviewer_name FROM inkind_donations k
             LEFT JOIN users u ON u.id = k.reviewed_by WHERE k.id = :id",
            ['id' => $id]
        );
    }

    public function findByReference(string $reference): ?array
    {
        return $this->db->selectOne('SELECT * FROM inkind_donations WHERE public_reference = :r', ['r' => $reference]);
    }

    /** @return list<array<string,mixed>> */
    public function items(int $id): array
    {
        return $this->db->select('SELECT * FROM inkind_donation_items WHERE inkind_id = :id ORDER BY sort_order, id', ['id' => $id]);
    }

    /** @return list<array<string,mixed>> */
    public function events(int $id): array
    {
        return $this->db->select(
            "SELECT e.*, CONCAT_WS(' ', u.first_name, u.last_name) AS actor_name FROM inkind_events e
             LEFT JOIN users u ON u.id = e.actor_user_id WHERE e.inkind_id = :id ORDER BY e.id DESC",
            ['id' => $id]
        );
    }

    /** @return list<string> relative private paths of the uploaded photos */
    public function photos(array $row): array
    {
        $list = json_decode((string) ($row['photos_json'] ?? ''), true);
        return is_array($list) ? array_values(array_filter($list, 'is_string')) : [];
    }

    public function photoPath(array $row, int $n): ?string
    {
        $photos = $this->photos($row);
        return isset($photos[$n]) ? $this->uploads->privatePath($photos[$n]) : null;
    }

    /** Name printed on the certificate: the acknowledgment recipient when the donor gave on someone's behalf. */
    public static function certificateName(array $row): string
    {
        if ((int) ($row['acknowledgment_self'] ?? 1) === 0 && trim((string) ($row['recipient_name'] ?? '')) !== '') {
            return trim((string) $row['recipient_name']);
        }
        return trim((string) ($row['donor_name'] ?? '')) ?: 'Donor';
    }

    /**
     * @param array{q?:string,status?:string,date_from?:string,date_to?:string} $f
     * @return array{rows:list<array<string,mixed>>,total:int,page:int,lastPage:int,counts:array<string,int>}
     */
    public function search(array $f, int $page = 1, int $perPage = 25): array
    {
        $where = ['1=1'];
        $p = [];
        $q = trim((string) ($f['q'] ?? ''));
        if ($q !== '') {
            $where[] = '(k.public_reference LIKE :q1 OR k.donor_name LIKE :q2 OR k.donor_email LIKE :q3 OR k.donor_phone LIKE :q4
                         OR EXISTS (SELECT 1 FROM inkind_donation_items i WHERE i.inkind_id = k.id AND i.item_name LIKE :q5))';
            foreach (['q1', 'q2', 'q3', 'q4', 'q5'] as $k) {
                $p[$k] = '%' . addcslashes($q, '%_\\') . '%';
            }
        }
        $status = (string) ($f['status'] ?? '');
        if (isset(self::STATUSES[$status])) {
            $where[] = 'k.status = :status';
            $p['status'] = $status;
        }
        foreach (['date_from' => '>=', 'date_to' => '<='] as $key => $op) {
            $d = (string) ($f[$key] ?? '');
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) {
                $where[] = "DATE(k.created_at) {$op} :{$key}";
                $p[$key] = $d;
            }
        }
        $w = implode(' AND ', $where);
        $total = $this->db->int("SELECT COUNT(*) FROM inkind_donations k WHERE {$w}", $p);
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, $page), $lastPage);
        $offset = ($page - 1) * $perPage;
        $rows = $this->db->select(
            "SELECT k.*,
                (SELECT COUNT(*) FROM inkind_donation_items i WHERE i.inkind_id = k.id) AS items_count,
                (SELECT COALESCE(SUM(i.quantity), 0) FROM inkind_donation_items i WHERE i.inkind_id = k.id) AS qty_total,
                (SELECT GROUP_CONCAT(CONCAT(i.quantity, ' x ', i.item_name) ORDER BY i.sort_order, i.id SEPARATOR ', ') FROM inkind_donation_items i WHERE i.inkind_id = k.id) AS summary,
                (SELECT c.certificate_number FROM inkind_certificates c WHERE c.inkind_id = k.id AND c.status = 'generated' ORDER BY c.version DESC LIMIT 1) AS certificate_number,
                (SELECT c.email_status FROM inkind_certificates c WHERE c.inkind_id = k.id AND c.status = 'generated' ORDER BY c.version DESC LIMIT 1) AS certificate_email
             FROM inkind_donations k WHERE {$w} ORDER BY k.id DESC LIMIT {$perPage} OFFSET {$offset}",
            $p
        );
        return ['rows' => $rows, 'total' => $total, 'page' => $page, 'lastPage' => $lastPage, 'counts' => $this->statusCounts()];
    }

    /** @return array<string,int> */
    public function statusCounts(): array
    {
        $out = ['pending' => 0, 'verified' => 0, 'rejected' => 0];
        foreach ($this->db->select('SELECT status, COUNT(*) AS c FROM inkind_donations GROUP BY status') as $r) {
            $out[(string) $r['status']] = (int) $r['c'];
        }
        return $out;
    }

    // ------------------------------------------------------------------ review workflow

    /**
     * @param 'approve'|'reject'|'reopen' $decision
     * @throws InvalidArgumentException when the transition is not allowed
     */
    public function decide(int $id, int $actor, string $decision, string $note = ''): void
    {
        $note = trim($note);
        if (mb_strlen($note) > 1000) {
            throw new InvalidArgumentException('The note is too long.');
        }
        [$from, $to, $event] = match ($decision) {
            'approve' => ['pending', 'verified', 'verified'],
            'reject'  => ['pending', 'rejected', 'rejected'],
            'reopen'  => ['rejected', 'pending', 'reopened'],
            default   => throw new InvalidArgumentException('Unknown decision.'),
        };
        if ($decision === 'reject' && mb_strlen($note) < 5) {
            throw new InvalidArgumentException('Please give a reason for rejecting this donation (at least 5 characters).');
        }
        $this->db->transaction(function () use ($id, $actor, $from, $to, $event, $note): void {
            $moved = $this->db->run(
                'UPDATE inkind_donations SET status = :to, review_note = :note, reviewed_by = :by, reviewed_at = UTC_TIMESTAMP() WHERE id = :id AND status = :from',
                ['to' => $to, 'note' => $note !== '' ? $note : null, 'by' => $actor, 'id' => $id, 'from' => $from]
            )->rowCount();
            if ($moved !== 1) {
                throw new InvalidArgumentException('This donation is no longer ' . $from . ', so that action is not available. Refresh the page.');
            }
            $this->event($id, $event, $actor, $note !== '' ? $note : null);
        });
        $this->audit->log('inkind.' . $event, 'inkind_donation', $id, ['note' => $note], $actor);
    }

    public function event(int $id, string $action, ?int $actor, ?string $note = null): void
    {
        $this->db->insert('inkind_events', ['inkind_id' => $id, 'action' => $action, 'actor_user_id' => $actor, 'note' => $note !== null ? mb_substr($note, 0, 1000) : null]);
    }
}
