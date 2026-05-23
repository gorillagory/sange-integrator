<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

class RbacMatrixService
{
    public function bootstrapGlobalRoles(): void
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId(0);

        $permissions = array_values(array_unique(array_merge(
            config('rbac.global_permissions', []),
            config('rbac.tenant_permissions', []),
        )));

        $this->ensurePermissionsExist($permissions);

        foreach ($this->globalRolePermissions() as $roleName => $permissionNames) {
            $role = Role::query()
                ->where('company_id', 0)
                ->where('guard_name', 'web')
                ->where('name', $roleName)
                ->first();

            $wasCreated = false;

            if (! $role) {
                $role = Role::create([
                    'name' => $roleName,
                    'guard_name' => 'web',
                    'company_id' => 0,
                ]);
                $wasCreated = true;
            }

            if ($wasCreated || in_array($roleName, $this->immutableGlobalRoleNames(), true)) {
                $role->syncPermissions($permissionNames);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function bootstrapTenantRoles(int $companyId): void
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId($companyId);

        $roleMatrix = config('rbac.tenant_role_permissions', []);
        $this->ensurePermissionsExist($this->tenantPermissionNames());

        foreach ($roleMatrix as $roleName => $permissionNames) {
            $role = $this->findTenantRole($roleName, $companyId);

            if (! $role) {
                $role = Role::create([
                    'name' => $roleName,
                    'guard_name' => 'web',
                    'company_id' => $companyId,
                ]);
                $role->syncPermissions($permissionNames);
            }

            $this->migrateLegacyAssignments($companyId, $roleName, (int) $role->id);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function syncForCompany(int $companyId): void
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId($companyId);
        $this->ensurePermissionsExist($this->tenantPermissionNames());

        foreach ($this->tenantRolePermissions() as $roleName => $_permissionNames) {
            $role = $this->findTenantRole($roleName, $companyId);

            if ($role) {
                $this->migrateLegacyAssignments($companyId, $roleName, (int) $role->id);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function tenantPermissionNames(): array
    {
        return array_values(config('rbac.tenant_permissions', []));
    }

    public function globalPermissionNames(): array
    {
        return array_values(config('rbac.global_permissions', []));
    }

    public function globalRolePermissions(): array
    {
        return config('rbac.global_role_permissions', []);
    }

    public function tenantRolePermissions(): array
    {
        return config('rbac.tenant_role_permissions', []);
    }

    public function defaultTenantRoleNames(): array
    {
        return array_keys($this->tenantRolePermissions());
    }

    public function defaultGlobalRoleNames(): array
    {
        return array_keys($this->globalRolePermissions());
    }

    public function immutableGlobalRoleNames(): array
    {
        return ['super_admin'];
    }

    public function findOrCreateTenantRole(string $roleName, int $companyId): Role
    {
        return $this->findTenantRole($roleName, $companyId)
            ?? Role::create([
                'name' => $roleName,
                'guard_name' => 'web',
                'company_id' => $companyId,
            ]);
    }

    public function findTenantRole(string $roleName, int $companyId): ?Role
    {
        return Role::query()
            ->where('company_id', $companyId)
            ->where('guard_name', 'web')
            ->where('name', $roleName)
            ->first();
    }

    public function protectedRoleNames(): array
    {
        return [];
    }

    public function protectedGlobalRoleNames(): array
    {
        return $this->immutableGlobalRoleNames();
    }

    private function migrateLegacyAssignments(int $companyId, string $roleName, int $tenantRoleId): void
    {
        $legacyRoleIds = Role::query()
            ->where('guard_name', 'web')
            ->where('name', $roleName)
            ->where('company_id', 0)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($legacyRoleIds === []) {
            return;
        }

        DB::connection('control')
            ->table('model_has_roles')
            ->where('company_id', $companyId)
            ->whereIn('role_id', $legacyRoleIds)
            ->update(['role_id' => $tenantRoleId]);
    }

    private function ensurePermissionsExist(array $permissionNames): void
    {
        foreach ($permissionNames as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }
    }
}
