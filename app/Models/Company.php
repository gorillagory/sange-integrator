<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use SoftDeletes;

    protected $connection = 'control';

    protected $fillable = [
        'main_group_company_id',
        'name',
        'registration_number',
        'subdomain',
        'db_name',
        'industry',
        'address',
        'phones',
        'enterprise_types',
        'logo_path',
        'theme_color',
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

    public function mainGroupCompany(): BelongsTo
    {
        return $this->belongsTo(MainGroupCompany::class, 'main_group_company_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'company_user', 'company_id', 'user_id')
            ->withPivot(['role'])
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
