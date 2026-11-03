<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Core\Csrf;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Http\Controllers\Controller;
use App\Services\AuthService;
use App\Services\CmsService;

/** Home page: admin-managed slider + the migrated home sections. */
final class HomeController extends Controller
{
    public function __construct(
        View $view,
        Session $session,
        Csrf $csrf,
        AuthService $auth,
        private CmsService $cms
    ) {
        parent::__construct($view, $session, $csrf, $auth);
    }

    public function index(): Response
    {
        return $this->render('public/home', [
            'pageTitle'       => 'Every Child Deserves a Place to Call "Apna Ghar"',
            'metaDescription' => 'Almarah Foundation cares for orphaned and vulnerable children through Apna Ghar homes, feeds the hungry, educates underprivileged students and shelters the homeless across Pakistan.',
            'bodyClass'       => 'page-home',
            'slides'          => $this->cms->activeSlides(),
        ]);
    }
}
