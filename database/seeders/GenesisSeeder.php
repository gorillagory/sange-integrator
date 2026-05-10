<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class GenesisSeeder extends Seeder
{
    private const GLOBAL_TEAM_ID = 0;
    private const SUPER_ADMIN_ROLE = 'super_admin';
    private const SUPER_ADMIN_NAME = 'Gorilla Gorriball';
    private const SUPER_ADMIN_EMAIL = 'gori@bayam.com.my';
    private const SUPER_ADMIN_PASSWORD = 'passG03r1lenc3';

    public function run(): void
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId(self::GLOBAL_TEAM_ID);

        Role::findOrCreate(self::SUPER_ADMIN_ROLE, 'web');

        $user = User::query()->updateOrCreate(
            ['email' => self::SUPER_ADMIN_EMAIL],
            [
                'name' => self::SUPER_ADMIN_NAME,
                'password' => Hash::make(self::SUPER_ADMIN_PASSWORD),
                'email_verified_at' => now(),
            ]
        );

        $user->syncRoles([self::SUPER_ADMIN_ROLE]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command?->info('Genesis super administrator seeded.');
    }
}

