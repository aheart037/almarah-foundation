<?php

declare(strict_types=1);

/**
 * Public routes for almarah.org.
 *
 * URLs mirror the WordPress site one-to-one (analysis/url_inventory.csv) so
 * nothing indexed is lost. The payment routes are untouched from the
 * fundraising platform — the gateways post back to exactly these paths.
 */

use App\Core\Router;
use App\Http\Controllers\Public\AuthController;
use App\Http\Controllers\Public\DonateController;
use App\Http\Controllers\Public\OfflineDonationController;
use App\Http\Controllers\Public\DonationStatusController;
use App\Http\Controllers\Public\FormsController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\PageController;
use App\Http\Controllers\Public\PaymentController;
use App\Http\Controllers\Public\SeoController;
use App\Http\Controllers\Public\SetupController;
use App\Http\Middleware\CsrfMiddleware;
use App\Http\Middleware\GuestMiddleware;

return static function (Router $router): void {
    // --- first-time setup (self-locking once an administrator exists) -------
    $router->get('/setup', [SetupController::class, 'index'], [], 'setup');
    $router->post('/setup', [SetupController::class, 'run'], [], 'setup.run');

    // --- search-engine files -------------------------------------------------
    $router->get('/robots.txt', [SeoController::class, 'robots'], [], 'seo.robots');
    $router->get('/sitemap.xml', [SeoController::class, 'sitemap'], [], 'seo.sitemap');

    // --- website -----------------------------------------------------------
    $router->get('/', [HomeController::class, 'index'], [], 'home');
    $router->get('/donate', [DonateController::class, 'index'], [], 'donate');
    $router->post('/donate/currency', [DonateController::class, 'preference'], [CsrfMiddleware::class], 'donate.currency');
    $router->post('/donate/estimate', [DonateController::class, 'estimate'], [CsrfMiddleware::class], 'donate.estimate');
    $router->get('/donate/transfer', [OfflineDonationController::class, 'form'], [], 'donate.transfer');
    $router->post('/donate/transfer', [OfflineDonationController::class, 'submit'], [CsrfMiddleware::class], 'donate.transfer.submit');

    // Public forms (JSON for the site's AJAX handler, redirect + flash otherwise).
    $router->post('/forms/contact', [FormsController::class, 'contact'], [CsrfMiddleware::class], 'forms.contact');
    $router->post('/forms/newsletter', [FormsController::class, 'newsletter'], [CsrfMiddleware::class], 'forms.newsletter');
    $router->post('/forms/volunteer', [FormsController::class, 'volunteer'], [CsrfMiddleware::class], 'forms.volunteer');
    $router->post('/forms/career', [FormsController::class, 'careerApply'], [CsrfMiddleware::class], 'forms.career');

    // Legacy WordPress addresses that moved (approved 30 Oct 2026).
    foreach (['/qurbani' => '/donate?purpose=qurbani', '/faq' => '/about', '/product/donation' => '/donate', '/product/qurbani' => '/donate?purpose=qurbani', '/shop' => '/donate', '/cart' => '/donate', '/checkout' => '/donate', '/compassionate-haven' => 'https://almarahhaven.org/'] as $from => $to) {
        $router->get($from, static fn () => \App\Core\Response::redirect(str_starts_with($to, 'http') ? $to : base_url(ltrim($to, '/')), 301));
    }

    // --- payment gateway returns (DO NOT CHANGE: configured at the banks) ---
    // Meezan returns through GET. Per the EPG guide, Etisalat POSTs the
    // TransactionID to the exact ReturnPath sent during Registration.
    $router->get('/payments/meezan/return', [PaymentController::class, 'meezanReturn'], [], 'payments.meezan.return');
    $router->post('/payments/meezan/callback', [PaymentController::class, 'meezanReturn'], [], 'payments.meezan.callback');
    $router->get('/payments/etisalat/return', [PaymentController::class, 'etisalatReturn'], [], 'payments.etisalat.return');
    $router->post('/payments/etisalat/return', [PaymentController::class, 'etisalatReturn'], [], 'payments.etisalat.return.post');
    $router->post('/payments/etisalat/callback', [PaymentController::class, 'etisalatReturn'], [], 'payments.etisalat.callback');
    $router->get('/payments/etisalat/bridge/{txn}', [PaymentController::class, 'etisalatBridge'], [], 'payments.etisalat.bridge');

    $router->get('/donation/success', [DonationStatusController::class, 'success'], [], 'donation.success');
    $router->get('/donation/processing', [DonationStatusController::class, 'processing'], [], 'donation.processing');
    $router->get('/donation/failed', [DonationStatusController::class, 'failed'], [], 'donation.failed');
    $router->get('/donation/{reference}', [DonationStatusController::class, 'show'], [], 'donation.show');

    // --- staff authentication (no public self-registration on the main site)
    $router->get('/login', [AuthController::class, 'showLogin'], [GuestMiddleware::class], 'login');
    $router->post('/login', [AuthController::class, 'login'], [GuestMiddleware::class, CsrfMiddleware::class], 'login.attempt');
    $router->post('/logout', [AuthController::class, 'logout'], [CsrfMiddleware::class], 'logout');
    $router->get('/forgot-password', [AuthController::class, 'showForgot'], [GuestMiddleware::class], 'password.request');
    $router->post('/forgot-password', [AuthController::class, 'sendReset'], [GuestMiddleware::class, CsrfMiddleware::class], 'password.email');
    $router->get('/reset-password/{token}', [AuthController::class, 'showReset'], [GuestMiddleware::class], 'password.reset');
    $router->post('/reset-password', [AuthController::class, 'reset'], [GuestMiddleware::class, CsrfMiddleware::class], 'password.update');
    $router->get('/verify-email/{token}', [AuthController::class, 'verify'], [], 'verification.verify');
    $router->get('/verify-email', [AuthController::class, 'verifyNotice'], [], 'verification.notice');
    $router->post('/verify-email', [AuthController::class, 'resendVerification'], [CsrfMiddleware::class], 'verification.resend');

    // --- content pages (catch-all, last). PageController::PAGES is the map.
    $router->get('/{slug}', [PageController::class, 'show'], [], 'page');
};
