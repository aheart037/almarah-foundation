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

/**
 * Staff-recorded (offline) donations. Finance (donations.verify) sees every entry;
 * a staff member who can only record sees just their own entries.
 */
final class OfflineEntryController extends Controller
{
    public function __construct(View $view, Session $session, Csrf $csrf, AuthService $auth, private DonationLedgerService $ledger)
    {
        parent::__construct($view, $session, $csrf, $auth);
    }

    public function index(Request $request): Response
    {
        $filters = ['channel' => 'offline'];
        foreach (['q', 'status', 'date_from', 'date_to'] as $k) {
            $filters[$k] = trim((string) $request->input($k, ''));
        }
        $all = $this->auth->can('donations.verify') || $this->auth->can('donations.view');
        if (!$all) {
            $filters['recorded_by'] = (int) $this->auth->id(); // record-only staff: own entries only
        }
        $page = max(1, (int) $request->input('page', 1));
        $result = $this->ledger->search($filters, $page, 25);
        return $this->render('admin/offline/index', [
            'pageTitle' => $all ? 'Offline donation entries' : 'My offline entries', 'filters' => $filters, 'showAll' => $all,
            'page' => $page, 'lastPage' => max(1, (int) ceil($result['total'] / 25)), 'result' => $result,
        ], 'layouts/admin');
    }
}
