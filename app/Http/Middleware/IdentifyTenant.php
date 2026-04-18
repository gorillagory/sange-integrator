<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Extract the subdomain (e.g., 'bner' from bner.bayam.test)
        $subdomain = $request->route('subdomain');

        // 2. Lookup the Company in the Control DB
        $company = DB::connection('control')->table('companies')
            ->where('subdomain', $subdomain)
            ->where('is_active', true)
            ->first();

        if (!$company) {
            abort(404, 'Company environment not found or inactive.');
        }

        // 3. RBAC Security Bridge: Does this user actually belong to this company?
        $user = Auth::user();
        $hasAccess = DB::connection('control')->table('company_user')
            ->where('user_id', $user->id)
            ->where('company_id', $company->id)
            ->exists();

        if (!$hasAccess) {
            // Kick unauthorized users back out to the System Core
            return redirect('http://sys.bayam.test:8000/dashboard')
                ->with('error', "You do not have security clearance for {$company->name}.");
        }

        // 4. The Vault Switch: Shape-shift the database connection
        Config::set('database.connections.tenant.database', $company->db_name);
        DB::purge('tenant');
        DB::reconnect('tenant');

        // 5. CRITICAL FOR SPATIE: Set the default connection to 'tenant'
        Config::set('database.default', 'tenant');

        // 6. Share the company data with all views/Inertia automatically
        view()->share('currentCompany', $company);
        Inertia::share('currentCompany', $company); // 👈 2. ADD THIS LINE
        return $next($request);
    }
}
