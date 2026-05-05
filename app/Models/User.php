<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    protected $connection = 'control';

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_user', 'user_id', 'company_id')
            ->withPivot(['role'])
            ->withTimestamps();
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasGlobalRole('super_admin');
    }

    public function isSystemAdmin(): bool
    {
        return $this->hasGlobalRole('system_admin');
    }

    public function isSystemUser(): bool
    {
        return $this->isSuperAdmin() || $this->isSystemAdmin();
    }

    public function belongsToCompany(int $companyId): bool
    {
        return DB::connection('control')
            ->table('company_user')
            ->where('user_id', $this->id)
            ->where('company_id', $companyId)
            ->exists();
    }

    public function firstAccessibleCompany(): ?Company
    {
        return $this->companies()
            ->where('companies.is_active', true)
            ->orderBy('companies.name')
            ->first();
    }

    private function hasGlobalRole(string $roleName): bool
    {
        return DB::connection('control')
            ->table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_type', $this->getMorphClass())
            ->where('model_has_roles.model_id', $this->id)
            ->where('model_has_roles.company_id', 0)
            ->where('roles.name', $roleName)
            ->exists();
    }
}
