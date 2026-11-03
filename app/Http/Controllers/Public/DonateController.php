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
        private PaymentGatewayManager $gateways
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

        return $this->render('public/donate/index', [
            'pageTitle'       => 'Donate Now',
            'metaDescription' => 'Donate securely to Almarah Foundation by debit/credit card or bank transfer. Support orphaned children, hungry families and students across Pakistan.',
            'bodyClass'       => 'page-donate',
            'purposes'        => $purposes,
            'selected'        => $selected,
            'presets'         => array_map(static fn ($m): int => (int) ((int) $m / 100), $presets),
            'preAmount'       => is_numeric($amount) ? (int) $amount : null,
            'gateways'        => $this->gateways->choices(),
            'gatewayReady'    => $this->gateways->hasUsableGateway(),
            'currency'        => (string) Config::get('app.currency', 'PKR'),
            'minAmount'       => (int) ((int) Config::get('app.donations.min_minor', 10000) / 100),
            'maxAmount'       => (int) ((int) Config::get('app.donations.max_minor', 500000000) / 100),
            'banks'           => $this->cms->bankAccounts(),
        ]);
    }
}
