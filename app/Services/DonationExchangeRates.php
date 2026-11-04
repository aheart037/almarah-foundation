<?php

declare(strict_types=1);

namespace App\Services;

use Closure;

/** Informational estimates ONLY. Never used to set a provider charge or a ledger amount. */
final class DonationExchangeRates
{
    private string $file;

    /** @param ?Closure():?array $fetch Injectable provider response for deterministic tests. */
    public function __construct(private ?Closure $fetch = null, ?string $cacheFile = null)
    {
        $this->file = $cacheFile ?? ((string) (getenv('DONATION_RATES_CACHE_FILE') ?: app()->basePath() . '/storage/cache/donation_rates_pkr.json'));
    }

    /** @return array{updated:int,stale:bool,rates:array<string,float>}|null */
    public function snapshot(): ?array
    {
        $handle = @fopen($this->file, 'c+');
        if ($handle === false || !flock($handle, LOCK_EX)) {
            if (is_resource($handle)) fclose($handle);
            return null; // No writable cache: do not hammer the free API.
        }
        try {
            rewind($handle);
            $data = json_decode((string) stream_get_contents($handle), true);
            if (!is_array($data)) $data = [];
            $now = time();
            $usable = $this->usable($data, $now);
            if ($usable !== null && ($data['next'] ?? 0) > $now) return $usable;
            if (($data['retry_after'] ?? 0) <= $now) {
                try {
                    $response = $this->fetch !== null ? ($this->fetch)() : $this->fetchRemote();
                    $fresh = $this->validate($response, $now);
                    if ($fresh !== null) {
                        $data = $fresh;
                    } else {
                        $data['retry_after'] = $now + 600;
                    }
                } catch (\Throwable) {
                    $data['retry_after'] = $now + 600;
                }
                rewind($handle);
                ftruncate($handle, 0);
                fwrite($handle, json_encode($data, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
                fflush($handle);
            }
            return $this->usable($data, $now);
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    /** @return array{amount:float,updated:int,stale:bool}|null */
    public function estimate(float $amount, string $from, string $to, ?array $snapshot = null): ?array
    {
        if (!is_finite($amount) || $amount <= 0 || $amount > 5000000
            || !isset(DonationCurrencies::OPTIONS[$from], DonationCurrencies::OPTIONS[$to])) return null;
        if ($from === $to) return ['amount'=>round($amount, 2), 'updated'=>time(), 'stale'=>false];
        $snapshot ??= $this->snapshot();
        if ($snapshot === null || !isset($snapshot['rates'][$from], $snapshot['rates'][$to])
            || $snapshot['rates'][$from] <= 0 || $snapshot['rates'][$to] <= 0) return null;
        return ['amount'=>round($amount / $snapshot['rates'][$from] * $snapshot['rates'][$to], 2),
            'updated'=>$snapshot['updated'], 'stale'=>$snapshot['stale']];
    }

    private function usable(array $data, int $now): ?array
    {
        $at = $data['updated'] ?? 0;
        if (!is_int($at) || $at < $now - 172800 || $at > $now + 300 || !is_array($data['rates'] ?? null)) return null;
        $rates = $data['rates'];
        if (($rates['PKR'] ?? 0) != 1) return null;
        return ['updated'=>$at, 'stale'=>($data['next'] ?? 0) <= $now, 'rates'=>$rates];
    }

    private function validate(?array $response, int $now): ?array
    {
        if (($response['result'] ?? '') !== 'success' || ($response['base_code'] ?? '') !== 'PKR'
            || !is_array($response['rates'] ?? null)) return null;
        $at = $response['time_last_update_unix'] ?? null;
        $next = $response['time_next_update_unix'] ?? null;
        if (!is_int($at) || $at > $now + 300 || $at < $now - 172800
            || !is_int($next) || $next < $at || $next > $now + 172800) return null;
        $rates = [];
        foreach (array_keys(DonationCurrencies::OPTIONS) as $code) {
            $value = $response['rates'][$code] ?? null;
            if ((is_float($value) || is_int($value)) && $value > 0 && is_finite((float) $value)) $rates[$code] = (float) $value;
        }
        if (($rates['PKR'] ?? 0) != 1) return null;
        return ['updated'=>$at, 'next'=>max($now + 3600, $next), 'rates'=>$rates, 'retry_after'=>0];
    }

    private function fetchRemote(): ?array
    {
        if (getenv('DONATION_RATES_DISABLE_REMOTE') === '1' || !function_exists('curl_init')) return null;
        $handle = curl_init('https://open.er-api.com/v6/latest/PKR');
        if ($handle === false) return null;
        curl_setopt_array($handle, [
            CURLOPT_RETURNTRANSFER=>true, CURLOPT_CONNECTTIMEOUT=>2, CURLOPT_TIMEOUT=>4,
            CURLOPT_FOLLOWLOCATION=>false, CURLOPT_SSL_VERIFYPEER=>true, CURLOPT_SSL_VERIFYHOST=>2,
            CURLOPT_HTTPHEADER=>['Accept: application/json'], CURLOPT_USERAGENT=>'Almarah-Donation-Estimates/1.0',
        ]);
        try {
            $json = curl_exec($handle);
            if (curl_getinfo($handle, CURLINFO_HTTP_CODE) !== 200 || !is_string($json) || strlen($json) > 65536) return null;
            $result = json_decode($json, true);
            return is_array($result) ? $result : null;
        } finally {
            curl_close($handle);
        }
    }
}
