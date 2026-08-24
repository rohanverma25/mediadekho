<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsStaff
{
    /**
     * Restricts access to the admin panel to Super Admin / Admin roles.
     * Customer roles (Retail/B2C/B2B/Enterprise) authenticate separately
     * for the storefront/API and have no business inside /admin.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasAnyRole(['Super Admin', 'Admin'])) {
            abort(403, 'You do not have access to the admin panel.');
        }

        return $next($request);
    }
}
