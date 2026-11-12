<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Logger;
use App\Mail\MailQueue;
use App\Repositories\DonationRepository;
use RuntimeException;
use Throwable;

/**
 * Tax exemption certificates for verified donations.
 *  - one certificate per donation (UNIQUE donation_id + row lock): no duplicate numbers, files or emails;
 *  - generation and email are independent: an email failure never touches donation verification;
 *  - the PDF lives in storage/private/certificates (never web-served) and is streamed to authorised staff only.
 */
final class DonationCertificateService
{
    public function __construct(
        private Database $db,
        private DonationRepository $donations,
        private SettingsService $settings,
        private CertificatePdfService $pdf,
        private MailQueue $mail,
        private AuditService $audit,
        private Logger $logger
    ) {
    }

    /** Why a donation cannot get a certificate, or null when it can. */
    public function ineligibleReason(array $d): ?string
    {
        if ((string) $d['status'] !== 'completed') {
            return 'The donation is not confirmed.';
        }
        if ($d['gateway_id'] === null && (string) ($d['verification_status'] ?? '') !== 'verified') {
            return 'Offline donations need finance verification first.';
        }
        return null;
    }

    public function find(int $donationId): ?array
    {
        return $this->db->selectOne('SELECT * FROM donation_certificates WHERE donation_id = :d', ['d' => $donationId]);
    }

    /** Verified donation -> certificate -> email. Never throws; failures are stored on the certificate row. */
    public function issue(int $donationId, ?int $actor = null): array
    {
        try {
            $cert = $this->generate($donationId, $actor);
            if ($cert['status'] === 'generated' && $cert['email_status'] === 'not_sent') {
                $this->sendEmail((int) $cert['id'], false);
            }
        } catch (Throwable $e) {
            $this->logger->error('Certificate issue failed', ['donation' => $donationId, 'error' => $e->getMessage()]);
            return ['ok' => false, 'message' => $e->getMessage(), 'certificate' => $this->find($donationId)];
        }
        $row = $this->find($donationId);
        return ['ok' => $row !== null && $row['status'] === 'generated', 'message' => (string) ($row['generation_error'] ?? ''), 'certificate' => $row];
    }

    /** Idempotent: returns the existing certificate when already generated. */
    public function generate(int $donationId, ?int $actor = null): array
    {
        $claim = $this->db->transaction(function () use ($donationId): array {
            $d = $this->db->selectOne('SELECT * FROM donations WHERE id = :id FOR UPDATE', ['id' => $donationId]);
            if (!$d) {
                throw new RuntimeException('Donation not found.');
            }
            if (($why = $this->ineligibleReason($d)) !== null) {
                throw new RuntimeException($why);
            }
            $row = $this->db->selectOne('SELECT * FROM donation_certificates WHERE donation_id = :id FOR UPDATE', ['id' => $donationId]);
            if ($row && $row['status'] === 'generated' && $this->absolutePath($row) !== null) {
                return ['row' => $row, 'work' => false];
            }
            if (!$row) {
                $this->db->insert('donation_certificates', ['donation_id' => $donationId, 'status' => 'pending']);
                $row = $this->db->selectOne('SELECT * FROM donation_certificates WHERE donation_id = :id', ['id' => $donationId]);
            }
            if (!$row['certificate_number']) {
                $number = sprintf('ALM-TC-%s-%06d', gmdate('Y'), (int) $row['id']);
                $this->db->update('donation_certificates', ['certificate_number' => $number], 'id = :id', ['id' => $row['id']]);
                $row['certificate_number'] = $number;
            }
            return ['row' => $row, 'work' => true];
        });
        if (!$claim['work']) {
            return $claim['row'];
        }
        $row = $claim['row'];
        $certId = (int) $row['id'];

        try {
            $info = $this->donations->find($donationId) ?? throw new RuntimeException('Donation not found.');
            $issuedAt = $row['issued_at'] ?: gmdate('Y-m-d H:i:s');
            $binary = $this->pdf->render($this->pdfData($info, (string) $row['certificate_number'], $issuedAt));
            if (!str_starts_with($binary, '%PDF')) {
                throw new RuntimeException('PDF renderer returned invalid output.');
            }
            $rel = 'private/certificates/' . $row['certificate_number'] . '.pdf';
            $this->store($rel, $binary);
            $this->db->update('donation_certificates', [
                'status' => 'generated', 'file_path' => $rel, 'file_sha256' => hash('sha256', $binary),
                'issued_at' => $issuedAt, 'generated_at' => gmdate('Y-m-d H:i:s'), 'generated_by' => $actor, 'generation_error' => null,
            ], "id = :id AND status <> 'generated'", ['id' => $certId]);
            $this->db->update('donations', ['certificate_status' => 'issued'], 'id = :id', ['id' => $donationId]);
            $this->audit->log('donation.certificate.generated', 'donation', $donationId, ['certificate' => $row['certificate_number']], $actor);
        } catch (Throwable $e) {
            $msg = mb_substr($e->getMessage(), 0, 480);
            $this->db->update('donation_certificates', ['status' => 'failed', 'generation_error' => $msg], "id = :id AND status <> 'generated'", ['id' => $certId]);
            $this->db->update('donations', ['certificate_status' => 'failed'], "id = :id AND certificate_status <> 'issued'", ['id' => $donationId]);
            $this->logger->error('Certificate generation failed', ['donation' => $donationId, 'error' => $msg]);
        }
        return $this->db->selectOne('SELECT * FROM donation_certificates WHERE id = :id', ['id' => $certId]) ?? $row;
    }

