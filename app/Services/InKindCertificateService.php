<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Logger;
use App\Mail\MailQueue;
use RuntimeException;
use Throwable;

/**
 * Thank-you certificates for verified in-kind donations. Every generation is a new row (version) so the
 * history is never overwritten; emailing is idempotent per certificate (a sent email is never sent twice).
 */
final class InKindCertificateService
{
    public function __construct(
        private Database $db,
        private InKindDonationService $donations,
        private SettingsService $settings,
        private CertificatePdfService $pdf,
        private MailQueue $mail,
        private AuditService $audit,
        private Logger $logger
    ) {
    }

    public function find(int $certId): ?array
    {
        return $this->db->selectOne('SELECT * FROM inkind_certificates WHERE id = :id', ['id' => $certId]);
    }

    /** Newest successfully generated certificate for a donation. */
    public function latest(int $inkindId): ?array
    {
        return $this->db->selectOne(
            "SELECT * FROM inkind_certificates WHERE inkind_id = :id AND status = 'generated' ORDER BY version DESC LIMIT 1",
            ['id' => $inkindId]
        );
    }

    /** @return list<array<string,mixed>> newest first, including failed attempts */
    public function history(int $inkindId): array
    {
        return $this->db->select(
            "SELECT c.*, CONCAT_WS(' ', u.first_name, u.last_name) AS generated_by_name FROM inkind_certificates c
             LEFT JOIN users u ON u.id = c.generated_by WHERE c.inkind_id = :id ORDER BY c.version DESC",
            ['id' => $inkindId]
        );
    }

    /**
     * Create a certificate for a verified donation. Idempotent unless $newVersion is true (staff re-issue,
     * e.g. after correcting a name): then a new version is added and the old ones stay in the history.
     *
     * @return array{ok:bool,row:?array<string,mixed>,message:string}
     */
    public function generate(int $inkindId, ?int $actor = null, bool $newVersion = false): array
    {
        $claim = $this->db->transaction(function () use ($inkindId, $newVersion): array {
            $d = $this->db->selectOne('SELECT * FROM inkind_donations WHERE id = :id FOR UPDATE', ['id' => $inkindId]);
            if (!$d) {
                throw new RuntimeException('Donation not found.');
            }
            if ((string) $d['status'] !== 'verified') {
                throw new RuntimeException('Only a verified donation can receive a certificate.');
            }
            $existing = $this->latest($inkindId);
            if ($existing && !$newVersion && $this->absolutePath($existing) !== null) {
                return ['row' => $existing, 'work' => false, 'donation' => $d, 'version' => 0];
            }
            $version = 1 + $this->db->int('SELECT COALESCE(MAX(version), 0) FROM inkind_certificates WHERE inkind_id = :id', ['id' => $inkindId]);
            return ['row' => null, 'work' => true, 'donation' => $d, 'version' => $version];
        });
        if (!$claim['work']) {
            return ['ok' => true, 'row' => $claim['row'], 'message' => 'A certificate already exists.'];
        }

        $d = $claim['donation'];
        $version = (int) $claim['version'];
        $number = sprintf('ALM-IK-%s-%06d', gmdate('Y'), $inkindId);
        $name = InKindDonationService::certificateName($d);
        try {
            $this->pdfData($d, $number, $name); // keeps the existing settings checks
            $lines = [];
            foreach ($this->donations->items($inkindId) as $i) {
                $lines[] = $i['unit'] ? ((int) $i['quantity'] . ' ' . $i['unit'] . ' of ' . $i['item_name']) : ((int) $i['quantity'] . ' x ' . $i['item_name']);
            }
            $binary = (new DonorLetterPdfService())->render(DonorLetterPdfService::fromInKind($d, $lines, $name));
            if (!str_starts_with($binary, '%PDF')) {
                throw new RuntimeException('PDF renderer returned invalid output.');
            }
            $rel = 'private/certificates/' . $number . '-v' . $version . '-pack.pdf';
            $this->store($rel, $binary);
            $certId = $this->db->insert('inkind_certificates', [
                'inkind_id' => $inkindId, 'version' => $version, 'certificate_number' => $number, 'status' => 'generated',
                'file_path' => $rel, 'file_sha256' => hash('sha256', $binary), 'certificate_name' => mb_substr($name, 0, 190), 'generated_by' => $actor,
            ]);
            $this->donations->event($inkindId, 'certificate_generated', $actor, 'Certificate ' . $number . ' (version ' . $version . ') for ' . $name . '.');
            $this->audit->log('inkind.certificate.generated', 'inkind_donation', $inkindId, ['certificate' => $number, 'version' => $version], $actor);
            return ['ok' => true, 'row' => $this->find($certId), 'message' => 'Certificate generated.'];
        } catch (Throwable $e) {
            $msg = mb_substr($e->getMessage(), 0, 480);
            try {
                $this->db->insert('inkind_certificates', [
                    'inkind_id' => $inkindId, 'version' => $version, 'certificate_number' => $number, 'status' => 'failed',
                    'certificate_name' => mb_substr($name, 0, 190), 'generation_error' => $msg, 'generated_by' => $actor,
                ]);
                $this->donations->event($inkindId, 'certificate_failed', $actor, $msg);
            } catch (Throwable) {
                // history write is best effort; the error is still logged below
            }
            $this->logger->error('In-kind certificate generation failed', ['inkind' => $inkindId, 'error' => $msg]);
            return ['ok' => false, 'row' => null, 'message' => $msg];
        }
    }

