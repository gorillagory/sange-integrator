<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contract extends Model
{
    // 🔒 THE VAULT ANCHOR: Forces Laravel to query the active Tenant DB
    protected $connection = 'tenant';

    protected $fillable = [
        'client_id',
        'contract_no',
        'title',
        'billing_address',
        'payment_terms'
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
