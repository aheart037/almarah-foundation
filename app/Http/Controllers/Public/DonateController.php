<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Core\Config;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Http\Controllers\Controller;
use App\Payments\PaymentGatewayManager;
use App\Services\AuthService;
use App\Services\CmsService;
use App\Services\DonationCurrencies;
use App\Services\DonationExchangeRates;
use App\Services\GeoCurrencyService;

/**
 * The website's single /donate page: purpose selector + amount + donor
 * details + payment method. Submission goes to fundraise-submit.php, the
 * unchanged gateway hand-off from the fundraising platform (target =
 * purpose slug, type = purpose), so the payment code path is identical.
 */
final class DonateController extends Controller
{
    public function __construct(
        View $view,
        Session $session,
        Csrf $csrf,
        AuthService $auth,
        private CmsService $cms,
        private PaymentGatewayManager $gateways,
        private GeoCurrencyService $geo,
        private DonationExchangeRates $rates
    ) {
        parent::__construct($view, $session, $csrf, $auth);
    }

    public function index(Request $request): Response
    {
        $purposes = $this->cms->donationPurposes();
        $selected = (string) $request->input('purpose', old('target', ''));
        if ($selected === '' || $this->cms->donationPurpose($selected) === null) {
            $selected = $purposes[0]['slug'] ?? 'general-fund';
        }

        $presets = (array) Config::get('app.donations.presets_minor', [100000, 250000, 500000, 1000000, 2500000, 5000000]);
        $amount = $request->input('amount');
        $banks = $this->cms->bankAccounts();
        $siteCurrency = strtoupper((string) Config::get('app.currency', 'PKR'));
        $currencies = DonationCurrencies::payable($banks, $siteCurrency);
        $this->session->start();
        // Manual selection wins over IP. A query parameter also supports
        // no-JavaScript selection and a bookmarkable currency preference.
        $explicit = $request->string('currency');
        $oldCurrency = old('currency');
        $manual = $this->session->get('donate.manual_currency');
        if ($explicit !== '') {
            $selectedCurrency = strtoupper($explicit);
            if (in_array($selectedCurrency, $currencies, true)) {
                $this->session->set('donate.manual_currency', $selectedCurrency);
            } else $selectedCurrency = $siteCurrency;
            $currencySource = 'manual';
        } elseif ($oldCurrency !== '' && in_array(strtoupper($oldCurrency), $currencies, true)) {
            $selectedCurrency = strtoupper($oldCurrency);
            $currencySource = 'form';
        } elseif (is_string($manual) && in_array($manual, $currencies, true)) {
            $selectedCurrency = $manual;
            $currencySource = 'manual';
        } else {
            $suggested = DonationCurrencies::forCountry($this->geo->country($request->ip(), $this->session));
            $selectedCurrency = in_array($suggested, $currencies, true) ? $suggested : $siteCurrency;
            $currencySource = $selectedCurrency !== $siteCurrency ? 'location' : 'default';
        }

        $rateSnapshot = count($currencies) > 1 ? $this->rates->snapshot() : null;
        $estimates = [];
        foreach ($currencies as $code) {
            foreach ($presets as $minor) {
                $value = (int) $minor / 100;
                $estimate = $code === $siteCurrency
                    ? ['amount'=>$value]
                    : $this->rates->estimate($value, $siteCurrency, $code, $rateSnapshot);
                if ($estimate !== null) $estimates[$code][(string) $value] = $estimate['amount'];
            }
        }

        // Several open tabs may contain different forms. Keep a small, expiring
        // set of one-use tokens without invalidating an earlier tab on refresh.
        $nonces = (array) $this->session->get('donate.form_nonces', []);
        $nonces = array_filter($nonces, static fn ($at): bool => is_int($at) && $at > time() - 3600);
        $nonce = bin2hex(random_bytes(16));
        $nonces[$nonce] = time();
        $this->session->set('donate.form_nonces', array_slice($nonces, -8, null, true));

        return $this->render('public/donate/index', [
            'pageTitle'       => 'Donate Now',
            'metaDescription' => 'Donate securely to Almarah Foundation by debit/credit card or bank transfer. Support orphaned children, hungry families and students across Pakistan.',
            'bodyClass'       => 'page-donate',
            'purposes'        => $purposes,
            'selected'        => $selected,
            'presets'         => array_map(static fn ($m): int => (int) ((int) $m / 100), $presets),
            'preAmount'       => is_numeric($amount) ? (int) $amount : null,
            'gateways'        => $siteCurrency === 'PKR' ? $this->gateways->choices() : [],
            'gatewayReady'    => $siteCurrency === 'PKR' && $this->gateways->hasUsableGateway(),
            'currency'        => $siteCurrency,
            'selectedCurrency'=> $selectedCurrency,
            'currencySource' => $currencySource,
            'currencyOptions'=> DonationCurrencies::OPTIONS,
            'rateEstimates'  => $estimates,
            'rateUpdated'    => $rateSnapshot['updated'] ?? null,
            'rateStale'      => $rateSnapshot['stale'] ?? false,
            'currencies'     => $currencies,
            'donationNonce'  => $nonce,
            'minAmount'       => (int) ((int) Config::get('app.donations.min_minor', 10000) / 100),
            'maxAmount'       => (int) ((int) Config::get('app.donations.max_minor', 500000000) / 100),
            'banks'           => $banks,
        ]);
    }

    /** Save an explicit donor choice; Geo-IP cannot override it later. */
    public function preference(Request $request): Response
    {
        $currency = strtoupper($request->string('currency'));
        if (!in_array($currency, DonationCurrencies::payable($this->cms->bankAccounts(), (string) Config::get('app.currency', 'PKR')), true)) {
            return Response::json(['success'=>false, 'message'=>'This currency has no published payment method.'], 422);
        }
        $this->session->set('donate.manual_currency', $currency);
        return Response::json(['success'=>true, 'currency'=>$currency])->withHeader('Cache-Control', 'private, no-store');
    }

    /** End-user donation estimate, not a provider price or a rates API. */
    public function estimate(Request $request): Response
    {
        $limiter = app(\App\Core\RateLimiter::class);
        $key = 'donate-estimate:' . $request->ip();
        if ($limiter->tooManyAttempts($key, 60)) return Response::json(['success'=>false, 'message'=>'Too many estimate requests. Please try later.'], 429);
        $limiter->hit($key, 60);
        $allowed = DonationCurrencies::payable($this->cms->bankAccounts(), (string) Config::get('app.currency', 'PKR'));
        $from = strtoupper($request->string('from'));
        $to = strtoupper($request->string('to'));
        $raw = $request->string('amount');
        if (!in_array($from, $allowed, true) || !in_array($to, $allowed, true)
            || !preg_match('/^(?:[0-9]{1,7})(?:\.[0-9]{1,2})?$/D', $raw)) {
            return Response::json(['success'=>false, 'message'=>'Unsupported currency or amount.'], 422);
        }
        $result = $this->rates->estimate((float) $raw, $from, $to);
        if ($result === null) {
            return Response::json(['success'=>false, 'message'=>'Exchange-rate estimate unavailable. Enter an amount in the selected currency manually.'], 503);
        }
        return Response::json(['success'=>true, 'estimate'=>number_format($result['amount'], 2, '.', ''),
            'currency'=>$to, 'updated'=>gmdate('Y-m-d H:i', $result['updated']) . ' UTC',
            'stale'=>$result['stale'], 'notice'=>'Estimate only; not a quoted payment or exchange rate.'])
            ->withHeader('Cache-Control', 'private, no-store');
    }

}
