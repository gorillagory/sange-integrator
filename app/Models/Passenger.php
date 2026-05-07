<?php

// app/Models/Passenger.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Passenger extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'booking_id',
        'company_id',
        'full_name',
        'passenger_type',
        'passport_no',
        'nationality',
        'date_of_birth',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'meta' => 'array',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
