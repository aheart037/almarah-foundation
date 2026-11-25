<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use CURLFile;

/**
 * Wetarseel WhatsApp Business API: an approved template message with a PDF attached.
 *   POST {base}/send-message-with-file   (multipart: file, to, template_name, parameters)
 * Donors have not messaged the foundation in the last 24 hours, so a template is the only message WhatsApp allows.
 * Never retries on its own: a retry after a timeout could deliver the same document twice.
 */
final class WetarseelClient
{
    public function isConfigured(): bool
    {
        return trim(Config::string('whatsapp.wetarseel.api_key')) !== '';
    }

    public function template(): string
    {
        return Config::string('whatsapp.wetarseel.template', 'donation_pack');
    }

    /**
     * @param list<string> $parameters body variables {{1}}, {{2}}... of the template
     * @return array{ok:bool,id:?string,error:?string}
     */
    public function sendTemplateWithFile(string $to, string $template, array $parameters, string $filePath, string $fileName): array
    {
        if (!$this->isConfigured()) {
            return ['ok' => false, 'id' => null, 'error' => 'The WhatsApp API key is not configured.'];
        }
        if (!is_file($filePath)) {
            return ['ok' => false, 'id' => null, 'error' => 'The PDF file to send was not found.'];
        }
        $ch = curl_init(Config::string('whatsapp.wetarseel.base_url', 'https://app.wetarseel.ai/api/v1') . '/send-message-with-file');
        if ($ch === false) {
            return ['ok' => false, 'id' => null, 'error' => 'Could not start the request.'];
        }
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . trim(Config::string('whatsapp.wetarseel.api_key')), 'Accept: application/json'],
            CURLOPT_POSTFIELDS     => [
                'file'          => new CURLFile($filePath, 'application/pdf', $fileName),
                'to'            => $to,
                'template_name' => $template,
                'parameters'    => json_encode(self::cleanParameters($parameters), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => max(5, Config::int('whatsapp.wetarseel.timeout', 30)),
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        $body = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($body === false) {
            return ['ok' => false, 'id' => null, 'error' => 'Could not reach Wetarseel: ' . ($curlError !== '' ? $curlError : 'unknown error')];
        }
        $data = json_decode((string) $body, true);
        $data = is_array($data) ? $data : [];
        if ($status >= 200 && $status < 300 && ($data['success'] ?? true) !== false) {
            $id = $data['messageId'] ?? $data['message_id'] ?? null;
            return ['ok' => true, 'id' => is_scalar($id) ? (string) $id : null, 'error' => null];
        }
        $why = $data['message'] ?? $data['error'] ?? ('HTTP ' . $status);
        return ['ok' => false, 'id' => null, 'error' => mb_substr(is_scalar($why) ? (string) $why : ('HTTP ' . $status), 0, 300)];
    }

    /**
     * Donor phone -> digits-only international number ("923001234567"), or null when it is not usable.
     * Numbers written with "+" or "00" are international. Local numbers (0300..., 300...) are treated as Pakistani (+92).
     */
    public static function phone(string $raw): ?string
    {
        $raw = trim($raw);
        $international = str_starts_with($raw, '+');
        $digits = preg_replace('/\D+/', '', $raw) ?? '';
        if ($digits === '') {
            return null;
        }
        if (!$international) {
            if (str_starts_with($digits, '00')) {
                $digits = substr($digits, 2);
            } elseif (str_starts_with($digits, '0')) {
                $digits = '92' . substr($digits, 1);
            } elseif (strlen($digits) === 10 && $digits[0] === '3') {
                $digits = '92' . $digits;
            }
        }
        $len = strlen($digits);
        return $len >= 10 && $len <= 15 ? $digits : null;
    }

    /**
     * WhatsApp rejects template variables that contain line breaks, tabs or long runs of spaces, or that are empty.
     *
     * @param list<string> $parameters
     * @return list<string>
     */
    public static function cleanParameters(array $parameters): array
    {
        $out = [];
        foreach ($parameters as $p) {
            $p = trim((string) preg_replace('/\s+/u', ' ', (string) $p));
            $out[] = $p === '' ? '-' : mb_substr($p, 0, 200);
        }
        return $out;
    }
}
