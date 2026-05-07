<?php

// app/Models/Module.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Module extends Model
{
    protected $connection = 'control';

    protected $fillable = [
        'key',
        'industry',
        'name',
        'description',
        'is_core',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_core' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_modules', 'module_id', 'company_id')
            ->withPivot(['enabled_at', 'settings_json'])
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
