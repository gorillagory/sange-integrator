<?php

namespace Database\Seeders;

use App\Services\RbacMatrixService;
use Illuminate\Database\Seeder;

class RbacSeeder extends Seeder
{
    public function run(): void
    {
        app(RbacMatrixService::class)->bootstrapGlobalRoles();

        $this->command->info('Global role catalog seeded successfully.');
    }
}
