<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Repositories\DonorRepository;
use InvalidArgumentException;
use Throwable;

/**
 * Editing of two kinds of records after they were entered: staff-recorded OFFLINE donations (bank transfer,
 * cash, cheque, Zelle...) and IN-KIND (goods) donations. Both need the matching permission
 * (donations.edit / inkind.edit); the routes enforce that, this class enforces the money-safety rules.
 *
 * Offline donations - what may change depends on how far the donation has got:
 *   - "full"    : never confirmed and not yet verified. Every field may change (amount, bank, reference, dates,
 *                 donor, acknowledgment...). If it was part-way through review, or rejected, the review starts
 *                 again from the first stage (a 'reset' review event, same convention as an amount correction).
 *   - "contact" : verified / completed, receipted, certificate issued, refunded, adjusted or reconciled. Money
 *                 and identity are frozen (use Adjust / refund for those). Only the donor's email and phone and
 *                 the internal note can be corrected, for example so the receipt or WhatsApp message reaches
 *                 the donor.
 *   - blocked   : online (gateway) payments, donor-reported transfers, failed / cancelled / refunded entries.
 *
 * In-kind donations hold no money and a certificate can be re-issued as a new version, so every status can be
 * edited; for a verified one the certificate already issued is NOT changed (re-issue it to reflect the edit).
 *
 * Every save needs a written reason (field `reason`, 5-500 characters; not required when nothing changed). It is stored
 * in the review / history event and the audit record. Every save writes an audit record listing which fields changed (money fields with old and new value; personal
 * data by field name only) in the same transaction as the change.
 */
final class RecordEditService
{
    public const OFFLINE_SOURCES = ['bank_transfer', 'online_transfer', 'cash', 'cheque', 'zelle', 'other'];
    public const CURRENCIES = ['PKR', 'USD', 'GBP', 'EUR', 'AED', 'SAR', 'CAD', 'AUD'];

    public function __construct(
        private Database $db,
        private DonorRepository $donors,
        private UploadService $uploads,
        private AuditService $audit
    ) {
    }

    // ================================================================== offline donations: access rules

    /**
     * Row-only check (no queries); used by the pages to decide whether to show the Edit button.
     *
     * @param array<string,mixed> $d a donations row
     * @return array{mode:string,reason:?string} mode is 'full', 'contact' or 'blocked'
     */
    public static function donationEditability(array $d): array
    {
        if (!empty($d['gateway_id'])) {
            return ['mode' => 'blocked', 'reason' => 'Online payments are confirmed by the payment provider and cannot be edited.'];
        }
        if (empty($d['recorded_by'])) {
            return ['mode' => 'blocked', 'reason' => 'This transfer was reported by the donor, not recorded by staff, so it cannot be edited here.'];
        }
        $status = (string) ($d['status'] ?? '');
        if (in_array($status, ['failed', 'cancelled', 'abandoned', 'processing'], true)) {
            return ['mode' => 'blocked', 'reason' => 'A ' . $status . ' donation cannot be edited.'];
        }
        if ($status === 'refunded') {
            return ['mode' => 'blocked', 'reason' => 'Refunded donations cannot be edited.'];
        }
        $frozen = null;
        if ($status === 'completed') {
            $frozen = 'This donation is confirmed.';
        } elseif (in_array((string) ($d['verification_status'] ?? ''), ['verified', 'legacy'], true)) {
            $frozen = 'This donation is verified.';
        } elseif (trim((string) ($d['receipt_number'] ?? '')) !== '') {
            $frozen = 'A receipt number was issued for this donation.';
        }
        if ($frozen !== null) {
            return ['mode' => 'contact', 'reason' => $frozen . ' The amount, bank and donor identity are locked; only the donor email, phone and internal note can be corrected.'];
        }
        return ['mode' => 'full', 'reason' => null];
    }

