<?php

namespace App\Http\Middleware;

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

        $company = DB::connection('control')->table('companies')
            ->where('subdomain', $subdomain)
            ->where('is_active', true)
            ->first();

        if (! $company) {
            abort(404, 'Company environment not found or inactive.');
        }

        $user = Auth::user();

        $hasAccess = DB::connection('control')->table('company_user')
            ->where('user_id', $user->id)
            ->where('company_id', $company->id)
            ->exists();

        if (! $hasAccess) {
            return redirect('http://sys.bayam.test:8000/dashboard')
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
