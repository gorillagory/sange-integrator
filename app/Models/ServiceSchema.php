<?php

namespace App\Models;

class ServiceSchema extends SchemaVector
{
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
}
