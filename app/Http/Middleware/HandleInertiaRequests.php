<?php

namespace App\Http\Middleware;

use App\Models\Company;
use App\Support\AppHost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $rbac = $this->buildRbacContext($request);
        $brand = $this->buildBrandContext($request);

        return [
            ...parent::share($request),

            // Globally share the Authenticated User
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                ] : null,
                'rbac' => $rbac,
            ],

            // Globally share the current active vault (set by IdentifyTenant middleware)
            'currentCompany' => view()->shared('currentCompany'),
            'brand' => $brand,

            // 📡 THE GLOBAL FEEDBACK PIPELINE
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'info' => fn () => $request->session()->get('info'),
            ],
        ];
    }

    private function buildBrandContext(Request $request): array
    {
        $rawHost = (string) ($request->header('host') ?: $request->getHttpHost() ?: $request->getHost());
        $host = AppHost::normalizeHost($rawHost);
        $systemName = (string) config('app.name', 'Sange Central');
        $baseDomain = AppHost::baseDomain();
        $systemHosts = [AppHost::systemHost(), $baseDomain, 'localhost', '127.0.0.1'];
        $isSystemHost = in_array($host, $systemHosts, true);

        $currentCompany = view()->shared('currentCompany');
        $company = is_object($currentCompany) ? $currentCompany : null;

        if (! $company) {
            $subdomain = AppHost::extractSubdomain($host);

            if ($subdomain !== null && $subdomain !== '') {
                $company = Company::query()
                    ->select(['id', 'name', 'subdomain', 'logo_path', 'theme_color', 'is_active'])
                    ->whereRaw('LOWER(TRIM(subdomain)) = ?', [$subdomain])
                    ->where('is_active', true)
                    ->first();
            }
        }

        $logoPath = $company?->logo_path;
        $logoUrl = null;

        if (is_string($logoPath) && $logoPath !== '') {
            if (Str::startsWith($logoPath, ['http://', 'https://'])) {
                $logoUrl = $logoPath;
            } else {
                $logoUrl = Str::startsWith($logoPath, '/storage/')
                    ? $logoPath
                    : '/storage/'.ltrim(Str::replaceFirst('storage/', '', $logoPath), '/');
            }
        }

        return [
            'host' => $host,
            'base_domain' => $baseDomain,
            'system_host' => AppHost::systemHost(),
            'system_name' => $systemName,
            'is_system' => $company === null || $isSystemHost,
            'subdomain' => $company?->subdomain,
            'tenant' => $company ? [
                'id' => $company->id,
                'name' => $company->name,
                'subdomain' => $company->subdomain,
                'host' => AppHost::tenantHost($company->subdomain),
                'logo_url' => $logoUrl,
                'theme_color' => $company->theme_color ?: '#4f46e5',
            ] : null,
            'favicon_url' => $logoUrl ?: '/favicon.ico',
        ];
    }

    private function buildRbacContext(Request $request): array
    {
        $user = $request->user();

        if (! $user) {
            return [
                'global_roles' => [],
                'tenant_roles' => [],
                'tenant_modules' => [],
                'is_super_admin' => false,
                'is_system_admin' => false,
                'system_nav' => [
                    'dashboard' => false,
                    'companies' => false,
                    'blueprints' => false,
                    'users' => false,
                    'audit_logs' => false,
                ],
                'tenant_nav' => [
                    'dashboard' => false,
                    'operations' => false,
                    'clients' => false,
                    'reports' => false,
                    'schemas' => false,
                    'documents' => false,
                ],
            ];
        }

        $globalRoles = $this->getRoleNamesByScope((int) $user->id, $user->getMorphClass(), 0);
        $currentCompany = view()->shared('currentCompany');
        $companyId = is_object($currentCompany) && isset($currentCompany->id)
            ? (int) $currentCompany->id
            : 0;
        $tenantRoles = $companyId > 0
            ? $this->getRoleNamesByScope((int) $user->id, $user->getMorphClass(), $companyId)
            : [];
        $tenantModules = $companyId > 0 ? $this->getTenantModules($companyId) : [];

        $isSuperAdmin = $user->isSuperAdmin() || in_array('super_admin', $globalRoles, true);
        $isSystemAdmin = $user->isSystemAdmin() || in_array('system_admin', $globalRoles, true);

        $hasBookingModule = in_array('travel.booking', $tenantModules, true);
        $hasSchemasModule = in_array('travel.schemas', $tenantModules, true);
        $hasDocumentsModule = in_array('travel.documents', $tenantModules, true);

        $hasAgencyAdminRole = in_array('agency_admin', $tenantRoles, true);
        $hasDocumentManagerRole = in_array('document_manager', $tenantRoles, true);

        return [
            'global_roles' => $globalRoles,
            'tenant_roles' => $tenantRoles,
            'tenant_modules' => $tenantModules,
            'is_super_admin' => $isSuperAdmin,
            'is_system_admin' => $isSystemAdmin,
            'system_nav' => [
                'dashboard' => $isSuperAdmin || $isSystemAdmin,
                'companies' => $isSuperAdmin,
                'blueprints' => $isSuperAdmin || $isSystemAdmin,
                'users' => $isSuperAdmin,
                'audit_logs' => $isSuperAdmin || $isSystemAdmin,
            ],
            'tenant_nav' => [
                'dashboard' => $companyId > 0,
                'operations' => $isSuperAdmin || $hasBookingModule,
                'clients' => $isSuperAdmin || $hasBookingModule,
                'reports' => $isSuperAdmin || $hasBookingModule,
                'schemas' => $isSuperAdmin || ($hasSchemasModule && $hasAgencyAdminRole),
                'documents' => $isSuperAdmin || ($hasDocumentsModule && ($hasAgencyAdminRole || $hasDocumentManagerRole)),
            ],
        ];
    }

    private function getRoleNamesByScope(int $userId, string $modelType, int $companyId): array
    {
        $query = DB::connection('control')
            ->table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->whereIn('model_has_roles.model_type', array_values(array_unique([$modelType, 'App\\Models\\User'])))
            ->where('model_has_roles.model_id', $userId)
            ->orderBy('roles.name')
            ->select('roles.name');

        if ($companyId === 0) {
            $query->where(function ($scope): void {
                $scope->where('model_has_roles.company_id', 0)
                    ->orWhereNull('model_has_roles.company_id');
            });
        } else {
            $query->where('model_has_roles.company_id', $companyId);
        }

        return $query->pluck('roles.name')->values()->all();
    }

    private function getTenantModules(int $companyId): array
    {
        return DB::connection('control')
            ->table('company_modules')
            ->join('modules', 'modules.id', '=', 'company_modules.module_id')
            ->where('company_modules.company_id', $companyId)
            ->where('modules.is_active', true)
            ->orderBy('modules.key')
            ->pluck('modules.key')
            ->values()
            ->all();
    }
}
