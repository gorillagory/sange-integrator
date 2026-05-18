<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceRecordRow extends Model
{
    protected $connection = 'tenant';

    protected $table = 'service_record_rows';

    protected $dateFormat = 'Y-m-d H:i:s';

    protected $fillable = [
        'service_record_id',
        'operation_id',
        'company_id',
        'schema_vector_id',
        'service_schema_id',
        'service_type',
        'service_code',
        'schema_version',
        'service_name',
        'service_details',
        'service_details_extra',
        'qty',
        'unit_name',
        'base_cost',
        'supplier_cost',
        'unit_fare',
        'discount_type',
        'discount_value',
        'discount_amount',
        'tax_type',
        'tax_value',
        'tax_amount',
        'sell_price',
        'client_price',
        'line_total',
        'sort_order',
        'payload',
        'payload_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'service_details' => 'array',
            'service_details_extra' => 'array',
            'payload' => 'array',
            'payload_snapshot' => 'array',
            'schema_version' => 'integer',
            'base_cost' => 'decimal:2',
            'supplier_cost' => 'decimal:2',
            'unit_fare' => 'decimal:2',
            'discount_value' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_value' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'sell_price' => 'decimal:2',
            'client_price' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    protected function operationId(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $attributes['operation_id'] ?? $attributes['service_record_id'] ?? null,
            set: fn (mixed $value) => ['service_record_id' => $value],
        );
    }

    protected function serviceSchemaId(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $attributes['service_schema_id'] ?? $attributes['schema_vector_id'] ?? null,
            set: fn (mixed $value) => ['schema_vector_id' => $value],
        );
    }

    public function serviceRecord(): BelongsTo
    {
        return $this->belongsTo(ServiceRecord::class);
    }

    public function operation(): BelongsTo
    {
        return $this->serviceRecord();
    }

    public function schemaVector(): BelongsTo
    {
        return $this->belongsTo(SchemaVector::class, 'schema_vector_id');
    }

    public function schema(): BelongsTo
    {
        return $this->schemaVector();
    }
}
