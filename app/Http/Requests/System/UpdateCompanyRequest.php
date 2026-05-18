<?php

namespace App\Http\Requests\System;

use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Company|null $company */
        $company = $this->route('company');
        $companyId = $company?->id;

        return [
            'company.main_group_company_id' => [
                'nullable',
                'integer',
                'exists:control.main_group_companies,id',
            ],
            'company.name' => ['required', 'string', 'max:255'],
            'company.registration_number' => ['nullable', 'string', 'max:100'],
            'company.subdomain' => [
                'required',
                'alpha_dash',
                'max:100',
                Rule::unique('control.companies', 'subdomain')->ignore($companyId),
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
}
