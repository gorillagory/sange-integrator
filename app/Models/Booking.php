<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'reference_no', 'invoice_no', 'client_id', 'contract_no',
        'cart_payload', 'passenger_details', 'total_amount', 'status'
    ];

    protected function casts(): array
    {
        return [
            'cart_payload' => 'array',
            'passenger_details' => 'array',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
