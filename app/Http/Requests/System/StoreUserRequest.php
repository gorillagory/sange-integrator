<?php

namespace App\Http\Requests\System;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc,dns', 'max:255', 'unique:control.users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'global_roles' => ['nullable', 'array'],
            'global_roles.*' => ['string'],
            'memberships' => ['nullable', 'array'],
            'memberships.*.company_id' => ['required', 'integer', 'exists:control.companies,id'],
            'memberships.*.tenant_roles' => ['nullable', 'array'],
            'memberships.*.tenant_roles.*' => ['string'],
        ];
    }
}
