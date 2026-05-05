<?php

namespace App\Http\Middleware;

use App\Models\Company;
use App\Services\AuthRedirectService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $subdomain = $request->route('subdomain');

        /** @var \App\Models\Company|null $company */
        $company = Company::query()
            ->where('subdomain', $subdomain)
            ->where('is_active', true)
            ->first();

        if (! $company) {
            abort(404, 'Company environment not found or inactive.');
        }

        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (! $user) {
            abort(401);
        }

        app(PermissionRegistrar::class)->setPermissionsTeamId(0);

        $isSuperAdmin = $user->isSuperAdmin();

        $hasMembership = DB::connection('control')
            ->table('company_user')
            ->where('user_id', $user->id)
            ->where('company_id', $company->id)
            ->exists();

        if (! $isSuperAdmin && ! $hasMembership) {
            /** @var \App\Services\AuthRedirectService $redirectService */
            $redirectService = app(AuthRedirectService::class);

            $targetUrl = $redirectService->redirectForDeniedTenantAccess($user, $company);

            return redirect($targetUrl)
                ->with('error', "You do not have security clearance for {$company->name}.");
        }

        Config::set('database.connections.tenant.database', $company->db_name);
        DB::purge('tenant');
        DB::reconnect('tenant');
        Config::set('database.default', 'tenant');

        app(PermissionRegistrar::class)->setPermissionsTeamId($company->id);

        view()->share('currentCompany', $company);
        Inertia::share('currentCompany', $company);

        return $next($request);
    }
}
