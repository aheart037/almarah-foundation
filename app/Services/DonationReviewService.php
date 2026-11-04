<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Core\Database;
use App\Core\Str;
use App\Repositories\DonationRepository;
use App\Repositories\DonorRepository;
use InvalidArgumentException;
use RuntimeException;

/** Complements the existing donation/payment engine; never trusts proof as bank confirmation. */
final class DonationReviewService
{
    public function __construct(private Database $db, private DonationRepository $donations, private DonorRepository $donors, private UploadService $uploads, private AuditService $audit)
    {
    }

    public function stages(): array
    {
        return (array) Config::get('donation_review.stages', []);
    }

    private function value(array $in, string $key, int $limit): ?string
    {
        $raw = $in[$key] ?? '';
        if (!is_scalar($raw)) throw new InvalidArgumentException("{$key} must be plain text.");
        $value = trim((string) $raw);
        if (mb_strlen($value) > $limit) {
            throw new InvalidArgumentException("{$key} is too long.");
        }
        return $value === '' ? null : $value;
    }

    public function submit(array $in, ?array $file = null, ?int $actor = null): array
    {
        $method = (string) ($in['source'] ?? 'bank_transfer');
        if (!in_array($method, ['bank_transfer', 'online_transfer', 'cash', 'cheque', 'zelle', 'other'], true) ||
            ($actor === null && $method === 'cash')) {
            throw new InvalidArgumentException('Invalid payment method. Cash must be recorded by staff.');
        }
        $name = $this->value($in, 'donor_name', 150);
        $email = $this->value($in, 'donor_email', 191);
        $phone = $this->value($in, 'donor_phone', 40);
        $anonymous = $actor !== null && !empty($in['anonymous']);
        if ($anonymous && !$name) $name = 'Anonymous donor';
        if (!$name || (!$anonymous && (!$email || !$phone)) || ($email && !filter_var($email, FILTER_VALIDATE_EMAIL))) {
            throw new InvalidArgumentException('Full name, valid email and mobile number are required unless staff records an anonymous gift.');
        }
        $amount = (string) ($in['amount'] ?? '');
        if (!preg_match('/^[0-9]{1,10}(?:\\.[0-9]{1,2})?$/', $amount)) {
            throw new InvalidArgumentException('Enter a valid amount with up to two decimal places.');
        }
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '');
        $minor = ((int) $whole * 100) + (int) str_pad($fraction, 2, '0');
        if ($minor < 100 || $minor > 500000000) {
            throw new InvalidArgumentException('Donation amount is outside the permitted range.');
        }
        $date = $this->value($in, 'received_at', 10);
        $parsed = $date ? \DateTimeImmutable::createFromFormat('!Y-m-d', $date, new \DateTimeZone('UTC')) : null;
        if (!$parsed || $parsed->format('Y-m-d') !== $date || $date > gmdate('Y-m-d')) {
            throw new InvalidArgumentException('A valid transaction date not in the future is required.');
        }
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
        $reference = $this->value($in, 'bank_reference', 120);
        $duplicateCount = $reference ? $this->db->int(
            'SELECT COUNT(*) FROM donations WHERE bank_reference = :r AND gateway_id IS NULL AND created_at >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 180 DAY)', ['r' => $reference]
        ) : 0;
        $currency = strtoupper((string) ($in['currency'] ?? 'PKR'));
        if (!in_array($currency, array_keys(DonationCurrencies::OPTIONS), true)) throw new InvalidArgumentException('Invalid currency.');
        $purposeId = (int) ($in['purpose_id'] ?? 0);
        if ($purposeId < 0 || ($purposeId > 0 && !$this->db->selectOne('SELECT id FROM donation_purposes WHERE id=:id', ['id' => $purposeId]))) {
            throw new InvalidArgumentException('Unknown donation purpose.');
        }
        $proof = null;
        if ($file && (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $proof = $this->uploads->storePaymentProof($file);
        }
        try {
            $id = $this->db->transaction(function () use ($in, $actor, $method, $name, $email, $phone, $minor, $currency, $purposeId, $date, $self, $recipient, $recipientEmail, $referral, $referrer, $reference, $proof): int {
                $donorId = $email ? $this->donors->findOrCreate($name, strtolower($email), $phone) : null;
                $id = $this->donations->create([
                    'public_reference' => Str::publicReference('ALM'), 'donor_id' => $donorId,
                    'amount_minor' => $minor, 'currency' => $currency, 'gateway_id' => null,
                    'purpose_id' => $purposeId ?: null, 'donation_type' => ($in['donation_type'] ?? '') === 'monthly' ? 'monthly' : 'one_time',
                    'anonymous' => !empty($in['anonymous']),
                    'source' => $method, 'bank_reference' => $reference, 'recorded_by' => $actor,
                    'donor_message' => $this->value($in, 'note', 255), 'status' => 'pending',
                ]);
                $this->db->update('donations', [
                    'transaction_at' => $date . ' 12:00:00', 'payment_method' => $method,
                    'payment_proof_path' => $proof, 'acknowledgment_self' => (int) $self,
                    'recipient_name' => $recipient, 'recipient_phone' => $self ? null : $this->value($in, 'recipient_phone', 40),
                    'recipient_email' => $recipientEmail, 'recipient_address' => $self ? null : $this->value($in, 'recipient_address', 500),
                    'acknowledgment_instructions' => $this->value($in, 'acknowledgment_instructions', 1000),
                    'referral_enabled' => (int) $referral, 'referrer_name' => $referrer,
                    'referrer_phone' => $referral ? $this->value($in, 'referrer_phone', 40) : null,
                    'referral_notes' => $referral ? $this->value($in, 'referral_notes', 1000) : null,
                    'verification_status' => 'submitted',
                ], 'id = :id', ['id' => $id]);
                $this->db->insert('donation_review_events', ['donation_id' => $id, 'actor_user_id' => $actor,
                    'stage_key' => 'submission', 'decision' => 'submitted', 'comment' => 'Donation submitted for review.']);
                return $id;
            });
        } catch (\Throwable $e) {
            if ($proof && ($path = $this->uploads->privatePath($proof))) {
                @unlink($path);
            }
            throw $e;
        }
        $this->audit->log('donation.submitted', 'donation', $id, ['possible_duplicates' => $duplicateCount], $actor);
        return ['id' => $id, 'reference' => $this->donations->find($id)['public_reference'], 'possible_duplicates' => $duplicateCount];
    }

