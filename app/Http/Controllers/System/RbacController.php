<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use App\Services\RbacMatrixService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\PermissionRegistrar;

class RbacController extends Controller
{
    public function __construct(
        private readonly RbacMatrixService $rbacMatrix,
    ) {}

    public function index(Request $request): Response
    {
        $this->ensureBaseRolesExist();
        $isSuperAdmin = $request->user()?->isSuperAdmin() === true;
        $legacyTenantRoleNames = $this->rbacMatrix->defaultTenantRoleNames();

        $globalRoles = Role::query()
            ->where('company_id', 0)
            ->when($legacyTenantRoleNames !== [], fn ($query) => $query->whereNotIn('name', $legacyTenantRoleNames))
            ->orderBy('name')
            ->get(['id', 'name', 'company_id']);

        $globalMembers = User::query()
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $memberRoleRows = DB::connection('control')
            ->table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where(function ($scope): void {
                $scope->where('model_has_roles.company_id', 0)
                    ->orWhereNull('model_has_roles.company_id');
            })
            ->where('model_has_roles.model_type', User::class)
            ->get([
                'model_has_roles.model_id as user_id',
                'roles.id as role_id',
                'roles.name as role_name',
            ])
            ->groupBy('user_id');

        $membersByRole = DB::connection('control')
            ->table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->join('users', 'users.id', '=', 'model_has_roles.model_id')
            ->where(function ($scope): void {
                $scope->where('model_has_roles.company_id', 0)
                    ->orWhereNull('model_has_roles.company_id');
            })
            ->where('model_has_roles.model_type', User::class)
            ->orderBy('roles.name')
            ->orderBy('users.name')
            ->get([
                'roles.name as role_name',
                'users.id as user_id',
                'users.name as user_name',
                'users.email as user_email',
            ])
            ->groupBy('role_name');

        $companies = Company::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'subdomain', 'industry']);

        $tenantRoleAssignments = $this->tenantRoleMemberCounts();
        $companyUserCounts = $this->companyUserCounts();
        $companyRoleCounts = $this->companyRoleCounts();
        $protectedGlobalRoles = $this->rbacMatrix->protectedGlobalRoleNames();

        return Inertia::render('System/Rbac/Index', [
            'globalRoles' => $globalRoles
                ->map(function (Role $role) use ($membersByRole, $protectedGlobalRoles) {
                    return [
                        'id' => (int) $role->id,
                        'name' => $role->name,
                        'permissions' => $role->permissions()
                            ->orderBy('name')
                            ->pluck('name')
                            ->values()
                            ->all(),
                        'is_protected' => in_array($role->name, $protectedGlobalRoles, true),
                        'members' => collect($membersByRole->get($role->name, collect()))
                            ->map(fn ($member) => [
                                'id' => (int) $member->user_id,
                                'name' => $member->user_name,
                                'email' => $member->user_email,
                            ])
                            ->values()
                            ->all(),
                    ];
                })
                ->values()
                ->all(),
            'globalMembers' => $globalMembers
                ->map(fn (User $user) => [
                    'id' => (int) $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ])
                ->values()
                ->all(),
            'globalMemberDirectory' => $globalMembers
                ->map(function (User $user) use ($memberRoleRows) {
                    $roleRows = collect($memberRoleRows->get($user->id, collect()));

                    return [
                        'id' => (int) $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role_ids' => $roleRows->pluck('role_id')->map(fn ($id) => (int) $id)->values()->all(),
                        'roles' => $roleRows->pluck('role_name')->values()->all(),
                    ];
                })
                ->values()
                ->all(),
            'globalPermissions' => $this->rbacMatrix->globalPermissionNames(),
            'protectedGlobalRoles' => $protectedGlobalRoles,
            'tenantRoles' => $this->buildTenantRoleCards($tenantRoleAssignments),
            'permissionCatalog' => $this->buildPermissionCatalog($this->rbacMatrix->tenantPermissionNames()),
            'companies' => $companies
                ->map(function (Company $company) use ($companyUserCounts, $companyRoleCounts) {
                    $companyId = (int) $company->id;

                    return [
                        'id' => $companyId,
                        'name' => $company->name,
                        'subdomain' => $company->subdomain,
                        'industry' => $company->industry,
                        'user_count' => $companyUserCounts[$companyId] ?? 0,
                        'role_count' => $companyRoleCounts[$companyId] ?? 0,
                        'rbac_href' => route('admin.rbac.index', ['subdomain' => $company->subdomain]),
                    ];
                })
                ->values()
                ->all(),
            'quickLinks' => [
                'users' => $isSuperAdmin ? route('system.users.index') : null,
                'companies' => $isSuperAdmin ? route('system.companies.index') : null,
            ],
            'stats' => [
                'global_user_count' => User::query()->count(),
                'company_count' => $companies->count(),
                'tenant_role_count' => Role::query()->where('company_id', '>', 0)->count(),
                'permission_count' => count($this->rbacMatrix->tenantPermissionNames()),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureBaseRolesExist();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'permissions' => ['array'],
            'permissions.*' => ['string'],
            'member_ids' => ['array'],
            'member_ids.*' => ['integer'],
        ]);

        $roleName = trim((string) $validated['name']);

        abort_if($roleName === '', 422, 'Role name is required.');
        abort_if(in_array($roleName, $this->rbacMatrix->protectedGlobalRoleNames(), true), 422, 'This role name is reserved.');

        $roleExists = Role::query()
            ->where('company_id', 0)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($roleName)])
            ->exists();

        abort_if($roleExists, 422, 'A global role with this name already exists.');

        $permissions = $this->validatedPermissions($validated['permissions'] ?? [], $this->rbacMatrix->globalPermissionNames());
        $memberIds = $this->validatedMemberIds($validated['member_ids'] ?? []);

        $role = Role::create([
            'name' => $roleName,
            'guard_name' => 'web',
            'company_id' => 0,
        ]);

        app(PermissionRegistrar::class)->setPermissionsTeamId(0);
        $role->syncPermissions($permissions);
        $this->syncRoleMembers($role, $memberIds);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()
            ->route('system.rbac.index', [], 303)
            ->with('success', 'Global role created successfully.');
    }

    public function update(Request $request, $roleId): RedirectResponse
    {
        $this->ensureBaseRolesExist();

        $role = $this->globalRole((int) $roleId, $request->input('name'));

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'permissions' => ['array'],
            'permissions.*' => ['string'],
            'member_ids' => ['array'],
            'member_ids.*' => ['integer'],
        ]);

        $permissions = $this->validatedPermissions($validated['permissions'] ?? [], $this->rbacMatrix->globalPermissionNames());
        $memberIds = $this->validatedMemberIds($validated['member_ids'] ?? []);
        $isProtected = in_array($role->name, $this->rbacMatrix->protectedGlobalRoleNames(), true);
        $newName = trim((string) $validated['name']);

        if (! $isProtected) {
            abort_if($newName === '', 422, 'Role name is required.');

            $roleExists = Role::query()
                ->where('company_id', 0)
                ->where('id', '<>', $role->id)
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($newName)])
                ->exists();

            abort_if($roleExists, 422, 'A global role with this name already exists.');
            $role->update(['name' => $newName]);
        }

        app(PermissionRegistrar::class)->setPermissionsTeamId(0);
        $role->syncPermissions($permissions);
        $this->syncRoleMembers($role, $memberIds);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()
            ->route('system.rbac.index', [], 303)
            ->with('success', $isProtected
                ? 'Protected global role access updated successfully.'
                : 'Global role updated successfully.');
    }

    public function destroy($roleId): RedirectResponse
    {
        $role = $this->globalRole((int) $roleId);

        abort_if(
            in_array($role->name, $this->rbacMatrix->protectedGlobalRoleNames(), true),
            422,
            'Protected global roles cannot be deleted.'
        );

        DB::connection('control')
            ->table('model_has_roles')
            ->where(function ($scope): void {
                $scope->where('company_id', 0)
                    ->orWhereNull('company_id');
            })
            ->where('role_id', $role->id)
            ->delete();

        $role->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()
            ->route('system.rbac.index', [], 303)
            ->with('success', 'Global role deleted successfully.');
    }

    public function updateMemberRoles(Request $request, $userId): RedirectResponse
    {
        $this->ensureBaseRolesExist();
        $userId = (int) $userId;

        $validated = $request->validate([
            'role_ids' => ['array'],
            'role_ids.*' => ['integer'],
        ]);

        abort_unless(User::query()->whereKey($userId)->exists(), 404);

        $allowedRoleIds = Role::query()
            ->where('company_id', 0)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $roleIds = collect($validated['role_ids'] ?? [])
            ->map(fn ($roleId) => (int) $roleId)
            ->filter(fn (int $roleId) => in_array($roleId, $allowedRoleIds, true))
            ->unique()
            ->values()
            ->all();

        DB::connection('control')
            ->table('model_has_roles')
            ->where(function ($scope): void {
                $scope->where('company_id', 0)
                    ->orWhereNull('company_id');
            })
            ->where('model_type', User::class)
            ->where('model_id', $userId)
            ->delete();

        foreach ($roleIds as $roleId) {
            DB::connection('control')
                ->table('model_has_roles')
                ->insertOrIgnore([
                    'role_id' => $roleId,
                    'model_type' => User::class,
                    'model_id' => $userId,
                    'company_id' => 0,
                ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()
            ->route('system.rbac.index', [], 303)
            ->with('success', 'Global user roles updated successfully.');
    }

    private function buildTenantRoleCards(array $memberCounts): array
    {
        return Role::query()
            ->where('company_id', '>', 0)
            ->with('permissions:id,name')
            ->orderBy('name')
            ->get(['id', 'name', 'company_id'])
            ->groupBy('name')
            ->map(function (Collection $roles, string $roleName) use ($memberCounts) {
                $permissionNames = $roles
                    ->flatMap(fn (Role $role) => $role->permissions->pluck('name'))
                    ->unique()
                    ->values();
                $grouped = $this->groupPermissions($permissionNames);

                return [
                    'name' => $roleName,
                    'label' => str($roleName)->replace('_', ' ')->title()->toString(),
                    'member_count' => $memberCounts[$roleName] ?? 0,
                    'permission_count' => $permissionNames->count(),
                    'company_count' => $roles->count(),
                    'groups' => $grouped->map(fn (Collection $items, string $group) => [
                        'name' => $group,
                        'permissions' => $items->values()->all(),
                    ])->values()->all(),
                ];
            })
            ->sortBy('label')
            ->values()
            ->all();
    }

    private function buildPermissionCatalog(array $permissions): array
    {
        return $this->groupPermissions(collect($permissions))
            ->map(fn (Collection $items, string $group) => [
                'name' => $group,
                'permissions' => $items->values()->all(),
            ])
            ->values()
            ->all();
    }

    private function groupPermissions(Collection $permissions): Collection
    {
        return $permissions
            ->groupBy(function (string $permission) {
                $suffix = (string) str($permission)->afterLast('.');

                return match ($suffix) {
                    'view' => 'View Access',
                    'manage', 'capture', 'generate' => 'Action Authority',
                    default => 'Access Control',
                };
            })
            ->sortKeys();
    }

    private function tenantRoleMemberCounts(): array
    {
        return DB::connection('control')
            ->table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.company_id', '>', 0)
            ->whereIn('roles.name', array_keys($this->rbacMatrix->tenantRolePermissions()))
            ->groupBy('roles.name')
            ->selectRaw('roles.name, COUNT(DISTINCT model_has_roles.model_id) as member_count')
            ->pluck('member_count', 'roles.name')
            ->map(fn ($count) => (int) $count)
            ->all();
    }

    private function companyUserCounts(): array
    {
        return DB::connection('control')
            ->table('company_user')
            ->groupBy('company_id')
            ->selectRaw('company_id, COUNT(DISTINCT user_id) as user_count')
            ->pluck('user_count', 'company_id')
            ->map(fn ($count) => (int) $count)
            ->all();
    }

    private function companyRoleCounts(): array
    {
        return DB::connection('control')
            ->table('roles')
            ->where('company_id', '>', 0)
            ->groupBy('company_id')
            ->selectRaw('company_id, COUNT(*) as role_count')
            ->pluck('role_count', 'company_id')
            ->map(fn ($count) => (int) $count)
            ->all();
    }

    private function validatedPermissions(array $permissions, array $allowed): array
    {
        return collect($permissions)
            ->map(fn ($permission) => (string) $permission)
            ->filter(fn (string $permission) => in_array($permission, $allowed, true))
            ->unique()
            ->values()
            ->all();
    }

    private function validatedMemberIds(array $memberIds): array
    {
        $allowedIds = User::query()
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return collect($memberIds)
            ->map(fn ($memberId) => (int) $memberId)
            ->filter(fn (int $memberId) => in_array($memberId, $allowedIds, true))
            ->unique()
            ->values()
            ->all();
    }

    private function syncRoleMembers(Role $role, array $memberIds): void
    {
        DB::connection('control')
            ->table('model_has_roles')
            ->where(function ($scope): void {
                $scope->where('company_id', 0)
                    ->orWhereNull('company_id');
            })
            ->where('role_id', $role->id)
            ->where('model_type', User::class)
            ->delete();

        foreach ($memberIds as $memberId) {
            DB::connection('control')
                ->table('model_has_roles')
                ->insertOrIgnore([
                    'role_id' => $role->id,
                    'model_type' => User::class,
                    'model_id' => $memberId,
                    'company_id' => 0,
                ]);
        }
    }

    private function globalRole(int $roleId, ?string $roleName = null): Role
    {
        $role = Role::query()
            ->where('company_id', 0)
            ->where('id', $roleId)
            ->first();

        if ($role instanceof Role) {
            return $role;
        }

        $normalizedName = trim((string) $roleName);

        if ($normalizedName !== '') {
            $role = Role::query()
                ->where('company_id', 0)
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($normalizedName)])
                ->first();

            if ($role instanceof Role) {
                return $role;
            }
        }

        return Role::query()
            ->where('company_id', 0)
            ->where('id', $roleId)
            ->firstOrFail();
    }

    private function ensureBaseRolesExist(): void
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId(0);
        $this->rbacMatrix->bootstrapGlobalRoles();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
