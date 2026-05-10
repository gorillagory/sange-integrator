<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class RbacSeeder extends Seeder
{
    private const GLOBAL_TEAM_ID = 0;

    private const GLOBAL_ROLES = [
        'super_admin',
        'system_admin',
    ];

    private const TENANT_ROLES = [
        'agency_admin',
        'travel_agent',
        'booking_manager',
        'document_manager',
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        app(PermissionRegistrar::class)->setPermissionsTeamId(self::GLOBAL_TEAM_ID);

        foreach (self::GLOBAL_ROLES as $roleName) {
            Role::findOrCreate($roleName, 'web');
        }

        foreach (self::TENANT_ROLES as $roleName) {
            Role::findOrCreate($roleName, 'web');
        }

        app(PermissionRegistrar::class)->setPermissionsTeamId(self::GLOBAL_TEAM_ID);

        $this->command->info('Global and tenant role catalog seeded successfully.');
    }
}
