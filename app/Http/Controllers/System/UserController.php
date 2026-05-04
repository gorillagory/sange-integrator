<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Http\Requests\System\StoreUserRequest;
use App\Http\Requests\System\UpdateUserRequest;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\PermissionRegistrar;

class UserController extends Controller
{
    private const GLOBAL_ROLES = [
        'super_admin',
        'system_admin',
    ];

    private const TENANT_ROLES = [
        'agency_admin',
        'travel_agent',
        'booking_manager',
        'document_manager',
    ];

    public function index(Request $request): Response
    {
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
            ->orderBy('name')
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
            'tenantRoles' => $this->getAvailableTenantRoles(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        DB::connection('control')->transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
                'password' => Hash::make($request->validated('password')),
            ]);

            $this->syncGlobalRoles(
                $user,
                $request->validated('global_roles', [])
            );

            $this->syncCompanyMembershipsAndTenantRoles(
                $user,
                $request->validated('memberships', [])
            );
        });

        return redirect()
            ->route('system.users.index')
            ->with('success', 'User enrolled successfully.');
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        DB::connection('control')->transaction(function () use ($request, $user) {
            $payload = [
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
            ];

            if ($request->filled('password')) {
                $payload['password'] = Hash::make($request->validated('password'));
            }

            $user->update($payload);

            $this->syncGlobalRoles(
                $user,
                $request->validated('global_roles', [])
            );

            $this->syncCompanyMembershipsAndTenantRoles(
                $user,
                $request->validated('memberships', [])
            );
        });

        return redirect()
            ->route('system.users.index')
            ->with('success', 'User updated successfully.');
    }

    private function syncGlobalRoles(User $user, array $roleNames): void
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId(0);

        $allowedRoleNames = array_values(array_intersect(
            $roleNames,
            self::GLOBAL_ROLES
        ));

        foreach (self::GLOBAL_ROLES as $roleName) {
            Role::findOrCreate($roleName, 'web');
        }

        $user->syncRoles($allowedRoleNames);
    }

    private function syncCompanyMembershipsAndTenantRoles(User $user, array $memberships): void
    {
        $normalizedMemberships = collect($memberships)
            ->filter(fn ($membership) => ! empty($membership['company_id']))
            ->map(function ($membership) {
                return [
                    'company_id' => (int) $membership['company_id'],
                    'tenant_roles' => array_values(array_intersect(
                        $membership['tenant_roles'] ?? [],
                        self::TENANT_ROLES
                    )),
                ];
            })
            ->unique('company_id')
            ->values();

        $companyIds = $normalizedMemberships->pluck('company_id')->all();

        $syncPayload = [];
        foreach ($companyIds as $companyId) {
            $syncPayload[$companyId] = [
                'role' => 'member',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $user->companies()->sync($syncPayload);

        foreach (self::TENANT_ROLES as $roleName) {
            Role::findOrCreate($roleName, 'web');
        }

        $allCompanyIds = Company::query()->pluck('id')->all();

        foreach ($allCompanyIds as $companyId) {
            app(PermissionRegistrar::class)->setPermissionsTeamId($companyId);

            foreach (self::TENANT_ROLES as $roleName) {
                if ($user->hasRole($roleName)) {
                    $user->removeRole($roleName);
                }
            }
        }

        foreach ($normalizedMemberships as $membership) {
            $companyId = $membership['company_id'];
            $tenantRoles = $membership['tenant_roles'];

            app(PermissionRegistrar::class)->setPermissionsTeamId($companyId);

            foreach ($tenantRoles as $roleName) {
                $user->assignRole($roleName);
            }
        }

        app(PermissionRegistrar::class)->setPermissionsTeamId(0);
    }

    private function getAvailableGlobalRoles(): array
    {
        foreach (self::GLOBAL_ROLES as $roleName) {
            Role::findOrCreate($roleName, 'web');
        }

        return Role::query()
            ->whereNull('company_id')
            ->whereIn('name', self::GLOBAL_ROLES)
            ->orderBy('name')
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
        foreach (self::TENANT_ROLES as $roleName) {
            Role::findOrCreate($roleName, 'web');
        }

        return Role::query()
            ->whereNull('company_id')
            ->whereIn('name', self::TENANT_ROLES)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Role $role) => [
                'id' => $role->id,
                'name' => $role->name,
            ])
            ->values()
            ->all();
    }

    private function getUserGlobalRoles(User $user): array
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId(0);

        return $user->roles
            ->whereIn('name', self::GLOBAL_ROLES)
            ->pluck('name')
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

        return $memberships->map(function ($membership) use ($user) {
            app(PermissionRegistrar::class)->setPermissionsTeamId($membership->company_id);

            $tenantRoles = $user->roles
                ->whereIn('name', self::TENANT_ROLES)
                ->pluck('name')
                ->values()
                ->all();

            return [
                'company_id' => $membership->company_id,
                'company_name' => $membership->company_name,
                'subdomain' => $membership->subdomain,
                'industry' => $membership->industry,
                'tenant_roles' => $tenantRoles,
            ];
        })->values()->all();
    }
}
