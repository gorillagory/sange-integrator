<?php

namespace App\Http\Requests\System;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'main_group_mode' => ['required', Rule::in(['existing', 'new'])],
            'main_group_company_id' => [
                'required_if:main_group_mode,existing',
                'nullable',
                'integer',
                'exists:control.main_group_companies,id',
            ],

            'main_group.name' => ['required_if:main_group_mode,new', 'nullable', 'string', 'max:255'],
            'main_group.registration_number' => ['nullable', 'string', 'max:100'],
            'main_group.address' => ['nullable', 'array'],
            'main_group.address.line1' => ['nullable', 'string', 'max:255'],
            'main_group.address.line2' => ['nullable', 'string', 'max:255'],
            'main_group.address.city' => ['nullable', 'string', 'max:120'],
            'main_group.address.state' => ['nullable', 'string', 'max:120'],
            'main_group.address.postcode' => ['nullable', 'string', 'max:30'],
            'main_group.address.country' => ['nullable', 'string', 'max:120'],
            'main_group.phones' => ['nullable', 'array'],
            'main_group.phones.*.label' => ['nullable', 'string', 'max:100'],
            'main_group.phones.*.type' => ['nullable', 'string', 'max:100'],
            'main_group.phones.*.number' => ['required_with:main_group.phones', 'string', 'max:50'],
            'main_group.enterprise_types' => ['nullable', 'array'],
            'main_group.enterprise_types.*' => ['string', 'max:100'],
            'main_group.logo' => ['nullable', 'image', 'max:4096'],

            'company.name' => ['required', 'string', 'max:255'],
            'company.registration_number' => ['nullable', 'string', 'max:100'],
            'company.subdomain' => ['required', 'alpha_dash', 'max:100', 'unique:control.companies,subdomain'],
            'company.db_name' => [
                'nullable',
                'string',
                'max:120',
                'regex:/^[A-Za-z0-9_-]+$/',
                'unique:control.companies,db_name',
            ],
            'company.industry' => ['required', Rule::in(['travel', 'medical', 'enterprise'])],
            'company.address' => ['nullable', 'array'],
            'company.address.line1' => ['nullable', 'string', 'max:255'],
            'company.address.line2' => ['nullable', 'string', 'max:255'],
            'company.address.city' => ['nullable', 'string', 'max:120'],
            'company.address.state' => ['nullable', 'string', 'max:120'],
            'company.address.postcode' => ['nullable', 'string', 'max:30'],
            'company.address.country' => ['nullable', 'string', 'max:120'],
            'company.phones' => ['nullable', 'array'],
            'company.phones.*.label' => ['nullable', 'string', 'max:100'],
            'company.phones.*.type' => ['nullable', 'string', 'max:100'],
            'company.phones.*.number' => ['required_with:company.phones', 'string', 'max:50'],
            'company.enterprise_types' => ['nullable', 'array'],
            'company.enterprise_types.*' => ['string', 'max:100'],
            'company.logo' => ['nullable', 'image', 'max:4096'],
            'company.theme_color' => ['nullable', 'string', 'max:20'],
            'company.is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'company.db_name.regex' => 'Database name may only contain letters, numbers, dashes, and underscores.',
        ];
    }
}