    /**
     * Full check including related records.
     *
     * @param array<string,mixed> $d a donations row
     * @return array{mode:string,reason:?string}
     */
    public function donationAccess(array $d): array
    {
        $a = self::donationEditability($d);
        if ($a['mode'] !== 'full') {
            return $a;
        }
        $id = (int) $d['id'];
        $checks = [
            'SELECT COUNT(*) FROM payment_refunds WHERE donation_id = :id'        => 'It has refund records.',
            'SELECT COUNT(*) FROM financial_adjustments WHERE donation_id = :id' => 'It has financial adjustments.',
            'SELECT COUNT(*) FROM reconciliation_items WHERE donation_id = :id'  => 'It appears in a reconciliation run.',
            "SELECT COUNT(*) FROM donation_certificates WHERE donation_id = :id AND status = 'generated'" => 'A certificate was issued for it.',
        ];
        foreach ($checks as $sql => $why) {
            if ($this->db->int($sql, ['id' => $id]) > 0) {
                return ['mode' => 'contact', 'reason' => $why . ' The amount, bank and donor identity are locked; only the donor email, phone and internal note can be corrected.'];
            }
        }
        return $a;
    }

    // ================================================================== offline donations: save

    /**
     * @param array<string,mixed> $in posted form fields
     * @param array<string,mixed>|null $file optional replacement bank / deposit slip
     * @return array{id:int,reference:string,mode:string,changed:list<string>,review_restarted:bool}
     * @throws InvalidArgumentException with a message that is safe to show to staff
     */
    public function updateDonation(int $id, array $in, ?array $file, int $actor): array
    {
        $newProof = null;
        $oldProof = null;
        try {
            $result = $this->db->transaction(function () use ($id, $in, $file, $actor, &$newProof, &$oldProof): array {
                $d = $this->db->selectOne(
                    'SELECT d.*, dn.name AS donor_name, dn.email AS donor_email, dn.phone AS donor_phone
                       FROM donations d LEFT JOIN donors dn ON dn.id = d.donor_id WHERE d.id = :id' . $this->lock(),
                    ['id' => $id]
                );
                if ($d === null) {
                    throw new InvalidArgumentException('Donation not found.');
                }
                $access = $this->donationAccess($d);
                if ($access['mode'] === 'blocked') {
                    throw new InvalidArgumentException((string) $access['reason']);
                }
                return $access['mode'] === 'full'
                    ? $this->saveDonationFull($d, $in, $file, $actor, $newProof, $oldProof)
                    : $this->saveDonationContact($d, $in, $actor);
            });
        } catch (Throwable $e) {
            if ($newProof !== null) {
                $this->deleteFile($newProof);
            }
            throw $e;
        }
        if ($oldProof !== null) {
            $this->deleteFile($oldProof);
        }
        return $result;
    }

