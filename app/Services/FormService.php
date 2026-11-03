<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Core\Database;
use App\Core\Logger;
use App\Core\RateLimiter;
use App\Mail\MailQueue;

/**
 * Stores every public form submission in MySQL and notifies the office.
 *
 * Shared protections for all forms:
 *  - honeypot field (`website`) — bots that fill it are silently "accepted";
 *  - minimum fill time (`_t` timestamp set by the view) — < 3 s is spam;
 *  - per-IP rate limit (rate_limits table, same as the donation flow);
 *  - duplicate fingerprint within 10 minutes is accepted but not re-stored;
 *  - an audit row in form_submissions for every attempt that reached PHP.
 *
 * Notification e-mails go through the central MailQueue (SMTP settings,
 * logs and retry live there), so nothing here talks to SMTP directly.
 */
final class FormService
{
    private const DUPLICATE_WINDOW_MIN = 10;

    public function __construct(
        private Database $db,
        private MailQueue $mail,
        private RateLimiter $limiter,
        private SettingsService $settings,
        private Logger $logger
    ) {
    }

    // ------------------------------------------------------------ guards

    /**
     * @param array<string,mixed> $input
     * @return string|null  Reason the submission is spam / throttled, or null if OK.
     */
    public function guard(string $formKey, array $input, string $ip, int $maxPerHour = 10): ?string
    {
        if (trim((string) ($input['website'] ?? '')) !== '') {
            $this->record($formKey, null, $input, $ip, 'spam');
            return 'spam';
        }
        $t = (int) ($input['_t'] ?? 0);
        if ($t > 0 && time() - $t < 3) {
            $this->record($formKey, null, $input, $ip, 'spam');
            return 'spam';
        }
        $key = 'form:' . $formKey . ':' . $ip;
        if ($this->limiter->tooManyAttempts($key, $maxPerHour)) {
            return 'throttled';
        }
        $this->limiter->hit($key, 60);
        return null;
    }

    /** @param array<string,mixed> $input */
    public function isDuplicate(string $formKey, array $input): bool
    {
        $fp = $this->fingerprint($formKey, $input);
        return $this->db->int(
            'SELECT COUNT(*) FROM form_submissions WHERE form_key = :k AND fingerprint = :f AND outcome = :o AND created_at >= DATE_SUB(NOW(), INTERVAL ' . self::DUPLICATE_WINDOW_MIN . ' MINUTE)',
            ['k' => $formKey, 'f' => $fp, 'o' => 'stored']
        ) > 0;
    }

    // ------------------------------------------------------------- forms

    /** @param array<string,mixed> $d */
    public function storeContact(array $d, string $ip, string $ua): int
    {
        $id = $this->db->insert('contact_messages', [
            'name'        => $this->s($d['name'], 120),
            'email'       => $this->s($d['email'], 191),
            'phone'       => $this->s($d['phone'] ?? '', 40) ?: null,
            'subject'     => $this->s($d['subject'] ?? '', 200) ?: null,
            'message'     => $this->s($d['message'], 5000),
            'status'      => 'new',
            'ip_address'  => $ip,
            'user_agent'  => mb_substr($ua, 0, 255),
            'fingerprint' => $this->fingerprint('contact', $d),
        ]);
        $this->record('contact', $id, $d, $ip, 'stored');
        $this->notify(
            'New contact message from ' . $this->s($d['name'], 120),
            [
                'Name'    => $d['name'],
                'Email'   => $d['email'],
                'Phone'   => $d['phone'] ?? '',
                'Message' => $d['message'],
            ],
            (string) $d['email']
        );
        return $id;
    }

