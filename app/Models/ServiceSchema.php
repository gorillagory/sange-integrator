<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceSchema extends Model
{
    // Force this model to ONLY look at the Control DB
    protected $connection = 'control';

    protected $fillable = [
        'industry', 'service_type', 'display_name', 'schema_payload'
    ];

    protected function casts(): array
    {
        return [
            'schema_payload' => 'array', // Auto-converts JSONB to Array
        ];
    }
}