    /** Optional supplemental metadata on the existing gateway checkout; no payment state changes. */
    public function captureGatewayDetails(int $id, array $in, string $method): void
    {
        $self = (string) ($in['acknowledgment_self'] ?? '1') === '1';
        $referral = (string) ($in['referral_enabled'] ?? '0') === '1';
        $recipient = $self ? null : $this->value($in, 'recipient_name', 160);
        $referrer = $referral ? $this->value($in, 'referrer_name', 160) : null;
        if (!$self && !$recipient) throw new InvalidArgumentException('Enter the recipient full name.');
        if ($referral && !$referrer) throw new InvalidArgumentException('Enter the referrer full name.');
        $recipientEmail = $self ? null : $this->value($in, 'recipient_email', 191);
        if ($recipientEmail && !filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) throw new InvalidArgumentException('Recipient email is invalid.');
        $this->db->update('donations', [
            'payment_method' => $method, 'acknowledgment_self' => (int) $self,
            'recipient_name' => $recipient,
            'recipient_phone' => $self ? null : $this->value($in, 'recipient_phone', 40),
            'recipient_email' => $recipientEmail,
            'recipient_address' => $self ? null : $this->value($in, 'recipient_address', 500),
            'acknowledgment_instructions' => $this->value($in, 'acknowledgment_instructions', 1000),
            'referral_enabled' => (int) $referral, 'referrer_name' => $referrer,
            'referrer_phone' => $referral ? $this->value($in, 'referrer_phone', 40) : null,
            'referral_notes' => $referral ? $this->value($in, 'referral_notes', 1000) : null,
        ], 'id=:id', ['id' => $id]);
    }