    /** @return array{id:int,reference:string,mode:string,changed:list<string>,review_restarted:bool} */
    private function saveDonationFull(array $d, array $in, ?array $file, int $actor, ?string &$newProof, ?string &$oldProof): array
    {
        $id = (int) $d['id'];
        $method = (string) ($in['source'] ?? '');
        if (!in_array($method, self::OFFLINE_SOURCES, true)) {
            throw new InvalidArgumentException('Invalid payment method.');
        }
        $name = $this->text($in, 'donor_name', 150);
        $email = $this->text($in, 'donor_email', 191);
        $phone = $this->text($in, 'donor_phone', 40);
        $anonymous = !empty($in['anonymous']);
        if ($anonymous && !$name) {
            $name = 'Anonymous donor';
        }
        if (!$name || (!$anonymous && (!$email || !$phone)) || ($email && !filter_var($email, FILTER_VALIDATE_EMAIL))) {
            throw new InvalidArgumentException('Full name, valid email and mobile number are required unless the gift is anonymous.');
        }
        $t = $this->readTransfer($in, $method);

        $self = (string) ($in['acknowledgment_self'] ?? '1') === '1';
        $recipient = $self ? null : $this->text($in, 'recipient_name', 160);
        if (!$self && !$recipient) {
            throw new InvalidArgumentException('Recipient full name is required.');
        }
        $recipientEmail = $self ? null : $this->text($in, 'recipient_email', 191);
        if ($recipientEmail && !filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Recipient email is invalid.');
        }
        $referral = (string) ($in['referral_enabled'] ?? '0') === '1';
        $referrer = $referral ? $this->text($in, 'referrer_name', 160) : null;
        if ($referral && !$referrer) {
            throw new InvalidArgumentException('Referrer name is required.');
        }
        $purposeId = (int) ($in['purpose_id'] ?? 0);
        if ($purposeId < 0 || ($purposeId > 0 && !$this->db->selectOne('SELECT id FROM donation_purposes WHERE id = :id', ['id' => $purposeId]))) {
            throw new InvalidArgumentException('Unknown donation purpose.');
        }

        $new = [
            'amount_minor'  => $t['minor'], 'currency' => $t['currency'], 'source' => $method, 'payment_method' => $method,
            'bank_account_id' => $t['bank_id'], 'bank_reference' => $t['reference'],
            'transaction_at' => substr((string) ($d['transaction_at'] ?? ''), 0, 10) === $t['date'] ? $d['transaction_at'] : $t['date'] . ' 12:00:00',
            'deposited_at' => $t['deposited_at'],
            'purpose_id' => $purposeId ?: null,
            'donation_type' => ($in['donation_type'] ?? '') === 'monthly' ? 'monthly' : 'one_time',
            'anonymous' => (int) $anonymous,
            'donor_message' => $this->text($in, 'note', 255),
            'acknowledgment_self' => (int) $self,
            'recipient_name' => $recipient,
            'recipient_phone' => $self ? null : $this->text($in, 'recipient_phone', 40),
            'recipient_email' => $recipientEmail,
            'recipient_address' => $self ? null : $this->text($in, 'recipient_address', 500),
            'acknowledgment_instructions' => $this->text($in, 'acknowledgment_instructions', 1000),
            'referral_enabled' => (int) $referral, 'referrer_name' => $referrer,
            'referrer_phone' => $referral ? $this->text($in, 'referrer_phone', 40) : null,
            'referral_notes' => $referral ? $this->text($in, 'referral_notes', 1000) : null,
        ];

        // Money-and-bank fields are logged with old and new values; everything else by name only.
        $moneyFields = ['amount_minor', 'currency', 'source', 'bank_account_id', 'bank_reference', 'transaction_at', 'deposited_at', 'purpose_id', 'donation_type'];
        $changed = [];
        $diff = [];
        foreach ($new as $col => $val) {
            if ($this->differs($d[$col] ?? null, $val)) {
                $changed[] = $col;
                if (in_array($col, $moneyFields, true)) {
                    $diff[$col] = ['from' => $d[$col] ?? null, 'to' => $val];
                }
            }
        }
        $email = $email !== null ? strtolower($email) : null;
        $update = $new;
        $donorChanged = $email !== null
            ? ($this->differs($d['donor_name'] ?? null, $name) || $this->differs($d['donor_email'] ?? null, $email) || $this->differs($d['donor_phone'] ?? null, $phone))
            : $d['donor_id'] !== null;
        if ($donorChanged) {
            // Re-link this donation only; findOrCreate keeps one donor row per email.
            $update['donor_id'] = $email !== null ? $this->donors->findOrCreate($name, $email, $phone) : null;
            $changed[] = 'donor';
        }
        if ($file !== null && (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $newProof = $this->uploads->storePaymentProof($file);
            $update['payment_proof_path'] = $newProof;
            $oldProof = ($d['payment_proof_path'] ?? null) ?: null;
            $changed[] = 'payment_proof_path';
        }
        $changed = array_values(array_unique($changed));
        if ($changed === []) {
            return ['id' => $id, 'reference' => (string) $d['public_reference'], 'mode' => 'full', 'changed' => [], 'review_restarted' => false];
        }

        $reason = $this->editReason($in);
        $restart = (string) ($d['verification_status'] ?? '') !== 'submitted';
        if ($restart) {
            $update['verification_status'] = 'submitted';
            $update['verified_by'] = null;
            $update['verified_at'] = null;
        }
        $this->db->update('donations', $update, 'id = :id', ['id' => $id]);
        $this->db->insert('donation_review_events', [
            'donation_id' => $id, 'actor_user_id' => $actor, 'stage_key' => 'correction',
            'decision' => $restart ? 'reset' : 'edited',
            'comment' => mb_substr('Edited by staff (' . implode(', ', $changed) . '). Reason: ' . $reason . ($restart ? ' Review restarted from the first stage.' : ''), 0, 1000),
        ]);
        $this->audit->logRequired('donation.edited', 'donation', $id, [
            'mode' => 'full', 'reason' => $reason, 'fields' => $changed, 'money' => $diff, 'review_restarted' => $restart,
            'previous_status' => (string) ($d['verification_status'] ?? ''),
        ], $actor);
        return ['id' => $id, 'reference' => (string) $d['public_reference'], 'mode' => 'full', 'changed' => $changed, 'review_restarted' => $restart];
    }

    /** @return array{id:int,reference:string,mode:string,changed:list<string>,review_restarted:bool} */
    private function saveDonationContact(array $d, array $in, int $actor): array
    {
        $id = (int) $d['id'];
        $email = $this->text($in, 'donor_email', 191);
        $phone = $this->text($in, 'donor_phone', 40);
        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Enter a valid donor email.');
        }
        $note = $this->text($in, 'note', 255);
        $changed = [];
        $update = [];
        if ($d['donor_id'] === null && !$email) {
            if ($phone) {
                throw new InvalidArgumentException('This donation has no donor record. Enter an email address to store the phone number.');
            }
        } else {
            if (!$email) {
                throw new InvalidArgumentException('The donor email cannot be removed from a verified donation.');
            }
            $email = strtolower($email);
            if ($this->differs($d['donor_email'] ?? null, $email) || $this->differs($d['donor_phone'] ?? null, $phone)) {
                $name = trim((string) ($d['donor_name'] ?? '')) ?: 'Anonymous donor';
                $update['donor_id'] = $this->donors->findOrCreate($name, $email, $phone);
                $changed[] = 'donor_contact';
            }
        }
        if ($this->differs($d['donor_message'] ?? null, $note)) {
            $update['donor_message'] = $note;
            $changed[] = 'donor_message';
        }
        if ($changed !== []) {
            $reason = $this->editReason($in);
            $this->db->update('donations', $update, 'id = :id', ['id' => $id]);
            $this->db->insert('donation_review_events', [
                'donation_id' => $id, 'actor_user_id' => $actor, 'stage_key' => 'correction', 'decision' => 'edited',
                'comment' => mb_substr('Contact details corrected (' . implode(', ', $changed) . '). Reason: ' . $reason, 0, 1000),
            ]);
            $this->audit->logRequired('donation.edited', 'donation', $id, ['mode' => 'contact', 'reason' => $reason, 'fields' => $changed], $actor);
        }
        return ['id' => $id, 'reference' => (string) $d['public_reference'], 'mode' => 'contact', 'changed' => $changed, 'review_restarted' => false];
    }

