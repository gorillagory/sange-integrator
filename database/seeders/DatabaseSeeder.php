<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RbacSeeder::class,
            GenesisSeeder::class,
            ModuleSeeder::class,
            CompanyModuleSeeder::class,
            SchemaSeeder::class,
            TemplateSeeder::class,
            DocumentTemplateSeeder::class,
        ]);
    }
}
