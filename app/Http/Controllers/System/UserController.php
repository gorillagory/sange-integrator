<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Http\Requests\System\StoreUserRequest;
use App\Http\Requests\System\UpdateUserRequest;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditEngine;
use App\Services\RbacMatrixService;
use App\Services\UserIdentityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\PermissionRegistrar;

class UserController extends Controller
{
    public function __construct(
        private readonly UserIdentityService $identityService,
        private readonly RbacMatrixService $rbacMatrix,
    ) {}

    public function index(Request $request): Response
    {
        $this->ensureBaseRolesExist();

        $search = trim((string) $request->string('search'));

        $users = User::query()
            ->with(['companies:id,name,subdomain,industry'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('id', $search);
                });
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString()
            ->through(function (User $user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'global_roles' => $this->getUserGlobalRoles($user),
                    'memberships' => $this->getUserMembershipPayload($user),
                    'created_at' => optional($user->created_at)?->toDateTimeString(),
                ];
            });

        return Inertia::render('Users/Index', [
            'filters' => [
                'search' => $search,
            ],
            'users' => $users,
            'companies' => Company::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'subdomain', 'industry']),
            'globalRoles' => $this->getAvailableGlobalRoles(),
            'tenantRoleOptions' => $this->getAvailableTenantRoles(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        DB::connection('control')->transaction(function () use ($request) {
            $this->ensureBaseRolesExist();

            $user = User::create([
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
                'password' => Hash::make($request->validated('password')),
            ]);

            $this->syncGlobalRoles(
                user: $user,
                roleNames: $request->validated('global_roles', [])
            );

            $this->syncCompanyMembershipsAndTenantRoles(
                user: $user,
                memberships: $request->validated('memberships', [])
            );

            $identityCompany = $this->companyFromMemberships($request->validated('memberships', []));
            $this->identityService->ensureIdentity($user, $identityCompany);

            AuditEngine::log('USER_ADMIN', 'USER.ACCESS_CREATED', [
                'global_roles' => $this->getUserGlobalRoles($user),
                'memberships' => $this->getUserMembershipPayload($user),
            ], [], $user);
        });

        return redirect()
            ->route('system.users.index')
            ->with('success', 'User enrolled successfully.');
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        DB::connection('control')->transaction(function () use ($request, $user) {
            $this->ensureBaseRolesExist();
            $beforeGlobalRoles = $this->getUserGlobalRoles($user);
            $beforeMemberships = $this->getUserMembershipPayload($user);

            $payload = [
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
            ];

            if ($request->filled('password')) {
                $payload['password'] = Hash::make($request->validated('password'));
            }

            $user->update($payload);

            $this->syncGlobalRoles(
                user: $user,
                roleNames: $request->validated('global_roles', [])
            );

            $this->syncCompanyMembershipsAndTenantRoles(
                user: $user,
                memberships: $request->validated('memberships', [])
            );

            $identityCompany = $this->companyFromMemberships($request->validated('memberships', []));
            $this->identityService->ensureIdentity($user, $identityCompany);

            AuditEngine::log('USER_ADMIN', 'USER.ACCESS_UPDATED', [
                'global_roles' => $this->getUserGlobalRoles($user),
                'memberships' => $this->getUserMembershipPayload($user),
            ], [
                'global_roles' => $beforeGlobalRoles,
                'memberships' => $beforeMemberships,
            ], $user);
        });

        return redirect()
            ->route('system.users.index')
            ->with('success', 'User updated successfully.');
    }

    private function syncGlobalRoles(User $user, array $roleNames): void
    {
        $allowedRoleNames = array_values(array_intersect($roleNames, $this->availableGlobalRoleNames()));
        $globalRoleIds = $this->visibleGlobalRolesQuery()
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        DB::connection('control')
            ->table('model_has_roles')
            ->where('model_type', $user->getMorphClass())
            ->where('model_id', $user->id)
            ->where('company_id', 0)
            ->whereIn('role_id', $globalRoleIds)
            ->delete();

        $assignedRoleIds = $this->getRoleIds($allowedRoleNames);

        foreach ($assignedRoleIds as $roleId) {
            DB::connection('control')
                ->table('model_has_roles')
                ->insertOrIgnore([
                    'role_id' => $roleId,
                    'model_type' => $user->getMorphClass(),
                    'model_id' => $user->id,
                    'company_id' => 0,
                ]);
        }

        $this->flushPermissionCache();
    }