    /** Queue (first send) or re-send (retry of a failed email). A sent email is never sent twice. */
    public function sendEmail(int $certId, bool $retry): string
    {
        $row = $this->db->selectOne('SELECT * FROM donation_certificates WHERE id = :id', ['id' => $certId]);
        if (!$row || $row['status'] !== 'generated') {
            throw new RuntimeException('The certificate has not been generated.');
        }
        $this->syncEmailStatus($row);
        $row = $this->db->selectOne('SELECT * FROM donation_certificates WHERE id = :id', ['id' => $certId]);
        $d = $this->donations->find((int) $row['donation_id']);
        $to = trim((string) ($d['donor_email'] ?? ''));
        if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $this->db->update('donation_certificates', ['email_status' => 'skipped', 'email_last_error' => 'No valid donor email on file.'], 'id = :id', ['id' => $certId]);
            return 'skipped';
        }
        // Atomic claim: only one caller can move not_sent/failed/skipped -> queued.
        $claimed = $this->db->run(
            "UPDATE donation_certificates SET email_status = 'queued', email_to = :to, email_last_error = NULL WHERE id = :id AND (email_status IN ('not_sent','failed','skipped') OR (:retry = 1 AND email_status = 'queued' AND email_last_error IS NOT NULL))",
            ['id' => $certId, 'to' => $to, 'retry' => $retry ? 1 : 0]
        )->rowCount();
        if ($claimed !== 1) {
            return (string) $row['email_status']; // already queued or sent
        }

