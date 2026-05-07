<?php

// app/Http/Middleware/EnsureCompanyModuleEnabled.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanyModuleEnabled
{
    public function handle(Request $request, Closure $next, string $moduleKey): Response
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        /** @var \App\Models\Company|null $company */
        $company = view()->shared('currentCompany');

        if (! $company) {
            abort(403, 'Tenant context is missing.');
        }

        if (! $company->hasModule($moduleKey)) {
            abort(403, "Module [{$moduleKey}] is not enabled for {$company->name}.");
        }

        return $next($request);
    }
}
