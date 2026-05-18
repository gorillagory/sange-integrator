<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class BayamTravelRoleUsersSeeder extends Seeder
{
    private const PASSWORD = 'passG03r1lenc3';

    /**
     * One user for each role.
     */
    private const USERS = [
        [
            'name' => 'Bayam Travel Super Admin',
            'email' => 'travel.super_admin@bayam.com.my',
            'role' => 'super_admin',
            'scope' => 'global',
        ],
        [
            'name' => 'Bayam Travel System Admin',
            'email' => 'travel.system_admin@bayam.com.my',
            'role' => 'system_admin',
            'scope' => 'global',
        ],
        [
            'name' => 'Bayam Travel Agency Admin',
            'email' => 'travel.agency_admin@bayam.com.my',
            'role' => 'agency_admin',
            'scope' => 'tenant',
        ],
        [
            'name' => 'Bayam Travel Agent',
            'email' => 'travel.travel_agent@bayam.com.my',
            'role' => 'travel_agent',
            'scope' => 'tenant',
        ],
        [
            'name' => 'Bayam Travel Booking Manager',
            'email' => 'travel.booking_manager@bayam.com.my',
            'role' => 'booking_manager',
            'scope' => 'tenant',
        ],
        [
            'name' => 'Bayam Travel Document Manager',
            'email' => 'travel.document_manager@bayam.com.my',
            'role' => 'document_manager',
            'scope' => 'tenant',
        ],
    ];

    public function run(): void
    {
        $company = Company::query()
            ->where('subdomain', 'bt')
            ->first();

        if (! $company) {
            $this->command?->warn('Skipped BayamTravelRoleUsersSeeder: company [bt] not found.');

            return;
        }

        app(PermissionRegistrar::class)->setPermissionsTeamId(0);

        DB::connection('control')->transaction(function () use ($company): void {
            foreach (self::USERS as $definition) {
                $user = User::query()->updateOrCreate(
                    ['email' => $definition['email']],
                    [
                        'name' => $definition['name'],
                        'password' => Hash::make(self::PASSWORD),
                        'email_verified_at' => now(),
                    ]
                );

                $this->syncMembership($user, $company->id);
                $this->syncRole($user, $definition['role'], $definition['scope'], $company->id);
            }
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command?->info('Seeded Bayam Travel role users (one per role).');
        $this->command?->line('Shared password: '.self::PASSWORD);
    }

    private function syncMembership(User $user, int $companyId): void
    {
        DB::connection('control')
            ->table('company_user')
            ->updateOrInsert(
                [
                    'company_id' => $companyId,
                    'user_id' => $user->id,
                ],
                [
                    'role' => 'member',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
    }

    private function syncRole(User $user, string $roleName, string $scope, int $companyId): void
    {
        $role = Role::query()
            ->where('name', $roleName)
            ->where('guard_name', 'web')
            ->where('company_id', 0)
            ->first();

        if (! $role) {
            return;
        }

        $targetCompanyId = $scope === 'global' ? 0 : $companyId;

        DB::connection('control')
            ->table('model_has_roles')
            ->where('model_type', $user->getMorphClass())
            ->where('model_id', $user->id)
            ->where('company_id', $targetCompanyId)
            ->delete();

        DB::connection('control')
            ->table('model_has_roles')
            ->insertOrIgnore([
                'role_id' => $role->id,
                'model_type' => $user->getMorphClass(),
                'model_id' => $user->id,
                'company_id' => $targetCompanyId,
            ]);
    }
}
