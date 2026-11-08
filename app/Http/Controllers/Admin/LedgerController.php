<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Http\Controllers\Controller;
use App\Services\AuthService;
use App\Services\DonationLedgerService;

/** Finance dashboard + central ledger. Read-only: nothing here edits or deletes a donation. */
final class LedgerController extends Controller
{
    public function __construct(View $view, Session $session, Csrf $csrf, AuthService $auth, private DonationLedgerService $ledger)
    {
        parent::__construct($view, $session, $csrf, $auth);
    }

    public function index(Request $request): Response
    {
        $filters = [];
        foreach (['q', 'channel', 'status', 'currency', 'date_from', 'date_to'] as $k) {
            $filters[$k] = trim((string) $request->input($k, ''));
        }
        $filters['bank_account_id'] = (int) $request->input('bank_account_id', 0);
        $page = max(1, (int) $request->input('page', 1));
        $result = $this->ledger->search($filters, $page, 25);
        return $this->render('admin/ledger/index', [
            'pageTitle' => 'Donation ledger', 'filters' => $filters, 'page' => $page,
            'lastPage' => max(1, (int) ceil($result['total'] / 25)), 'result' => $result, 'banks' => $this->ledger->banks(),
        ], 'layouts/admin');
    }
}
