<?php

declare(strict_types=1);

namespace App\Services;

/** Payable currency metadata, independent of Geo-IP or an exchange-rate feed. */
final class DonationCurrencies
{
    public const OPTIONS = [
        'PKR' => ['name' => 'Pakistani Rupee', 'symbol' => '₨', 'flag' => '🇵🇰'],
        'USD' => ['name' => 'US Dollar', 'symbol' => '$', 'flag' => '🇺🇸'],
        'GBP' => ['name' => 'British Pound', 'symbol' => '£', 'flag' => '🇬🇧'],
        'EUR' => ['name' => 'Euro', 'symbol' => '€', 'flag' => '🇪🇺'],
        'AED' => ['name' => 'UAE Dirham', 'symbol' => 'د.إ', 'flag' => '🇦🇪'],
        'SAR' => ['name' => 'Saudi Riyal', 'symbol' => '﷼', 'flag' => '🇸🇦'],
        'CAD' => ['name' => 'Canadian Dollar', 'symbol' => 'C$', 'flag' => '🇨🇦'],
        'AUD' => ['name' => 'Australian Dollar', 'symbol' => 'A$', 'flag' => '🇦🇺'],
    ];

    /** Only expose currencies with an actual published payment route. */
    public static function payable(array $banks, string $siteCurrency = 'PKR'): array
    {
        $out = [$siteCurrency];
        foreach ($banks as $bank) {
            $code = strtoupper((string) ($bank['currency'] ?? ''));
            if (isset(self::OPTIONS[$code]) && !in_array($code, $out, true)) $out[] = $code;
        }
        return $out;
    }

    public static function forCountry(?string $country): string
    {
        if ($country === null) return 'PKR';
        $country = strtoupper($country);
        if ($country === 'PK') return 'PKR';
        if (in_array($country, ['AT','BE','CY','EE','FI','FR','DE','GR','IE','IT','LV','LT','LU','MT','NL','PT','SK','SI','ES','HR'], true)) return 'EUR';
        return [
            'US'=>'USD', 'GB'=>'GBP', 'AE'=>'AED', 'SA'=>'SAR', 'CA'=>'CAD', 'AU'=>'AUD',
        ][$country] ?? 'PKR';
    }
}
