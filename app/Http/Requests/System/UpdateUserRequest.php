<?php

namespace App\Http\Requests\System;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id ?? $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('control.users', 'email')->ignore($userId),
            ],
            'password' => ['nullable', 'confirmed', Password::defaults()],

            'global_roles' => ['nullable', 'array'],
            'global_roles.*' => ['string'],

            'memberships' => ['nullable', 'array'],
            'memberships.*.company_id' => ['required', 'integer', 'exists:control.companies,id'],
            'memberships.*.tenant_roles' => ['nullable', 'array'],
            'memberships.*.tenant_roles.*' => ['string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $globalRoles = $this->normalizeGlobalRoles();
            $memberships = $this->normalizeMemberships();
            $allowedGlobalRoles = $this->allowedGlobalRoles();

            $companyIds = collect($memberships)
                ->pluck('company_id')
                ->filter()
                ->values();

            if ($companyIds->count() !== $companyIds->unique()->count()) {
                $validator->errors()->add('memberships', 'Each company may only be assigned once.');
            }

            foreach ($globalRoles as $roleName) {
                if (! in_array($roleName, $allowedGlobalRoles, true)) {
                    $validator->errors()->add('global_roles', "Global role [{$roleName}] is not available.");
                }
            }

            $isSuperAdmin = in_array('super_admin', $globalRoles, true);
            $isSystemAdmin = in_array('system_admin', $globalRoles, true);

            if (! $isSuperAdmin && ! $isSystemAdmin && empty($memberships)) {
                $validator->errors()->add('memberships', 'At least one company membership is required unless user is a system-level admin.');
            }

            foreach ($memberships as $index => $membership) {
                $tenantRoles = $membership['tenant_roles'] ?? [];
                $allowedTenantRoles = $this->allowedTenantRolesForCompany((int) ($membership['company_id'] ?? 0));

                if (! empty($tenantRoles) && empty($membership['company_id'])) {
                    $validator->errors()->add("memberships.{$index}.company_id", 'A company is required when assigning tenant roles.');
                }

                foreach ($tenantRoles as $roleName) {
                    if (! in_array($roleName, $allowedTenantRoles, true)) {
                        $validator->errors()->add("memberships.{$index}.tenant_roles", "Tenant role [{$roleName}] is not available for the selected company.");
                    }
                }
            }
        });
    }

    private function allowedGlobalRoles(): array
    {
        return Role::query()
            ->where('company_id', 0)
            ->whereNotIn('name', array_keys(config('rbac.tenant_role_permissions', [])))
            ->pluck('name')
            ->values()
            ->all();
    }

    private function allowedTenantRolesForCompany(int $companyId): array
    {
        if ($companyId <= 0) {
            return [];
        }

        return Role::query()
            ->where('company_id', $companyId)
            ->pluck('name')
            ->values()
            ->all();
    }

    private function normalizeGlobalRoles(): array
    {
        return collect($this->input('global_roles', []))
            ->filter(fn ($role) => is_string($role) && $role !== '')
            ->unique()
            ->values()
            ->all();
    }

    private function normalizeMemberships(): array
    {
        return collect($this->input('memberships', []))
            ->filter(fn ($membership) => is_array($membership) && ! empty($membership['company_id']))
            ->map(function (array $membership) {
                return [
                    'company_id' => (int) $membership['company_id'],
                    'tenant_roles' => collect($membership['tenant_roles'] ?? [])
                        ->filter(fn ($role) => is_string($role) && $role !== '')
                        ->unique()
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();
    }
}
