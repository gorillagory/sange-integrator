<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
//            AirportSeeder::class,
            RbacSeeder::class,      // 🟢 Creates Roles & Super Admin
//            SchemaSeeder::class,    // 🟢 Seeds Control DB
//            TemplateSeeder::class,  // 🟢 Seeds Tenant DB
        ]);
    }
}
