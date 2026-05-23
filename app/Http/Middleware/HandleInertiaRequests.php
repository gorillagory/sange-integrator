<?php

namespace App\Http\Middleware;

use App\Models\Company;
use App\Services\RbacMatrixService;
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
                    'username' => $request->user()->username,
                    'email' => $request->user()->email,
                    'digital_id' => $request->user()->digital_id,
                    'image_url' => $request->user()->image_url,
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

        $company = $this->resolveCurrentCompany($request);

        if (! $company) {
            $subdomain = AppHost::extractSubdomain($host);

            if ($subdomain !== null && $subdomain !== '') {
                $company = Company::query()
                    ->select(['id', 'name', 'subdomain', 'main_group_company_id', 'logo_path', 'theme_color', 'is_active'])
                    ->whereRaw('LOWER(TRIM(subdomain)) = ?', [$subdomain])
                    ->where('is_active', true)
                    ->first();
            }
        }

        if ($company) {
            $company->loadMissing('mainGroupCompany');
        }

        $logoUrl = $this->normalizePublicAssetUrl($company?->logo_path);
        $mainGroupLogoUrl = $this->normalizePublicAssetUrl($company?->mainGroupCompany?->logo_path);

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
                'main_group' => $company->mainGroupCompany ? [
                    'id' => $company->mainGroupCompany->id,
                    'name' => $company->mainGroupCompany->name,
                    'logo_url' => $mainGroupLogoUrl,
                ] : null,
                'theme_color' => $company->theme_color ?: '#4f46e5',
            ] : null,
            'favicon_url' => $logoUrl ?: '/favicon.ico',
        ];
    }

    private function normalizePublicAssetUrl(?string $path): ?string
    {
        if (! is_string($path) || trim($path) === '') {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', 'data:'])) {
            return $path;
        }

        return Str::startsWith($path, '/storage/')
            ? $path
            : '/storage/'.ltrim(Str::replaceFirst('storage/', '', $path), '/');
    }

    private function buildRbacContext(Request $request): array
    {
        $user = $request->user();

        if (! $user) {
            return [
                'global_roles' => [],
                'tenant_roles' => [],
                'tenant_permissions' => [],
                'tenant_modules' => [],
                'is_super_admin' => false,
                'is_system_admin' => false,
                'system_nav' => [
                    'dashboard' => false,
                    'companies' => false,
                    'blueprints' => false,
                    'users' => false,
                    'rbac' => false,
                    'audit_logs' => false,
                ],
                'tenant_nav' => [
                    'dashboard' => false,
                    'operations' => false,
                    'clients' => false,
                    'reports' => false,
                    'schemas' => false,
                    'documents' => false,
                    'rbac' => false,
                ],
            ];
        }

        $globalRoles = $this->getRoleNamesByScope((int) $user->id, $user->getMorphClass(), 0);
        $currentCompany = $this->resolveCurrentCompany($request);
        $companyId = $currentCompany?->id ? (int) $currentCompany->id : 0;
        $tenantRoles = $companyId > 0
            ? $this->getRoleNamesByScope((int) $user->id, $user->getMorphClass(), $companyId)
            : [];
        $tenantPermissionNames = $companyId > 0
            ? app(RbacMatrixService::class)->tenantPermissionNames()
            : [];
        $tenantModules = $companyId > 0 ? $this->getTenantModules($companyId) : [];

        $isSuperAdmin = $user->isSuperAdmin() || in_array('super_admin', $globalRoles, true);
        $isSystemAdmin = $user->isSystemAdmin() || in_array('system_admin', $globalRoles, true);

        $hasBookingModule = in_array('travel.booking', $tenantModules, true);
        $hasSchemasModule = in_array('travel.schemas', $tenantModules, true);
        $hasDocumentsModule = in_array('travel.documents', $tenantModules, true);
        $hasResolvedModules = $companyId > 0 && ! empty($tenantModules);

        $tenantPermissions = $companyId > 0
            ? collect($tenantPermissionNames)
                ->filter(fn (string $permission) => $user->can($permission))
                ->values()
                ->all()
            : [];

        if ($companyId > 0 && $tenantPermissions === [] && $tenantRoles !== []) {
            $tenantPermissions = collect($tenantRoles)
                ->flatMap(fn (string $roleName) => config("rbac.tenant_role_permissions.{$roleName}", []))
                ->unique()
                ->values()
                ->all();
        }

        if ($companyId > 0 && ($isSuperAdmin || $isSystemAdmin)) {
            $tenantPermissions = $tenantPermissionNames;
        }

        return [
            'global_roles' => $globalRoles,
            'tenant_roles' => $tenantRoles,
            'tenant_permissions' => $tenantPermissions,
            'tenant_modules' => $tenantModules,
            'is_super_admin' => $isSuperAdmin,
            'is_system_admin' => $isSystemAdmin,
            'system_nav' => [
                'dashboard' => $isSuperAdmin || $isSystemAdmin,
                'companies' => $isSuperAdmin,
                'blueprints' => $isSuperAdmin || $isSystemAdmin,
                'users' => $isSuperAdmin,
                'rbac' => $isSuperAdmin || $isSystemAdmin,
                'audit_logs' => $isSuperAdmin || $isSystemAdmin,
            ],
            'tenant_nav' => [
                'dashboard' => $companyId > 0 && ($isSuperAdmin || in_array('tenant.dashboard.view', $tenantPermissions, true)),
                'operations' => ($isSuperAdmin || in_array('service_records.view', $tenantPermissions, true))
                    && (! $hasResolvedModules || $hasBookingModule),
                'clients' => ($isSuperAdmin || in_array('clients.view', $tenantPermissions, true))
                    && (! $hasResolvedModules || $hasBookingModule),
                'reports' => ($isSuperAdmin || in_array('reports.view', $tenantPermissions, true))
                    && (! $hasResolvedModules || $hasBookingModule),
                'schemas' => ($isSuperAdmin || in_array('schemas.view', $tenantPermissions, true))
                    && (! $hasResolvedModules || $hasSchemasModule),
                'documents' => ($isSuperAdmin || in_array('documents.view', $tenantPermissions, true))
                    && (! $hasResolvedModules || $hasDocumentsModule),
                'rbac' => $companyId > 0 && ($isSuperAdmin || in_array('rbac.view', $tenantPermissions, true)),
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

    private function resolveCurrentCompany(Request $request): ?Company
    {
        $appCompany = app()->bound('currentCompany') ? app('currentCompany') : null;

        if ($appCompany instanceof Company) {
            return $appCompany;
        }

        $viewCompany = view()->shared('currentCompany');

        if ($viewCompany instanceof Company) {
            return $viewCompany;
        }

        $subdomain = $request->route('subdomain');

        if (is_string($subdomain) && trim($subdomain) !== '') {
            return Company::query()
                ->select(['id', 'name', 'subdomain', 'main_group_company_id', 'logo_path', 'theme_color', 'is_active'])
                ->whereRaw('LOWER(TRIM(subdomain)) = ?', [trim(strtolower($subdomain))])
                ->where('is_active', true)
                ->first();
        }

        return null;
    }
}
