<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class SchemaVector extends Model
{
    use Auditable;

    protected $connection = 'control';

    protected $table = 'schema_vectors';

    protected $fillable = [
        'industry',
        'service_group_key',
        'service_type',
        'display_name',
        'service_code',
        'service_name',
        'version',
        'status',
        'is_default',
        'schema_payload',
    ];

    protected function casts(): array
    {
        return [
            'schema_payload' => 'array',
            'version' => 'integer',
            'is_default' => 'boolean',
        ];
    }
}
