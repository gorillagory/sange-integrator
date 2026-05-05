<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

class AllowSuperAdminOrTenantRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        $currentCompany = view()->shared('currentCompany');
        $companyId = $currentCompany->id ?? null;

        if (! $companyId) {
            abort(403, 'Tenant context is missing.');
        }

        app(PermissionRegistrar::class)->setPermissionsTeamId($companyId);

        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return $next($request);
            }
        }

        abort(403, 'User does not have any of the necessary access rights.');
    }
}
