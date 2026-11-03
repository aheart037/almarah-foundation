<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Exceptions\HttpException;
use App\Services\AuthService;

/**
 * Permission-based guard for the unified admin panel.
 *
 * Register on a route as "PermissionMiddleware::class . ':content.manage'".
 * The user must be signed in, hold a staff role, and have the named permission
 * (super_admin always has every permission). Enforced server-side — menus that
 * hide a link are a courtesy, not the security boundary.
 */
final class PermissionMiddleware implements MiddlewareInterface
{
    public function __construct(private AuthService $auth, private ?string $permission = null)
    {
    }

    public function withArgument(string $argument): self
    {
        return new self($this->auth, trim($argument));
    }

    public function handle(Request $request, callable $next): Response
    {
        if (!$this->auth->check()) {
            throw new HttpException(302, '', ['Location' => '/login']);
        }
        if (!$this->auth->isStaff()) {
            throw new HttpException(403, 'You do not have permission to access that area.');
        }
        if ($this->permission !== null && $this->permission !== '' && !$this->auth->can($this->permission)) {
            throw new HttpException(403, 'Your role does not include permission to do that (' . $this->permission . ').');
        }
        return $next($request);
    }
}
