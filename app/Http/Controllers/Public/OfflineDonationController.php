<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Http\Controllers\Controller;
use App\Services\AuthService;
use App\Services\DonationReviewService;
use App\Services\DonationCurrencies;
use App\Services\CmsService;
use InvalidArgumentException;

final class OfflineDonationController extends Controller
{
    public function __construct(View $view, Session $session, Csrf $csrf, AuthService $auth, private DonationReviewService $review, private CmsService $cms)
    {
        parent::__construct($view, $session, $csrf, $auth);
    }

    /** Published accounts determine which currencies the public transfer page offers. */
    private function currencies(): array
    {
        $allowed = array_keys(DonationCurrencies::OPTIONS);
        $currencies = ['PKR']; // Keep reporting legacy domestic transfers possible.
        foreach ($this->cms->bankAccounts() as $bank) {
            $currency = strtoupper((string) ($bank['currency'] ?? ''));
            if (in_array($currency, $allowed, true) && !in_array($currency, $currencies, true)) $currencies[] = $currency;
        }
        return $currencies;
    }

    public function form(Request $request): Response
    {
        $currencies = $this->currencies();
        $selected = strtoupper((string) $request->input('currency', 'PKR'));
        if (!in_array($selected, $currencies, true)) $selected = 'PKR';
        $nonce = bin2hex(random_bytes(16));
        $nonces = (array) $this->session->get('donate.transfer_nonces', []);
        $nonces = array_filter($nonces, static fn ($at): bool => is_int($at) && $at > time() - 3600);
        $nonces[$nonce] = time();
        $this->session->set('donate.transfer_nonces', array_slice($nonces, -8, null, true));
        return $this->render('public/donate/transfer', [
            'pageTitle' => 'Report a transfer', 'bodyClass' => 'page-donate',
            'currencies' => $currencies, 'currencyOptions' => DonationCurrencies::OPTIONS, 'selectedCurrency' => $selected, 'transferNonce' => $nonce,
        ]);
    }

    public function submit(Request $request): Response
    {
        // DB-backed throttle, shared with other public forms.
        $limiter = app(\App\Core\RateLimiter::class);
        $key = 'offline-donation:' . $request->ip();
        if ($limiter->tooManyAttempts($key, 5)) return Response::text('Please try again later.', 429);
        $limiter->hit($key, 60);
        // Honeypot avoids creating junk donations without punishing normal donors.
        if ($request->input('website', '') !== '') return Response::text('Unable to submit.', 400);
        $currency = strtoupper((string) $request->input('currency', 'PKR'));
        if (!in_array($currency, $this->currencies(), true)) {
            return $this->flashFormStateTo(['form' => 'No published account supports this currency.'], $request->all(), '/donate/transfer');
        }
        // Consume only on a real submission; a replay must not create a
        // second pending review record (even if the bank reference is blank).
        $nonce = $request->input('transfer_attempt');
        $nonces = (array) $this->session->get('donate.transfer_nonces', []);
        if (!is_string($nonce) || !preg_match('/^[a-f0-9]{32}$/D', $nonce)
            || !isset($nonces[$nonce]) || !is_int($nonces[$nonce]) || $nonces[$nonce] < time() - 3600) {
            return $this->flashFormStateTo(['form' => 'This transfer report was already sent or has expired. Check its reference before trying again.'], [], '/donate/transfer');
        }
        unset($nonces[$nonce]);
        $this->session->set('donate.transfer_nonces', $nonces);
        try {
            $result = $this->review->submit($request->all(), $request->file('payment_proof'));
        } catch (InvalidArgumentException $e) {
            return $this->flashFormStateTo(['form' => $e->getMessage()], $request->all(), '/donate/transfer');
        }
        $this->flashSuccess('Your donation has been submitted. Reference: ' . $result['reference'] . '. No verified receipt will be issued until staff confirmation.');
        return $this->redirect('/donate/transfer');
    }
}