    /** Same rules as DonationReviewService for the money / bank part of a staff entry. */
    private function readTransfer(array $in, string $method): array
    {
        $amount = (string) ($in['amount'] ?? '');
        if (!preg_match('/^[0-9]{1,10}(?:\.[0-9]{1,2})?$/', $amount)) {
            throw new InvalidArgumentException('Enter a valid amount with up to two decimal places.');
        }
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '');
        $minor = ((int) $whole * 100) + (int) str_pad($fraction, 2, '0');
        if ($minor < 100 || $minor > 500000000) {
            throw new InvalidArgumentException('Donation amount is outside the permitted range.');
        }
        $date = $this->text($in, 'received_at', 10);
        if (!$this->validDate($date) || $date > gmdate('Y-m-d')) {
            throw new InvalidArgumentException('A valid transaction date not in the future is required.');
        }
        $currency = strtoupper((string) ($in['currency'] ?? ''));
        if (!in_array($currency, self::CURRENCIES, true)) {
            throw new InvalidArgumentException('Invalid currency.');
        }
        $bankId = (int) ($in['bank_account_id'] ?? 0);
        $bank = $bankId > 0 ? $this->db->selectOne("SELECT id, currency FROM bank_accounts WHERE id = :id AND status = 'published'", ['id' => $bankId]) : null;
        if (!$bank) {
            throw new InvalidArgumentException('Select the bank account the payment was made into.');
        }
        if (strtoupper((string) $bank['currency']) !== $currency) {
            throw new InvalidArgumentException('The selected bank account does not accept ' . $currency . '.');
        }
        $reference = $this->text($in, 'bank_reference', 120);
        $deposited = null;
        if ($method === 'cash') {
            $deposited = $this->text($in, 'deposited_at', 10);
            if (!$this->validDate($deposited) || $deposited > gmdate('Y-m-d') || $deposited < $date) {
                throw new InvalidArgumentException('Cash needs a bank deposit date that is not before the collection date or in the future.');
            }
            if (!$reference) {
                throw new InvalidArgumentException('Cash needs the bank deposit slip number.');
            }
        }
        return ['minor' => $minor, 'date' => $date, 'currency' => $currency, 'bank_id' => (int) $bank['id'], 'reference' => $reference, 'deposited_at' => $deposited];
    }

    // ================================================================== in-kind donations

    /**
     * @param array<string,mixed> $in posted form fields (same names as the public goods form)
     * @return array{id:int,reference:string,changed:list<string>,certificate_stale:bool}
     * @throws InvalidArgumentException with a message that is safe to show to staff
     */
    public function updateInKind(int $id, array $in, int $actor): array
    {
        $name = $this->text($in, 'donor_name', 150);
        $email = $this->text($in, 'donor_email', 191);
        $phone = $this->text($in, 'donor_phone', 40);
        if (!$name || !$email || !$phone || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Full name, a valid email and a mobile number are required.');
        }
        $items = $this->readItems($in);
        $method = (string) ($in['handover_method'] ?? 'drop_off');
        if (!in_array($method, ['drop_off', 'pickup'], true)) {
            throw new InvalidArgumentException('Please choose how the goods reach us.');
        }
        $address = $this->text($in, 'donor_address', 500);
        $pickup = $method === 'pickup' ? ($this->text($in, 'pickup_address', 500) ?? $address) : null;
        if ($method === 'pickup' && !$pickup) {
            throw new InvalidArgumentException('Please enter the address where the goods should be collected.');
        }
        $date = $this->text($in, 'handover_date', 10);
        if ($date !== null && (!$this->validDate($date) || $date < '2000-01-01' || $date > gmdate('Y-m-d', time() + 366 * 86400))) {
            throw new InvalidArgumentException('Please enter a valid hand-over date.');
        }
        $self = (string) ($in['acknowledgment_self'] ?? '1') === '1';
        $recipient = $self ? null : $this->text($in, 'recipient_name', 160);
        if (!$self && !$recipient) {
            throw new InvalidArgumentException('Recipient full name is required.');
        }
        $recipientEmail = $self ? null : $this->text($in, 'recipient_email', 191);
        if ($recipientEmail && !filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Recipient email is invalid.');
        }
        $referral = (string) ($in['referral_enabled'] ?? '0') === '1';
        $referrer = $referral ? $this->text($in, 'referrer_name', 160) : null;
        if ($referral && !$referrer) {
            throw new InvalidArgumentException('Referrer name is required.');
        }

        $new = [
            'donor_name' => $name, 'donor_email' => strtolower($email), 'donor_phone' => $phone,
            'donor_city' => $this->text($in, 'donor_city', 100), 'donor_address' => $address,
            'handover_method' => $method, 'handover_date' => $date, 'pickup_address' => $pickup,
            'description' => $this->text($in, 'description', 2000),
            'acknowledgment_self' => (int) $self, 'recipient_name' => $recipient,
            'recipient_phone' => $self ? null : $this->text($in, 'recipient_phone', 40),
            'recipient_email' => $recipientEmail,
            'recipient_address' => $self ? null : $this->text($in, 'recipient_address', 500),
            'acknowledgment_instructions' => $this->text($in, 'acknowledgment_instructions', 1000),
            'referral_enabled' => (int) $referral, 'referrer_name' => $referrer,
            'referrer_phone' => $referral ? $this->text($in, 'referrer_phone', 40) : null,
            'referral_notes' => $referral ? $this->text($in, 'referral_notes', 1000) : null,
        ];

        return $this->db->transaction(function () use ($id, $in, $new, $items, $actor): array {
            $row = $this->db->selectOne('SELECT * FROM inkind_donations WHERE id = :id' . $this->lock(), ['id' => $id]);
            if ($row === null) {
                throw new InvalidArgumentException('In-kind donation not found.');
            }
            $changed = [];
            foreach ($new as $col => $val) {
                if ($this->differs($row[$col] ?? null, $val)) {
                    $changed[] = $col;
                }
            }
            $oldItems = $this->db->select('SELECT category, item_name, quantity, unit, details FROM inkind_donation_items WHERE inkind_id = :id ORDER BY sort_order, id', ['id' => $id]);
            $itemsChanged = count($oldItems) !== count($items);
            if (!$itemsChanged) {
                foreach ($items as $i => $it) {
                    foreach ($it as $k => $v) {
                        if ($this->differs($oldItems[$i][$k] ?? null, $v)) {
                            $itemsChanged = true;
                            break 2;
                        }
                    }
                }
            }
            if ($itemsChanged) {
                $changed[] = 'items';
            }
            $reference = (string) $row['public_reference'];
            if ($changed === []) {
                return ['id' => $id, 'reference' => $reference, 'changed' => [], 'certificate_stale' => false];
            }

            $reason = $this->editReason($in);
            $update = $new;
            if (array_intersect($changed, ['donor_name', 'donor_email', 'donor_phone'])) {
                $update['donor_id'] = $this->donors->findOrCreate($new['donor_name'], $new['donor_email'], $new['donor_phone']);
            }
            $this->db->update('inkind_donations', $update, 'id = :id', ['id' => $id]);
            if ($itemsChanged) {
                $this->db->delete('inkind_donation_items', 'inkind_id = :id', ['id' => $id]);
                foreach ($items as $i => $item) {
                    $this->db->insert('inkind_donation_items', $item + ['inkind_id' => $id, 'sort_order' => $i]);
                }
            }
            $stale = (string) $row['status'] === 'verified'
                && $this->db->int("SELECT COUNT(*) FROM inkind_certificates WHERE inkind_id = :id AND status = 'generated'", ['id' => $id]) > 0;
            $this->db->insert('inkind_events', [
                'inkind_id' => $id, 'action' => 'edited', 'actor_user_id' => $actor,
                'note' => mb_substr('Edited by staff (' . implode(', ', $changed) . '). Reason: ' . $reason . ($stale ? ' The issued certificate was not changed; re-issue it to reflect the edit.' : ''), 0, 1000),
            ]);
            $this->audit->logRequired('inkind.edited', 'inkind_donation', $id, ['reason' => $reason, 'fields' => $changed, 'status' => (string) $row['status'], 'items' => count($items)], $actor);
            return ['id' => $id, 'reference' => $reference, 'changed' => $changed, 'certificate_stale' => $stale];
        });
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
                continue;
            }
            if ($name === '' || mb_strlen($name) > 150) {
                throw new InvalidArgumentException('Please describe each item in 150 characters or fewer.');
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
                'category' => isset(InKindDonationService::CATEGORIES[$cat]) ? $cat : 'other',
                'item_name' => $name, 'quantity' => (int) $qtyRaw,
                'unit' => $unit !== '' ? $unit : null, 'details' => $details !== '' ? $details : null,
            ];
        }
        if ($items === []) {
            throw new InvalidArgumentException('Please add at least one item with its quantity.');
        }
        if (count($items) > InKindDonationService::MAX_ITEMS) {
            throw new InvalidArgumentException('You can list up to ' . InKindDonationService::MAX_ITEMS . ' items. Describe the rest in the notes.');
        }
        return $items;
    }

    // ================================================================== helpers

    /** @param array<string,mixed> $in */
    private function text(array $in, string $key, int $limit): ?string
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

    /** @param array<string,mixed> $in @throws InvalidArgumentException */
    private function editReason(array $in): string
    {
        $reason = is_scalar($in['reason'] ?? null) ? trim((string) $in['reason']) : '';
        if (mb_strlen($reason) < 5) {
            throw new InvalidArgumentException('Enter the reason for this edit (at least 5 characters).');
        }
        if (mb_strlen($reason) > 500) {
            throw new InvalidArgumentException('The reason is too long (500 characters at most).');
        }
        return $reason;
    }

    private function validDate(?string $date): bool
    {
        if ($date === null) {
            return false;
        }
        $p = \DateTimeImmutable::createFromFormat('!Y-m-d', $date, new \DateTimeZone('UTC'));
        return $p !== false && $p->format('Y-m-d') === $date;
    }

    /** Loose comparison for values that come back from the database as strings. */
    private function differs(mixed $old, mixed $new): bool
    {
        $norm = static fn (mixed $v): string => $v === null ? '' : (string) $v;
        return $norm($old) !== $norm($new);
    }

    /** Remove a private upload if it exists. Never throws. */
    private function deleteFile(?string $relativePath): void
    {
        try {
            $abs = $this->uploads->privatePath($relativePath);
            if ($abs !== null && is_file($abs)) {
                @unlink($abs);
            }
        } catch (Throwable) {
            // A leftover file is harmless and never web-accessible.
        }
    }

    /** Row lock on MySQL; SQLite (used only by automated tests) has no FOR UPDATE. */
    private function lock(): string
    {
        return $this->db->pdo()->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'mysql' ? ' FOR UPDATE' : '';
    }
}
