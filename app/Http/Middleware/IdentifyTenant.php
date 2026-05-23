<?php

namespace App\Http\Middleware;

use App\Models\Company;
use App\Services\AuditEngine;
use App\Services\AuthRedirectService;
use App\Services\RbacMatrixService;
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

        $isSystemUser = $user->isSystemUser();

        $hasMembership = DB::connection('control')
            ->table('company_user')
            ->where('user_id', $user->id)
            ->where('company_id', $company->id)
            ->exists();

        if (! $isSystemUser && ! $hasMembership) {
            /** @var \App\Services\AuthRedirectService $redirectService */
            $redirectService = app(AuthRedirectService::class);

            $targetUrl = $redirectService->redirectForDeniedTenantAccess($user, $company);
            $message = "You do not have security clearance for {$company->name}.";
            AuditEngine::log('ACCESS', 'TENANT.ACCESS_DENIED', [
                'attempted_company_id' => $company->id,
                'attempted_company_subdomain' => $company->subdomain,
                'redirect_url' => $targetUrl,
            ], [], $user);

            if ($request->header('X-Inertia') && $this->isCrossOriginRedirect($request, $targetUrl)) {
                $request->session()->flash('error', $message);

                return Inertia::location($targetUrl);
            }

            return redirect($targetUrl)->with('error', $message);
        }

        Config::set('database.connections.tenant.database', $company->db_name);
        DB::purge('tenant');
        DB::reconnect('tenant');
        Config::set('database.default', 'tenant');

        app(PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        app(RbacMatrixService::class)->syncForCompany($company->id);

        app()->instance('currentCompany', $company);
        view()->share('currentCompany', $company);
        Inertia::share('currentCompany', $company);

        return $next($request);
    }

    private function isCrossOriginRedirect(Request $request, string $targetUrl): bool
    {
        $target = parse_url($targetUrl);

        if (! is_array($target) || ! isset($target['host'])) {
            return false;
        }

        $targetScheme = $target['scheme'] ?? $request->getScheme();
        $targetHost = $target['host'];
        $targetPort = $target['port'] ?? null;

        $requestScheme = $request->getScheme();
        $requestHost = $request->getHost();
        $requestPort = $request->getPort();

        return $targetScheme !== $requestScheme
            || $targetHost !== $requestHost
            || ($targetPort !== null && $targetPort !== $requestPort);
    }
}