    /**
     * Queue (first send) or re-send (staff retry) the certificate email to the donor.
     *
     * @return 'sent'|'queued'|'failed'|'skipped'
     */
    public function sendEmail(int $certId, bool $retry = false): string
    {
        $row = $this->find($certId);
        if (!$row || $row['status'] !== 'generated') {
            throw new RuntimeException('The certificate has not been generated.');
        }
        $row = $this->syncEmailStatus($row);
        if ($row['email_status'] === 'sent' && !$retry) {
            return 'sent';
        }
        $d = $this->donations->find((int) $row['inkind_id']);
        $to = trim((string) ($d['donor_email'] ?? ''));
        if (!$d || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $this->db->update('inkind_certificates', ['email_status' => 'skipped', 'email_last_error' => 'No valid donor email on file.'], 'id = :id', ['id' => $certId]);
            return 'skipped';
        }
        $dedupe = 'inkind.certificate:' . $certId;
        try {
            $job = $this->db->selectOne('SELECT id, status FROM email_jobs WHERE dedupe_key = :k', ['k' => $dedupe]);
            if ($job && $job['status'] === 'sent' && !$retry) {
                return (string) $this->syncEmailStatus($row)['email_status'];
            }
            if ($job) {
                // Re-send on request: reset the same job so one certificate never has two queued emails.
                $this->db->run("UPDATE email_jobs SET status = 'queued', attempts = 0, last_error = NULL, recipient_email = :to, available_at = UTC_TIMESTAMP() WHERE id = :id AND status IN ('failed','cancelled','sent')", ['id' => $job['id'], 'to' => $to]);
                $jobId = (int) $job['id'];
            } else {
                $jobId = $this->mail->enqueue('inkind.certificate', $to, (string) $d['donor_name'], $this->emailData($d, $row), $dedupe);
            }
            if ($jobId === null) {
                throw new RuntimeException('The email could not be queued.');
            }
            $this->db->update('inkind_certificates', ['email_job_id' => $jobId, 'email_status' => 'queued', 'email_to' => $to, 'email_last_error' => null], 'id = :id', ['id' => $certId]);
            if ($retry) {
                $this->mail->processJobNow($jobId);
            }
        } catch (Throwable $e) {
            $this->db->update('inkind_certificates', ['email_status' => 'failed', 'email_to' => $to, 'email_last_error' => mb_substr($e->getMessage(), 0, 480)], 'id = :id', ['id' => $certId]);
            $this->logger->error('In-kind certificate email failed', ['certificate' => $certId, 'error' => $e->getMessage()]);
        }
        $row = $this->syncEmailStatus($this->find($certId) ?? $row);
        $this->donations->event((int) $row['inkind_id'], 'certificate_emailed', null, 'Email to ' . $to . ': ' . $row['email_status'] . '.');
        $this->audit->log('inkind.certificate.email', 'inkind_donation', (int) $row['inkind_id'], ['retry' => $retry, 'status' => $row['email_status']], null);
        return (string) $row['email_status'];
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
        $upd = [
            'email_status' => $status, 'email_attempts' => (int) $job['attempts'],
            'email_last_error' => $status === 'sent' ? null : ($job['last_error'] !== null ? mb_substr((string) $job['last_error'], 0, 480) : ($row['email_last_error'] ?? null)),
            'email_sent_at' => $status === 'sent' ? ($job['sent_at'] ?: gmdate('Y-m-d H:i:s')) : null,
        ];
        $this->db->update('inkind_certificates', $upd, 'id = :id', ['id' => $row['id']]);
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

    // ------------------------------------------------------------------ internals

    private function setting(string $key): string
    {
        return trim((string) $this->settings->get('certificate.' . $key, ''));
    }

    private function pdfData(array $d, string $number, string $name): array
    {
        $orgName = $this->setting('org_name');
        $orgAddress = $this->setting('org_address');
        if ($orgName === '' || $orgAddress === '') {
            throw new RuntimeException('The foundation name and address are missing from the certificate settings.');
        }
        $all = $this->donations->items((int) $d['id']);
        $lines = [];
        foreach ($all as $i) {
            $lines[] = $i['unit'] ? ((int) $i['quantity'] . ' ' . $i['unit'] . ' of ' . $i['item_name']) : ((int) $i['quantity'] . ' × ' . $i['item_name']);
        }
        $fmt = static fn (string $ymd): string => (new \DateTimeImmutable($ymd, new \DateTimeZone('UTC')))->format('j F Y');
        $logo = dirname(__DIR__, 2) . '/assets/images/logo/cropped-logo-1-scaled-1.png';
        $signature = dirname(__DIR__, 2) . '/assets/images/signature/chairperson-signature.png';
        $date = substr((string) ($d['handover_date'] ?: $d['created_at']), 0, 10);
        return [
            'donor_name' => $name, 'items' => array_slice($lines, 0, 6), 'more_items' => max(0, count($lines) - 6),
            'donation_date' => $fmt($date), 'reference' => (string) $d['public_reference'], 'certificate_number' => $number,
            'issue_date' => $fmt(gmdate('Y-m-d')), 'org_name' => $orgName, 'org_address' => $orgAddress,
            'signatory_name' => $this->setting('signatory_name') ?: 'Sophia Warraich',
            'signatory_title' => in_array($this->setting('signatory_title'), ['', 'Authorized Signatory'], true) ? 'Chairperson' : $this->setting('signatory_title'),
            'logo' => is_file($logo) ? $logo : null, 'signature' => is_file($signature) ? $signature : null,
        ];
    }

    private function emailData(array $d, array $cert): array
    {
        $items = $this->donations->items((int) $d['id']);
        $summary = array_map(static fn (array $i): string => (int) $i['quantity'] . ($i['unit'] ? ' ' . $i['unit'] . ' of ' : ' × ') . $i['item_name'], array_slice($items, 0, 8));
        return [
            'heading' => 'Thank you for your donation of goods',
            'donor_name' => (string) $d['donor_name'], 'certificate_name' => (string) ($cert['certificate_name'] ?? ''),
            'reference' => (string) $d['public_reference'], 'certificate_number' => (string) $cert['certificate_number'],
            'items_summary' => implode('; ', $summary) . (count($items) > 8 ? '; and more' : ''),
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
