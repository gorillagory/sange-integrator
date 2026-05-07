<?php

// app/Models/BookingService.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingService extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'booking_id',
        'company_id',
        'service_schema_id',
        'service_type',
        'service_name',
        'service_details',
        'qty',
        'unit_fare',
        'tax_type',
        'tax_value',
        'tax_amount',
        'client_price',
        'line_total',
        'sort_order',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'service_details' => 'array',
            'payload' => 'array',
            'unit_fare' => 'decimal:2',
            'tax_value' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'client_price' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function schema(): BelongsTo
    {
        return $this->belongsTo(ServiceSchema::class, 'service_schema_id');
    }
}
