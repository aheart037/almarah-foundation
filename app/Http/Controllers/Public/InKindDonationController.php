<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Core\Csrf;
use App\Core\RateLimiter;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Http\Controllers\Controller;
use App\Services\AuthService;
use App\Services\InKindDonationService;
use InvalidArgumentException;
use Throwable;

/** Public "Donate goods / in-kind" form. Submissions wait for finance verification (see Admin\InKindController). */
final class InKindDonationController extends Controller
{
    public function __construct(View $view, Session $session, Csrf $csrf, AuthService $auth, private InKindDonationService $inkind)
    {
        parent::__construct($view, $session, $csrf, $auth);
    }

    public function form(Request $request): Response
    {
        $nonce = bin2hex(random_bytes(16));
        $nonces = (array) $this->session->get('donate.goods_nonces', []);
        $nonces = array_filter($nonces, static fn ($at): bool => is_int($at) && $at > time() - 3600);
        $nonces[$nonce] = time();
        $this->session->set('donate.goods_nonces', array_slice($nonces, -8, null, true));
        $ref = $this->session->get('donate.goods_reference');
        $this->session->forget('donate.goods_reference'); // shown once

        return $this->render('public/donate/goods', [
            'pageTitle' => 'Donate goods / in-kind', 'bodyClass' => 'page-donate',
            'goodsNonce' => $nonce, 'submittedRef' => is_string($ref) ? $ref : null,
            'categories' => InKindDonationService::CATEGORIES, 'maxItems' => InKindDonationService::MAX_ITEMS,
        ]);
    }

    public function submit(Request $request): Response
    {
        $limiter = app(RateLimiter::class);
        $key = 'inkind-donation:' . $request->ip();
        if ($limiter->tooManyAttempts($key, 5)) {
            return Response::text('Please try again later.', 429);
        }
        $limiter->hit($key, 60);
        if ($request->input('website', '') !== '') { // honeypot
            return Response::text('Unable to submit.', 400);
        }
        $to = base_url('donate/goods');
        $nonce = $request->input('goods_attempt');
        $nonces = (array) $this->session->get('donate.goods_nonces', []);
        if (!is_string($nonce) || !preg_match('/^[a-f0-9]{32}$/D', $nonce)
            || !isset($nonces[$nonce]) || !is_int($nonces[$nonce]) || $nonces[$nonce] < time() - 3600) {
            return $this->flashFormStateTo(['form' => 'This form was already sent or has expired. Please check your reference or try again.'], [], $to);
        }
        unset($nonces[$nonce]);
        $this->session->set('donate.goods_nonces', $nonces);

        $photos = [];
        for ($i = 1; $i <= InKindDonationService::MAX_PHOTOS; $i++) {
            $photos['photo_' . $i] = $request->file('photo_' . $i);
        }
        try {
            $result = $this->inkind->submit($request->all(), $photos);
        } catch (InvalidArgumentException $e) {
            return $this->flashFormStateTo(['form' => $e->getMessage()], $request->all(), $to);
        } catch (Throwable $e) {
            app(\App\Core\Logger::class)->error('In-kind submission failed', ['error' => $e->getMessage()]);
            return $this->flashFormStateTo(['form' => 'Sorry, we could not save your donation. Please try again in a moment.'], $request->all(), $to);
        }
        $this->session->set('donate.goods_reference', $result['reference']);
        return $this->redirect($to);
    }
}
