<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MainGroupCompany extends Model
{
    use Auditable;

    protected $connection = 'control';

    protected $fillable = [
        'name',
        'registration_number',
        'address',
        'phones',
        'enterprise_types',
        'logo_path',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'address' => 'array',
            'phones' => 'array',
            'enterprise_types' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function companies(): HasMany
    {
        return $this->hasMany(Company::class, 'main_group_company_id');
    }
}
