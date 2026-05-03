<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentTemplate extends Model
{
    // No $connection override needed; this naturally binds to the active Tenant DB

    protected $fillable = [
        'name',
        'code',
        'document_type',
        'layout_vector'
    ];

    // This ensures Laravel automatically converts the JSON from Vue into a DB array
    protected $casts = [
        'layout_vector' => 'array'
    ];
}
