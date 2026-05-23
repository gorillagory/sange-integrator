<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\PermissionRegistrar;

class RbacController extends Controller
{
    public function index(): Response
    {
        $company = $this->resolveCompany();

        $companyRoles = Role::query()
            ->where('company_id', $company->id)
            ->orderBy('name')
            ->get(['id', 'name', 'company_id']);

        $availableMembers = DB::connection('control')
            ->table('company_user')
            ->join('users', 'users.id', '=', 'company_user.user_id')
            ->where('company_user.company_id', $company->id)
            ->orderBy('users.name')
            ->get([
                'users.id',
                'users.name',
                'users.email',
            ])
            ->map(fn ($member) => [
                'id' => (int) $member->id,
                'name' => $member->name,
                'email' => $member->email,
            ])
            ->values()
            ->all();

        $memberRoleRows = DB::connection('control')
            ->table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.company_id', $company->id)
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
            ->where('model_has_roles.company_id', $company->id)
            ->where('model_has_roles.model_type', 'App\\Models\\User')
            ->orderBy('roles.name')
            ->orderBy('users.name')
            ->get([
                'roles.name as role_name',
                'users.id as user_id',
                'users.name as user_name',
                'users.email as user_email',
            ])
            ->groupBy('role_name');

        $roles = $companyRoles
            ->map(function (Role $role) use ($membersByRole) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'permissions' => $role->permissions()
                        ->orderBy('name')
                        ->pluck('name')
                        ->values()
                        ->all(),
                    'is_protected' => false,
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
            ->all();

        return Inertia::render('Admin/Rbac/Index', [
            'company' => [
                'id' => $company->id,
                'name' => $company->name,
                'subdomain' => $company->subdomain,
            ],
            'roles' => $roles,
            'members' => $availableMembers,
            'memberDirectory' => collect($availableMembers)
                ->map(function (array $member) use ($memberRoleRows) {
                    $roleRows = collect($memberRoleRows->get($member['id'], collect()));

                    return [
                        ...$member,
                        'role_ids' => $roleRows->pluck('role_id')->map(fn ($id) => (int) $id)->values()->all(),
                        'roles' => $roleRows->pluck('role_name')->values()->all(),
                    ];
                })
                ->values()
                ->all(),
            'permissions' => array_values(config('rbac.tenant_permissions', [])),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $company = $this->resolveCompany();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'permissions' => ['array'],
            'permissions.*' => ['string'],
            'member_ids' => ['array'],
            'member_ids.*' => ['integer'],
        ]);

        $roleName = trim((string) $validated['name']);

        abort_if($roleName === '', 422, 'Role name is required.');

        $roleExists = Role::query()
            ->where('company_id', $company->id)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($roleName)])
            ->exists();

        abort_if($roleExists, 422, 'A role with this name already exists for the company.');

        $permissions = $this->validatedPermissions($validated['permissions'] ?? []);
        $memberIds = $this->validatedMemberIds($company->id, $validated['member_ids'] ?? []);

        $role = Role::create([
            'name' => $roleName,
            'guard_name' => 'web',
            'company_id' => $company->id,
        ]);

        app(PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $role->syncPermissions($permissions);
        $this->syncRoleMembers($company->id, $role, $memberIds);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()
            ->route('admin.rbac.index', ['subdomain' => $company->subdomain], 303)
            ->with('success', 'Role created successfully.');
    }

