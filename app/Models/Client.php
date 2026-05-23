<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use Auditable;

    // 🔒 THE VAULT ANCHOR: This makes it a globally shared resource
    protected $connection = 'control';

    protected $fillable = [
        'name',
        'registration_number',
        'logo_path',
        'hq_contact_person',
        'hq_contact_email',
        'address',
        'profile',
    ];

    protected $appends = [
        'logo_url',
    ];

    protected function logoUrl(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                if (! $this->logo_path) {
                    return null;
                }

                if (str_starts_with($this->logo_path, 'http://') || str_starts_with($this->logo_path, 'https://')) {
                    return $this->logo_path;
                }

                return str_starts_with($this->logo_path, '/storage/')
                    ? $this->logo_path
                    : '/storage/' . ltrim(str_replace('storage/', '', $this->logo_path), '/');
            }
        );
    }

    // A global client can have many localized contracts across different tenants
    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function remarkPresets(): HasMany
    {
        return $this->hasMany(ClientRemarkPreset::class)->orderBy('title');
    }
}
