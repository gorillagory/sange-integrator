<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceRecord extends Model
{
    protected $connection = 'tenant';

    protected $table = 'service_records';

    protected $dateFormat = 'Y-m-d H:i:s';

    protected $fillable = [
        'company_id',
        'reference_no',
        'service_group_key',
        'handler_key',
        'document_no',
        'invoice_no',
        'client_id',
        'contract_no',
        'client_remark_preset_id',
        'remarks',
        'total_amount',
        'status',
    ];

    protected $appends = [
        'document_no',
    ];

    protected function casts(): array
    {
        return [
            'client_remark_preset_id' => 'integer',
            'total_amount' => 'decimal:2',
        ];
    }

    protected function documentNo(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $attributes['document_no'] ?? $attributes['invoice_no'] ?? null,
            set: fn (mixed $value) => ['invoice_no' => $value],
        );
    }

    protected function serviceGroupKey(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $attributes['service_group_key'] ?? $attributes['handler_key'] ?? null,
            set: fn (mixed $value) => ['service_group_key' => $value],
        );
    }

    protected function handlerKey(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $attributes['handler_key'] ?? $attributes['service_group_key'] ?? null,
            set: fn (mixed $value) => ['service_group_key' => $value],
        );
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function clientRemarkPreset(): BelongsTo
    {
        return $this->belongsTo(ClientRemarkPreset::class, 'client_remark_preset_id');
    }

    public function rows(): HasMany
    {
        return $this->hasMany(ServiceRecordRow::class)->orderBy('sort_order');
    }

    public function serviceInstances(): HasMany
    {
        return $this->rows();
    }

    public function serviceRecordRows(): HasMany
    {
        return $this->rows();
    }

    public function services(): HasMany
    {
        return $this->rows();
    }
}