        try {
            $dedupe = 'donation.certificate:' . $row['donation_id'];
            $jobId = $this->db->selectOne('SELECT id, status FROM email_jobs WHERE dedupe_key = :k', ['k' => $dedupe]);
            if ($jobId) {
                // Retry of the same job: reset it, so one donation never has two certificate emails.
                if ($jobId['status'] === 'sent') {
                    $this->syncEmailStatus($row);
                    return 'sent';
                }
                $this->db->run("UPDATE email_jobs SET status = 'queued', attempts = 0, last_error = NULL, recipient_email = :to, available_at = UTC_TIMESTAMP() WHERE id = :id AND status IN ('failed','cancelled')", ['id' => $jobId['id'], 'to' => $to]);
                $id = (int) $jobId['id'];
            } else {
                $id = $this->mail->enqueue('donation.certificate', $to, (string) ($d['donor_name'] ?? ''), $this->emailData($d, $row), $dedupe);
            }
            if ($id === null) {
                throw new RuntimeException('The email could not be queued.');
            }
            $this->db->update('donation_certificates', ['email_job_id' => $id], 'id = :id', ['id' => $certId]);
            if ($retry) {
                $this->mail->processJobNow($id); // staff asked for it: send now and show the result
            }
        } catch (Throwable $e) {
            $this->db->update('donation_certificates', ['email_status' => 'failed', 'email_last_error' => mb_substr($e->getMessage(), 0, 480)], 'id = :id', ['id' => $certId]);
            $this->logger->error('Certificate email failed', ['certificate' => $certId, 'error' => $e->getMessage()]);
        }
        $row = $this->db->selectOne('SELECT * FROM donation_certificates WHERE id = :id', ['id' => $certId]);
        $this->audit->log('donation.certificate.email', 'donation', (int) $row['donation_id'], ['retry' => $retry], null);
        return (string) $this->syncEmailStatus($row)['email_status'];
    }

    /** Mirror the mail queue's state onto the certificate row. */
    public function syncEmailStatus(array $row): array
    {
        if (empty($row['email_job_id'])) {
            return $row;
        }
        $job = $this->db->selectOne('SELECT status, attempts, last_error, sent_at FROM email_jobs WHERE id = :id', ['id' => $row['email_job_id']]);
        if (!$job) {
            return $row;
        }
        $status = match ($job['status']) { 'sent' => 'sent', 'failed', 'cancelled' => 'failed', default => 'queued' };
        $upd = ['email_status' => $status, 'email_attempts' => (int) $job['attempts'],
            'email_last_error' => $status === 'sent' ? null : ($job['last_error'] !== null ? mb_substr((string) $job['last_error'], 0, 480) : $row['email_last_error']),
            'email_sent_at' => $status === 'sent' ? ($job['sent_at'] ?: gmdate('Y-m-d H:i:s')) : null];
        $this->db->update('donation_certificates', $upd, 'id = :id', ['id' => $row['id']]);
        return array_merge($row, $upd);
    }

    public function absolutePath(array $row): ?string
    {
        $rel = (string) ($row['file_path'] ?? '');
        if ($rel === '' || !str_starts_with($rel, 'private/certificates/') || str_contains($rel, '..')) {
            return null;
        }
        $abs = dirname(__DIR__, 2) . '/storage/' . $rel;
        return is_file($abs) ? $abs : null;
    }

    /** Console sweep: retry failed generations/emails; with $since also issue missing certificates for donations on/after that date. */
    public function sweep(int $limit = 50, ?string $since = null): array
    {
        $stats = ['issued' => 0, 'retried' => 0];
        if ($since !== null) {
            $ids = $this->db->select(
                "SELECT d.id FROM donations d LEFT JOIN donation_certificates c ON c.donation_id = d.id
                 WHERE c.id IS NULL AND d.status = 'completed' AND (d.gateway_id IS NOT NULL OR d.verification_status = 'verified') AND d.created_at >= :s ORDER BY d.id LIMIT " . max(1, $limit),
                ['s' => $since . ' 00:00:00']
            );
            foreach ($ids as $r) { $this->issue((int) $r['id']); $stats['issued']++; }
        }
        foreach ($this->db->select("SELECT * FROM donation_certificates WHERE status IN ('pending','failed') OR email_status IN ('not_sent','failed') ORDER BY id LIMIT " . max(1, $limit)) as $c) {
            if ($c['status'] !== 'generated') { $this->generate((int) $c['donation_id']); }
            $fresh = $this->db->selectOne('SELECT * FROM donation_certificates WHERE id = :id', ['id' => $c['id']]);
            if ($fresh && $fresh['status'] === 'generated' && in_array($fresh['email_status'], ['not_sent', 'failed'], true)) {
                $this->sendEmail((int) $fresh['id'], $fresh['email_status'] === 'failed');
            }
            $stats['retried']++;
        }
        return $stats;
    }

    // -------------------------------------------------------------------------------------------------------------

    private function setting(string $key): string
    {
        return trim((string) $this->settings->get('certificate.' . $key, ''));
    }

    /** Builds the PDF input; refuses to issue when registration/tax details are missing or the approval does not cover the donation date. */
    private function pdfData(array $d, string $number, string $issuedAt): array
    {
        $required = ['org_name' => 'organisation name', 'org_address' => 'address', 'ntn' => 'NTN / registration number', 'approval_text' => 'tax approval text', 'approval_valid_from' => 'approval start date', 'approval_valid_until' => 'approval end date'];
        foreach ($required as $key => $label) {
            if ($this->setting($key) === '') {
                throw new RuntimeException('Foundation ' . $label . ' is not configured (site_settings certificate.' . $key . ').');
            }
        }
        $date = substr((string) ($d['transaction_at'] ?: $d['completed_at'] ?: $d['created_at']), 0, 10);
        if ($date < $this->setting('approval_valid_from') || $date > $this->setting('approval_valid_until')) {
            throw new RuntimeException('The donation date (' . $date . ') is outside the configured tax approval period.');
        }
        $fmt = static fn (string $ymd): string => (new \DateTimeImmutable($ymd, new \DateTimeZone('UTC')))->format('j F Y');
        $logo = dirname(__DIR__, 2) . '/assets/images/logo/cropped-logo-1-scaled-1.png';
        $signature = dirname(__DIR__, 2) . '/assets/images/signature/chairperson-signature.png';
        return [
            'donor_name' => trim((string) ($d['donor_name'] ?? '')) ?: 'Donor',
            'amount' => (string) $d['currency'] . ' ' . number_format(((int) $d['amount_minor']) / 100, 2),
            'donation_date' => $fmt($date), 'reference' => (string) $d['public_reference'],
            'certificate_number' => $number, 'issue_date' => $fmt(substr($issuedAt, 0, 10)),
            'org_name' => $this->setting('org_name'), 'org_address' => $this->setting('org_address'), 'ntn' => $this->setting('ntn'),
            'approval_text' => $this->setting('approval_text'),
            'valid_from' => $fmt($this->setting('approval_valid_from')), 'valid_until' => $fmt($this->setting('approval_valid_until')),
            'signatory_name' => $this->setting('signatory_name') ?: 'Sophia Warraich',
            'signatory_title' => in_array($this->setting('signatory_title'), ['', 'Authorized Signatory'], true) ? 'Chairperson' : $this->setting('signatory_title'),
            'logo' => is_file($logo) ? $logo : null,
            'signature' => is_file($signature) ? $signature : null,
            // Static pages: FBR 2(36) approval (2 pages), Shariah approval, Punjab Charity Commission registration.
            'appendix' => dirname(__DIR__, 2) . '/assets/documents/Certificate-Supporting-Documents.pdf',
        ];
    }

    private function emailData(array $d, array $cert): array
    {
        $date = substr((string) ($d['transaction_at'] ?: $d['completed_at'] ?: $d['created_at']), 0, 10);
        return [
            'heading' => 'Your tax exemption certificate',
            'donor_name' => (string) ($d['donor_name'] ?? ''),
            'amount' => (string) $d['currency'] . ' ' . number_format(((int) $d['amount_minor']) / 100, 2),
            'reference' => (string) $d['public_reference'], 'certificate_number' => (string) $cert['certificate_number'],
            'donation_date' => (new \DateTimeImmutable($date, new \DateTimeZone('UTC')))->format('j F Y'),
            'attachments_json' => json_encode([['path' => $cert['file_path'], 'name' => $cert['certificate_number'] . '.pdf']]),
        ];
    }

    private function store(string $rel, string $binary): void
    {
        $dir = dirname(__DIR__, 2) . '/storage/private/certificates';
        if (!is_dir($dir) && !@mkdir($dir, 0770, true) && !is_dir($dir)) {
            throw new RuntimeException('Unable to create the certificate directory.');
        }
        if (!is_file($dir . '/.htaccess')) {
            @file_put_contents($dir . '/.htaccess', "Require all denied\n");
        }
        $target = dirname(__DIR__, 2) . '/storage/' . $rel;
        $tmp = $target . '.' . bin2hex(random_bytes(4)) . '.tmp';
        if (file_put_contents($tmp, $binary) === false || !@rename($tmp, $target)) {
            @unlink($tmp);
            throw new RuntimeException('Unable to store the certificate PDF.');
        }
        @chmod($target, 0640);
    }
}
