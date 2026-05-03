<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class RbacSeeder extends Seeder
{
    public function run(): void
    {
        // Flush cache before seeding
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Create the 3-Level Architecture
        $superAdminRole = Role::findOrCreate('super_admin');
        $agencyAdminRole = Role::findOrCreate('agency_admin');
        $travelAgentRole = Role::findOrCreate('travel_agent');

        // 2. The Genesis User (You)
        $admin = User::firstOrCreate(
            ['email' => 'admin@bayam.test'],
            [
                'name' => 'Admin Director',
                'password' => Hash::make('password'),
            ]
        );

        // 3. Hand over the keys to the kingdom
        $admin->assignRole($superAdminRole);

        $this->command->info('Genesis User and RBAC Tiers established successfully!');
    }
}
