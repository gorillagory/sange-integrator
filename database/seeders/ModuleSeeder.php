<?php

// database/seeders/ModuleSeeder.php

namespace Database\Seeders;

use App\Models\Module;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $modules = [
            [
                'key' => 'travel.booking',
                'industry' => 'travel',
                'name' => 'Booking',
                'description' => 'Travel booking operations, services, clients, and invoicing flow.',
                'is_core' => true,
                'is_active' => true,
            ],
            [
                'key' => 'travel.documents',
                'industry' => 'travel',
                'name' => 'Documents',
                'description' => 'Document builder, templates, previews, and PDF generation.',
                'is_core' => true,
                'is_active' => true,
            ],
            [
                'key' => 'travel.schemas',
                'industry' => 'travel',
                'name' => 'Service Schemas',
                'description' => 'Dynamic service blueprints for travel operations.',
                'is_core' => true,
                'is_active' => true,
            ],
        ];

        foreach ($modules as $module) {
            Module::query()->updateOrCreate(
                ['key' => $module['key']],
                array_merge($module, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]),
            );
        }
    }
}
