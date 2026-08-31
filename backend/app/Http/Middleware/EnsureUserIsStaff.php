<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsStaff
{
    /**
     * Restricts access to the admin panel to any account that actually has
     * at least one admin-panel permission — rather than hardcoding the
     * "Super Admin"/"Admin" role names, this checks permissions directly,
     * so a custom role created via the Roles admin module (e.g. "Content
     * Editor" with just blog.edit) works immediately without this
     * middleware needing to know it exists. Customer roles (Retail/B2C/
     * B2B/Enterprise) are seeded with zero permissions by design — see
     * RolesAndPermissionsSeeder — so they're excluded automatically rather
     * than by name.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->getAllPermissions()->isEmpty()) {
            abort(403, 'You do not have access to the admin panel.');
        }

        return $next($request);
    }
}
