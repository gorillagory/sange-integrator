<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    // 🔒 THE VAULT ANCHOR: This makes it a globally shared resource
    protected $connection = 'control';

    protected $fillable = [
        'name',
        'registration_number',
        'logo_path',
        'hq_contact_person',
        'hq_contact_email'
    ];

    // A global client can have many localized contracts across different tenants
    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }
}