    /**
     * @param array<string,mixed> $d
     * @return array{id:int,status:string}
     */
    public function storeNewsletter(array $d, string $ip, string $source = 'website'): array
    {
        $email = mb_strtolower(trim((string) $d['email']));
        $existing = $this->db->selectOne('SELECT id, status FROM newsletter_subscribers WHERE email = :e', ['e' => $email]);
        $unsubToken = bin2hex(random_bytes(20));
        if ($existing !== null) {
            if ($existing['status'] !== 'subscribed') {
                $this->db->update('newsletter_subscribers', [
                    'status'                 => 'subscribed',
                    'name'                   => $this->s($d['name'] ?? '', 120) ?: null,
                    'confirmed_at'           => date('Y-m-d H:i:s'),
                    'unsubscribed_at'        => null,
                    'unsubscribe_token_hash' => hash('sha256', $unsubToken),
                ], 'id = :id', ['id' => (int) $existing['id']]);
            }
            $this->record('newsletter', (int) $existing['id'], $d, $ip, 'duplicate');
            return ['id' => (int) $existing['id'], 'status' => 'existing'];
        }
        $id = $this->db->insert('newsletter_subscribers', [
            'name'                   => $this->s($d['name'] ?? '', 120) ?: null,
            'email'                  => $email,
            'status'                 => 'subscribed',
            'unsubscribe_token_hash' => hash('sha256', $unsubToken),
            'source'                 => $source,
            'ip_address'             => $ip,
            'confirmed_at'           => date('Y-m-d H:i:s'),
        ]);
        $this->record('newsletter', $id, $d, $ip, 'stored');
        return ['id' => $id, 'status' => 'new'];
    }