    private function companyFromMemberships(array $memberships): ?Company
    {
        $companyId = collect($memberships)
            ->pluck('company_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->first();

        if (! $companyId) {
            return null;
        }

        return Company::query()->with('mainGroupCompany')->find($companyId);
    }

    private function syncCompanyMembershipsAndTenantRoles(User $user, array $memberships): void
    {
        $normalizedMemberships = collect($memberships)
            ->filter(fn ($membership) => ! empty($membership['company_id']))
            ->map(function ($membership) {
                return [
                    'company_id' => (int) $membership['company_id'],
                    'tenant_roles' => array_values(array_unique(array_intersect(
                        $membership['tenant_roles'] ?? [],
                        $this->availableTenantRoleNamesForCompany((int) $membership['company_id'])
                    ))),
                ];
            })
            ->unique('company_id')
            ->values();

        $syncPayload = $normalizedMemberships
            ->mapWithKeys(fn ($membership) => [
                $membership['company_id'] => [
                    'role' => 'member',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ])
            ->all();

        $user->companies()->sync($syncPayload);

        DB::connection('control')
            ->table('model_has_roles')
            ->where('model_type', $user->getMorphClass())
            ->where('model_id', $user->id)
            ->where('company_id', '<>', 0)
            ->delete();

        foreach ($normalizedMemberships as $membership) {
            $companyId = (int) $membership['company_id'];
            $roleIds = $this->getRoleIds($membership['tenant_roles'], $companyId);

            foreach ($roleIds as $roleId) {
                DB::connection('control')
                    ->table('model_has_roles')
                    ->insertOrIgnore([
                        'role_id' => $roleId,
                        'model_type' => $user->getMorphClass(),
                        'model_id' => $user->id,
                        'company_id' => $companyId,
                    ]);
            }
        }

        $this->flushPermissionCache();
    }

    private function getAvailableGlobalRoles(): array
    {
        $this->ensureBaseRolesExist();

        return $this->visibleGlobalRolesQuery()
            ->get(['id', 'name'])
            ->map(fn (Role $role) => [
                'id' => $role->id,
                'name' => $role->name,
            ])
            ->values()
            ->all();
    }

    private function getAvailableTenantRoles(): array
    {
        return Company::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id'])
            ->mapWithKeys(fn (Company $company) => [
                (int) $company->id => Role::query()
                    ->where('company_id', $company->id)
                    ->orderBy('name')
                    ->get(['id', 'name'])
                    ->map(fn (Role $role) => [
                        'id' => (int) $role->id,
                        'name' => $role->name,
                    ])
                    ->values()
                    ->all(),
            ])
            ->all();
    }

    private function getUserGlobalRoles(User $user): array
    {
        return DB::connection('control')
            ->table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_type', $user->getMorphClass())
            ->where('model_has_roles.model_id', $user->id)
            ->where('model_has_roles.company_id', 0)
            ->whereNotIn('roles.name', $this->rbacMatrix->defaultTenantRoleNames())
            ->orderBy('roles.name')
            ->pluck('roles.name')
            ->values()
            ->all();
    }

    private function getUserMembershipPayload(User $user): array
    {
        $memberships = DB::connection('control')
            ->table('company_user')
            ->join('companies', 'companies.id', '=', 'company_user.company_id')
            ->where('company_user.user_id', $user->id)
            ->select([
                'companies.id as company_id',
                'companies.name as company_name',
                'companies.subdomain',
                'companies.industry',
            ])
            ->orderBy('companies.name')
            ->get();

        return $memberships
            ->map(function ($membership) use ($user) {
                return [
                    'company_id' => (int) $membership->company_id,
                    'company_name' => $membership->company_name,
                    'subdomain' => $membership->subdomain,
                    'industry' => $membership->industry,
                    'tenant_roles' => $this->getUserTenantRolesForCompany(
                        user: $user,
                        companyId: (int) $membership->company_id
                    ),
                ];
            })
            ->values()
            ->all();
    }

    private function getUserTenantRolesForCompany(User $user, int $companyId): array
    {
        return DB::connection('control')
            ->table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_type', $user->getMorphClass())
            ->where('model_has_roles.model_id', $user->id)
            ->where('model_has_roles.company_id', $companyId)
            ->orderBy('roles.name')
            ->pluck('roles.name')
            ->values()
            ->all();
    }

    private function ensureBaseRolesExist(): void
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId(0);
        $this->rbacMatrix->bootstrapGlobalRoles();

        $this->flushPermissionCache();
    }

    private function visibleGlobalRolesQuery()
    {
        return Role::query()
            ->where('company_id', 0)
            ->whereNotIn('name', $this->rbacMatrix->defaultTenantRoleNames())
            ->orderByRaw("CASE WHEN name = 'super_admin' THEN 0 ELSE 1 END")
            ->orderBy('name');
    }

    private function availableGlobalRoleNames(): array
    {
        return $this->visibleGlobalRolesQuery()
            ->pluck('name')
            ->values()
            ->all();
    }

    private function availableTenantRoleNamesForCompany(int $companyId): array
    {
        return Role::query()
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->pluck('name')
            ->values()
            ->all();
    }

    private function getRoleIds(array $roleNames, int $companyId = 0): array
    {
        if ($roleNames === []) {
            return [];
        }

        return Role::query()
            ->where('company_id', $companyId)
            ->whereIn('name', $roleNames)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    private function flushPermissionCache(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