    public function update(Request $request, string $subdomain, $roleId): RedirectResponse
    {
        $company = $this->resolveCompany();
        $role = $this->roleForCompany(
            $company->id,
            (int) $roleId,
            $request->input('name')
        );

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'permissions' => ['array'],
            'permissions.*' => ['string'],
            'member_ids' => ['array'],
            'member_ids.*' => ['integer'],
        ]);

        $permissions = $this->validatedPermissions($validated['permissions'] ?? []);
        $memberIds = $this->validatedMemberIds($company->id, $validated['member_ids'] ?? []);
        $newName = trim((string) $validated['name']);
        abort_if($newName === '', 422, 'Role name is required.');

        $roleExists = Role::query()
            ->where('company_id', $company->id)
            ->where('id', '<>', $role->id)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($newName)])
            ->exists();

        abort_if($roleExists, 422, 'A role with this name already exists for the company.');

        $role->update(['name' => $newName]);

        app(PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $role->syncPermissions($permissions);
        $this->syncRoleMembers($company->id, $role, $memberIds);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()
            ->route('admin.rbac.index', ['subdomain' => $company->subdomain], 303)
            ->with('success', 'Role updated successfully.');
    }

    public function destroy(string $subdomain, $roleId): RedirectResponse
    {
        $company = $this->resolveCompany();
        $role = $this->roleForCompany($company->id, (int) $roleId);

        DB::connection('control')
            ->table('model_has_roles')
            ->where('company_id', $company->id)
            ->where('role_id', $role->id)
            ->delete();

        $role->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()
            ->route('admin.rbac.index', ['subdomain' => $company->subdomain], 303)
            ->with('success', 'Role deleted successfully.');
    }

    public function updateMemberRoles(Request $request, string $subdomain, $userId): RedirectResponse
    {
        $company = $this->resolveCompany();
        $userId = (int) $userId;

        $validated = $request->validate([
            'role_ids' => ['array'],
            'role_ids.*' => ['integer'],
        ]);

        abort_unless(User::query()->whereKey($userId)->exists(), 404);

        $isMember = DB::connection('control')
            ->table('company_user')
            ->where('company_id', $company->id)
            ->where('user_id', $userId)
            ->exists();

        if (! $isMember) {
            DB::connection('control')
                ->table('company_user')
                ->insert([
                    'company_id' => $company->id,
                    'user_id' => $userId,
                    'role' => 'member',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
        }

        $allowedRoleIds = Role::query()
            ->where('company_id', $company->id)
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
            ->where('company_id', $company->id)
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
                    'company_id' => $company->id,
                ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()
            ->route('admin.rbac.index', ['subdomain' => $company->subdomain], 303)
            ->with('success', 'User roles updated successfully.');
    }

    private function resolveCompany(): Company
    {
        /** @var Company|null $company */
        $company = app()->bound('currentCompany') ? app('currentCompany') : view()->shared('currentCompany');

        abort_unless($company instanceof Company, 403, 'Tenant context is missing.');

        return $company;
    }

    private function roleForCompany(int $companyId, int $roleId, ?string $roleName = null): Role
    {
        $role = Role::query()
            ->where('company_id', $companyId)
            ->where('id', $roleId)
            ->first();

        if ($role instanceof Role) {
            return $role;
        }

        $normalizedName = trim((string) $roleName);

        if ($normalizedName !== '') {
            $role = Role::query()
                ->where('company_id', $companyId)
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($normalizedName)])
                ->first();

            if ($role instanceof Role) {
                return $role;
            }
        }

        return Role::query()
            ->where('company_id', $companyId)
            ->where('id', $roleId)
            ->firstOrFail();
    }

    private function validatedPermissions(array $permissions): array
    {
        $allowed = array_values(config('rbac.tenant_permissions', []));

        return collect($permissions)
            ->map(fn ($permission) => (string) $permission)
            ->filter(fn (string $permission) => in_array($permission, $allowed, true))
            ->unique()
            ->values()
            ->all();
    }

    private function validatedMemberIds(int $companyId, array $memberIds): array
    {
        $allowedIds = DB::connection('control')
            ->table('company_user')
            ->where('company_id', $companyId)
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return collect($memberIds)
            ->map(fn ($memberId) => (int) $memberId)
            ->filter(fn (int $memberId) => in_array($memberId, $allowedIds, true))
            ->unique()
            ->values()
            ->all();
    }

    private function syncRoleMembers(int $companyId, Role $role, array $memberIds): void
    {
        DB::connection('control')
            ->table('model_has_roles')
            ->where('company_id', $companyId)
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
                    'company_id' => $companyId,
                ]);
        }
    }
}