    public function history(int $id): array
    {
        return $this->db->select('SELECT e.*, u.email AS actor_email FROM donation_review_events e LEFT JOIN users u ON u.id = e.actor_user_id WHERE e.donation_id = :id ORDER BY e.id', ['id' => $id]);
    }

    public function possibleDuplicates(array $donation): array
    {
        $ref = trim((string) ($donation['bank_reference'] ?? ''));
        if ($ref === '') return [];
        return $this->db->select('SELECT id, public_reference, amount_minor, created_at FROM donations WHERE id <> :id AND bank_reference = :ref ORDER BY id DESC LIMIT 20', ['id' => $donation['id'], 'ref' => $ref]);
    }

    /** Row lock serializes simultaneous reviewers; distinct actors must perform every stage. */
    public function decide(int $id, int $actor, array $roles, string $decision, string $comment): bool
    {
        if (!in_array($decision, ['approve', 'reject'], true) || mb_strlen($comment) > 1000 || ($decision === 'reject' && mb_strlen(trim($comment)) < 5)) {
            throw new InvalidArgumentException('Enter a valid decision and a rejection reason (at least five characters).');
        }
        $stages = $this->stages();
        if (!$stages) throw new RuntimeException('Review stages are not configured.');
        $final = $this->db->transaction(function () use ($id, $actor, $roles, $decision, $comment, $stages): bool {
            $d = $this->db->selectOne('SELECT * FROM donations WHERE id = :id FOR UPDATE', ['id' => $id]);
            if (!$d || in_array($d['status'], ['failed', 'cancelled', 'refunded', 'abandoned'], true)) throw new InvalidArgumentException('Donation cannot be reviewed in its current payment state.');
            $keys = array_keys($stages);
            $position = array_search((string) $d['verification_status'], $keys, true);
            $next = $position === false && $d['verification_status'] === 'submitted' ? 0 : ($position === false ? false : $position + 1);
            if ($next === false || !isset($keys[$next])) throw new InvalidArgumentException('This donation is not awaiting a review stage.');
            $stage = $keys[$next];
            if (!array_intersect($roles, (array) ($stages[$stage]['roles'] ?? []))) throw new InvalidArgumentException('Your role cannot approve this stage.');
            if ((int) ($d['recorded_by'] ?? 0) === $actor || $this->db->int("SELECT COUNT(*) FROM donation_review_events WHERE donation_id = :id AND actor_user_id = :a AND decision = :v AND id > COALESCE((SELECT MAX(r.id) FROM donation_review_events r WHERE r.donation_id=:reset_id AND r.decision='reset'), 0)", ['id' => $id, 'a' => $actor, 'v' => 'approve', 'reset_id' => $id])) {
                throw new InvalidArgumentException('Submitters and previous reviewers cannot approve their own donation.');
            }
            if ($stage === 'final_verification' && $d['gateway_id'] !== null && $d['status'] !== 'completed') {
                throw new InvalidArgumentException('Gateway has not confirmed the payment.');
            }
            $this->db->insert('donation_review_events', ['donation_id' => $id, 'actor_user_id' => $actor,
                'stage_key' => $stage, 'decision' => $decision, 'comment' => trim($comment)]);
            $isFinal = $decision === 'approve' && $next === count($keys) - 1;
            $this->db->update('donations', [
                'verification_status' => $decision === 'reject' ? 'rejected' : ($isFinal ? 'verified' : $stage),
                'verified_by' => $isFinal ? $actor : null,
                'verified_at' => $isFinal ? gmdate('Y-m-d H:i:s') : null,
                'certificate_status' => $isFinal ? 'issued' : $d['certificate_status'],
                // Offline donation becomes financially completed only after final review.
                'status' => $isFinal && $d['gateway_id'] === null ? 'completed' : $d['status'],
                'completed_at' => $isFinal && $d['gateway_id'] === null ? ($d['transaction_at'] ?: gmdate('Y-m-d H:i:s')) : $d['completed_at'],
            ], 'id = :id', ['id' => $id]);
            return $isFinal;
        });
        $this->audit->log('donation.review.' . $decision, 'donation', $id, ['stage' => $final ? 'final_verification' : 'progress'], $actor);
        return $final;
    }
}
