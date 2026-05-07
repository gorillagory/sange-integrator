<?php

// app/Models/Booking.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'company_id',
        'reference_no',
        'invoice_no',
        'client_id',
        'contract_no',
        'cart_payload',
        'passenger_details',
        'total_amount',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'cart_payload' => 'array',
            'passenger_details' => 'array',
            'total_amount' => 'decimal:2',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(BookingService::class)->orderBy('sort_order');
    }

    public function passengers(): HasMany
    {
        return $this->hasMany(Passenger::class);
    }
}
