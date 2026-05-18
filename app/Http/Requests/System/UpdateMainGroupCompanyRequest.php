<?php

namespace App\Http\Requests\System;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMainGroupCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'group.name' => ['required', 'string', 'max:255'],
            'group.registration_number' => ['nullable', 'string', 'max:100'],
            'group.address' => ['nullable', 'array'],
            'group.address.line1' => ['nullable', 'string', 'max:255'],
            'group.address.line2' => ['nullable', 'string', 'max:255'],
            'group.address.city' => ['nullable', 'string', 'max:120'],
            'group.address.state' => ['nullable', 'string', 'max:120'],
            'group.address.postcode' => ['nullable', 'string', 'max:30'],
            'group.address.country' => ['nullable', 'string', 'max:120'],
            'group.phones' => ['nullable', 'array'],
            'group.phones.*.label' => ['nullable', 'string', 'max:100'],
            'group.phones.*.type' => ['nullable', 'string', 'max:100'],
            'group.phones.*.number' => ['required_with:group.phones', 'string', 'max:50'],
            'group.enterprise_types' => ['nullable', 'array'],
            'group.enterprise_types.*' => ['string', 'max:100'],
            'group.logo' => ['nullable', 'image', 'max:4096'],
            'group.is_active' => ['nullable', 'boolean'],
        ];
    }
}
