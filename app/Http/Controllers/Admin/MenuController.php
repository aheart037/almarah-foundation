<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Http\Controllers\Controller;
use App\Services\AuditService;
use App\Services\AuthService;
use App\Services\NavigationService;

/** Header and footer navigation editor backed by NavigationService (settings JSON). */
final class MenuController extends Controller
{
    public function __construct(
        View $view,
        Session $session,
        Csrf $csrf,
        AuthService $auth,
        private NavigationService $navigation,
        private AuditService $audit
    ) {
        parent::__construct($view, $session, $csrf, $auth);
    }

    public function index(Request $request): Response
    {
        $location = (string) $request->string('location', 'header');
        if (!isset(NavigationService::LOCATIONS[$location])) {
            $location = 'header';
        }
        return $this->render('admin/menus/index', [
            'pageTitle' => 'Menus',
            'location'  => $location,
            'locations' => NavigationService::LOCATIONS,
            'tree'      => $this->navigation->tree($location),
            'maxDepth'  => NavigationService::MAX_DEPTH,
        ], 'layouts/admin');
    }

    public function save(Request $request): Response
    {
        $location = (string) $request->routeParam('location', '');
        if (!isset(NavigationService::LOCATIONS[$location])) {
            abort(404, 'Unknown menu.');
        }
        $tree = json_decode((string) $request->input('tree', '[]'), true);
        if (!is_array($tree)) {
            $this->flashError('The menu could not be read. Please try again.');
            return $this->redirect('/admin/menus?location=' . $location);
        }
        try {
            $this->navigation->save($location, $tree, (int) $this->auth->id());
        } catch (\InvalidArgumentException $e) {
            $this->flashError($e->getMessage());
            return $this->redirect('/admin/menus?location=' . $location);
        }
        $this->audit->log('menu.saved', 'navigation', $location, [], (int) $this->auth->id());
        $this->flashSuccess('Menu saved. It is live on the website now.');
        return $this->redirect('/admin/menus?location=' . $location);
    }

    public function reset(Request $request): Response
    {
        $location = (string) $request->routeParam('location', '');
        if (!isset(NavigationService::LOCATIONS[$location])) {
            abort(404, 'Unknown menu.');
        }
        $this->navigation->reset($location, (int) $this->auth->id());
        $this->audit->log('menu.reset', 'navigation', $location, [], (int) $this->auth->id());
        $this->flashSuccess('Menu restored to the built-in default.');
        return $this->redirect('/admin/menus?location=' . $location);
    }
}
