<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceRecordProjection extends Model
{
    protected $connection = 'tenant';

    protected $table = 'service_record_projections';

    protected $fillable = [
        'service_group_key',
        'service_record_id',
        'service_record_row_id',
        'schema_vector_id',
        'service_code',
        'schema_version',
        'captured_at',
        'dimensions',
        'metrics',
        'raw_payload',
    ];

    protected function casts(): array
    {
        return [
            'captured_at' => 'datetime',
            'dimensions' => 'array',
            'metrics' => 'array',
            'raw_payload' => 'array',
            'schema_version' => 'integer',
        ];
    }

    public function serviceRecord(): BelongsTo
    {
        return $this->belongsTo(ServiceRecord::class);
    }

    public function operation(): BelongsTo
    {
        return $this->serviceRecord();
    }

    public function serviceRecordRow(): BelongsTo
    {
        return $this->belongsTo(ServiceRecordRow::class);
    }

    public function serviceInstance(): BelongsTo
    {
        return $this->serviceRecordRow();
    }
}