    /** @param array<string,mixed> $d */
    public function storeVolunteer(array $d, string $ip): int
    {
        [$first, $last] = $this->splitName((string) $d['full_name']);
        $days = array_values(array_filter(array_map('strval', (array) ($d['availability'] ?? [])), static fn (string $x): bool => in_array($x, ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'], true)));
        $details = [
            'occupation_type' => $d['occupation_type'] ?? null,
            'institution'     => $d['institution'] ?? null,
            'program'         => $d['program'] ?? null,
            'semester'        => $d['semester'] ?? null,
            'organisation'    => $d['organisation'] ?? null,
            'qualification'   => $d['qualification'] ?? null,
            'about'           => $d['about'] ?? null,
            'area'            => $d['area'] ?? null,
        ];
        $id = $this->db->insert('volunteers', [
            'first_name'          => $first,
            'last_name'           => $last,
            'guardian_name'       => $this->s($d['guardian_name'] ?? '', 120) ?: null,
            'email'               => mb_strtolower($this->s($d['email'], 191)),
            'phone'               => $this->s($d['phone'], 40),
            'alt_phone'           => $this->s($d['whatsapp'] ?? '', 40) ?: null,
            'date_of_birth'       => $d['date_of_birth'] ?: null,
            'gender'              => in_array($d['gender'] ?? '', ['male', 'female', 'other'], true) ? $d['gender'] : null,
            'cnic'                => $this->s($d['cnic'] ?? '', 20) ?: null,
            'address_line'        => trim($this->s($d['street'] ?? '', 200) . (isset($d['area']) && $d['area'] !== '' ? ', ' . $this->s($d['area'], 50) : '')) ?: null,
            'city'                => $this->s($d['city'] ?? '', 120) ?: null,
            'state'               => $this->s($d['state'] ?? '', 120) ?: null,
            'country'             => $this->s($d['country'] ?? '', 80) ?: null,
            'occupation'          => $this->s($d['occupation_type'] ?? '', 120) ?: null,
            'organisation'        => $this->s($d['organisation'] ?? ($d['institution'] ?? ''), 160) ?: null,
            'skills'              => $this->s($d['skills'] ?? '', 500) ?: null,
            'interests_json'      => json_encode(['days' => $days], JSON_UNESCAPED_UNICODE),
            'availability'        => $days === [] ? null : implode(', ', array_map(static fn ($x) => substr($x, 0, 3), $days)),
            'previous_experience' => $this->s($d['experience'] ?? '', 5000) ?: null,
            'motivation'          => $this->s($d['motivation'] ?? '', 5000) ?: null,
            'details_json'        => json_encode($details, JSON_UNESCAPED_UNICODE),
            'status'              => 'pending',
            'ip_address'          => $ip,
        ]);
        $this->record('volunteer', $id, $d, $ip, 'stored');
        $this->notify('New volunteer registration: ' . $d['full_name'], [
            'Name'     => $d['full_name'],
            'Email'    => $d['email'],
            'Mobile'   => $d['phone'],
            'WhatsApp' => $d['whatsapp'] ?? '',
            'City'     => $d['city'] ?? '',
            'Days'     => implode(', ', $days),
            'Why'      => $d['motivation'] ?? '',
        ], (string) $d['email']);
        return $id;
    }

    /** @param array<string,mixed> $d */
    public function storeCareerApplication(array $d, ?int $jobId, ?string $resumePath, ?string $resumeName, string $ip): int
    {
        $id = $this->db->insert('career_applications', [
            'job_id'               => $jobId,
            'position'             => $this->s($d['position'] ?? 'General application', 160),
            'name'                 => $this->s($d['name'], 120),
            'email'                => mb_strtolower($this->s($d['email'], 191)),
            'phone'                => $this->s($d['phone'] ?? '', 40) ?: null,
            'city'                 => $this->s($d['city'] ?? '', 120) ?: null,
            'cover_letter'         => $this->s($d['cover_letter'] ?? '', 8000) ?: null,
            'linkedin_url'         => $this->s($d['linkedin_url'] ?? '', 255) ?: null,
            'resume_path'          => $resumePath,
            'resume_original_name' => $resumeName,
            'status'               => 'new',
            'ip_address'           => $ip,
        ]);
        $this->record('career', $id, $d, $ip, 'stored');
        $this->notify('New job application: ' . ($d['position'] ?? 'General') . ' — ' . $d['name'], [
            'Position' => $d['position'] ?? 'General application',
            'Name'     => $d['name'],
            'Email'    => $d['email'],
            'Phone'    => $d['phone'] ?? '',
            'Resume'   => $resumeName ?? '—',
        ], (string) $d['email']);
        return $id;
    }

    // ----------------------------------------------------------- helpers

    /** @param array<string,mixed> $input */
    public function record(string $formKey, ?int $recordId, array $input, string $ip, string $outcome): void
    {
        try {
            $this->db->insert('form_submissions', [
                'form_key'    => $formKey,
                'record_id'   => $recordId,
                'fingerprint' => $this->fingerprint($formKey, $input),
                'ip_address'  => $ip,
                'outcome'     => $outcome,
            ]);
        } catch (\Throwable $e) {
            $this->logger->warning('form_submissions write failed', ['error' => $e->getMessage()]);
        }
    }

    /** @param array<string,string|array<string,mixed>> $rows */
    private function notify(string $subject, array $rows, string $replyTo = ''): void
    {
        $to = (string) $this->settings->get('site.notification_email', $this->settings->get('site.support_email', (string) Config::get('app.org.email', '')));
        if ($to === '') {
            return;
        }
        $html = '<table cellpadding="6" style="font-family:Arial,sans-serif;font-size:14px;border-collapse:collapse">';
        $text = '';
        foreach ($rows as $k => $v) {
            $v = is_array($v) ? implode(', ', $v) : (string) $v;
            $html .= '<tr><th align="left" style="border-bottom:1px solid #eee;color:#6B0F35">' . htmlspecialchars($k) . '</th><td style="border-bottom:1px solid #eee">' . nl2br(htmlspecialchars($v)) . '</td></tr>';
            $text .= $k . ': ' . $v . "\n";
        }
        $html .= '</table>';
        if ($replyTo !== '') {
            $html .= '<p style="color:#888;font-size:12px">Reply to: ' . htmlspecialchars($replyTo) . '</p>';
            $text .= "Reply to: {$replyTo}\n";
        }
        try {
            $this->mail->enqueueRaw($to, 'Al Marah Foundation', $subject, $html, $text, 'forms.notification');
        } catch (\Throwable $e) {
            $this->logger->warning('Form notification could not be queued', ['error' => $e->getMessage()]);
        }
    }

    /** @param array<string,mixed> $input */
    private function fingerprint(string $formKey, array $input): string
    {
        unset($input['_token'], $input['_t'], $input['website']);
        ksort($input);
        return hash('sha256', $formKey . '|' . json_encode($input, JSON_UNESCAPED_UNICODE));
    }

    private function s(mixed $v, int $max): string
    {
        $v = is_array($v) ? implode(', ', $v) : (string) $v;
        return mb_substr(trim(strip_tags($v)), 0, $max);
    }

    /** @return array{0:string,1:string} */
    private function splitName(string $full): array
    {
        $full = trim(preg_replace('/\s+/', ' ', $full) ?? $full);
        $pos = strrpos($full, ' ');
        if ($pos === false) {
            return [mb_substr($full, 0, 80), ''];
        }
        return [mb_substr(substr($full, 0, $pos), 0, 80), mb_substr(substr($full, $pos + 1), 0, 80)];
    }
}
