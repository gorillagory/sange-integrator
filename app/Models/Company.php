<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    // 🛡️ From your migration: $table->softDeletes();
    // This ensures if you delete a company, their data isn't wiped instantly.
    use SoftDeletes;

    // 🔒 THE VAULT ANCHOR
    // Forces this model to NEVER touch a tenant database
    protected $connection = 'control';

    protected $fillable = [
        'name',
        'subdomain',
        'db_name',
        'industry',
        'is_active',
        'theme_color' // Added for future UI customization
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
