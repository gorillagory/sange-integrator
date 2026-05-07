<?php

// app/Models/Contract.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contract extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'company_id',
        'client_id',
        'contract_no',
        'title',
        'billing_address',
        'payment_terms',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
