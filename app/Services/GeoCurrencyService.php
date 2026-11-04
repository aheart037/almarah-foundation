<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Session;
use Closure;

/** Server-side, best-effort country hint. Never trusts forwarded-IP headers. */
final class GeoCurrencyService
{
    /** @param ?Closure(string):?array $lookup Test seam; production uses HTTPS ipwho.is. */
    public function __construct(private ?Closure $lookup = null)
    {
    }

    public function country(string $ip, Session $session): ?string
    {
        // Never send local addresses or an arbitrary user-supplied string to a provider.
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) return null;
        $cached = $session->get('donate.geo_country');
        if (is_array($cached) && ($cached['at'] ?? 0) > time() - 86400) {
            return is_string($cached['country'] ?? null) ? $cached['country'] : null;
        }
        $country = null;
        try {
            $data = $this->lookup !== null ? ($this->lookup)($ip) : $this->fetch($ip);
            $value = strtoupper((string) ($data['country_code'] ?? ''));
            if (($data['success'] ?? false) === true && preg_match('/^[A-Z]{2}$/D', $value)) $country = $value;
        } catch (\Throwable) {
            // A geolocation outage must never prevent a donation.
        }
        // Cache failures briefly, successes for one day; store country only, not IP.
        $session->set('donate.geo_country', ['country'=>$country, 'at'=>time() - ($country === null ? 85800 : 0)]);
        return $country;
    }

    private function fetch(string $ip): ?array
    {
        if (getenv('DONATION_GEO_DISABLE_REMOTE') === '1' || !function_exists('curl_init')) return null;
        $handle = curl_init('https://ipwho.is/' . $ip);
        if ($handle === false) return null;
        curl_setopt_array($handle, [
            CURLOPT_RETURNTRANSFER=>true, CURLOPT_CONNECTTIMEOUT=>2, CURLOPT_TIMEOUT=>3,
            CURLOPT_FOLLOWLOCATION=>false, CURLOPT_SSL_VERIFYPEER=>true, CURLOPT_SSL_VERIFYHOST=>2,
            CURLOPT_HTTPHEADER=>['Accept: application/json'], CURLOPT_USERAGENT=>'Almarah-Donation-Country/1.0',
        ]);
        try {
            $response = curl_exec($handle);
            if (curl_getinfo($handle, CURLINFO_HTTP_CODE) !== 200 || !is_string($response) || strlen($response) > 16384) return null;
            $data = json_decode($response, true);
            return is_array($data) ? $data : null;
        } finally {
            curl_close($handle);
        }
    }
}
