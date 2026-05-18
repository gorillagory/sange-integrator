<?php

namespace App\Http\Requests\System;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

class UpdateUserRequest extends FormRequest
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
            'global_roles.*' => ['string', 'in:' . implode(',', self::GLOBAL_ROLES)],

            'memberships' => ['nullable', 'array'],
            'memberships.*.company_id' => ['required', 'integer', 'exists:control.companies,id'],
            'memberships.*.tenant_roles' => ['nullable', 'array'],
            'memberships.*.tenant_roles.*' => ['string', 'in:' . implode(',', self::TENANT_ROLES)],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $globalRoles = $this->normalizeGlobalRoles();
            $memberships = $this->normalizeMemberships();

            $companyIds = collect($memberships)
                ->pluck('company_id')
                ->filter()
                ->values();

            if ($companyIds->count() !== $companyIds->unique()->count()) {
                $validator->errors()->add('memberships', 'Each company may only be assigned once.');
            }

            $isSuperAdmin = in_array('super_admin', $globalRoles, true);
            $isSystemAdmin = in_array('system_admin', $globalRoles, true);

            if (! $isSuperAdmin && ! $isSystemAdmin && empty($memberships)) {
                $validator->errors()->add('memberships', 'At least one company membership is required unless user is a system-level admin.');
            }

            foreach ($memberships as $index => $membership) {
                $tenantRoles = $membership['tenant_roles'] ?? [];

                if (! empty($tenantRoles) && empty($membership['company_id'])) {
                    $validator->errors()->add("memberships.{$index}.company_id", 'A company is required when assigning tenant roles.');
                }
            }
        });
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
