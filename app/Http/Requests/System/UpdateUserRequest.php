<?php

namespace App\Http\Requests\System;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

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
                'email:rfc,dns',
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
}
