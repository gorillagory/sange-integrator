<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

class AllowGlobalAdminOrTenantPermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        if ($user->isSuperAdmin() || $user->isSystemAdmin()) {
            return $next($request);
        }

        $company = $this->resolveCurrentCompany($request);
        $companyId = $company?->id ? (int) $company->id : 0;

        if ($companyId === 0) {
            abort(403, 'Tenant context is missing.');
        }

        app(PermissionRegistrar::class)->setPermissionsTeamId($companyId);

        foreach ($permissions as $permission) {
            if ($permission !== '' && $user->can($permission)) {
                return $next($request);
            }
        }

        $effectivePermissions = $this->derivePermissionsFromRoles(
            userId: (int) $user->id,
            modelType: $user->getMorphClass(),
            companyId: $companyId,
        );

        foreach ($permissions as $permission) {
            if (in_array($permission, $effectivePermissions, true)) {
                return $next($request);
            }
        }

        abort(403, 'User does not have the necessary access rights.');
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

        if (! is_string($subdomain) || trim($subdomain) === '') {
            return null;
        }

        return Company::query()
            ->select(['id', 'name', 'subdomain', 'main_group_company_id', 'logo_path', 'theme_color', 'is_active'])
            ->whereRaw('LOWER(TRIM(subdomain)) = ?', [trim(strtolower($subdomain))])
            ->where('is_active', true)
            ->first();
    }

    private function derivePermissionsFromRoles(int $userId, string $modelType, int $companyId): array
    {
        $roleNames = DB::connection('control')
            ->table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->whereIn('model_has_roles.model_type', array_values(array_unique([$modelType, 'App\\Models\\User'])))
            ->where('model_has_roles.model_id', $userId)
            ->where('model_has_roles.company_id', $companyId)
            ->orderBy('roles.name')
            ->pluck('roles.name')
            ->values()
            ->all();

        $matrix = config('rbac.tenant_role_permissions', []);

        return collect($roleNames)
            ->flatMap(fn (string $roleName) => $matrix[$roleName] ?? [])
            ->unique()
            ->values()
            ->all();
    }
}
