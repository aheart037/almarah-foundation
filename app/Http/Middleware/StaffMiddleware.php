<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\AuthService;

/** Any staff role may pass; use PermissionMiddleware for finer control. */
final class StaffMiddleware extends RoleMiddleware
{
    public function __construct(AuthService $auth)
    {
        parent::__construct($auth, AuthService::STAFF_ROLES);
    }
}
