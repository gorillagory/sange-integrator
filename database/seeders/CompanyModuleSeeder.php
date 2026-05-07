<?php

// database/seeders/CompanyModuleSeeder.php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Module;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CompanyModuleSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $travelModuleKeys = [
            'travel.booking',
            'travel.documents',
            'travel.schemas',
        ];

        $travelModuleIds = Module::query()
            ->whereIn('key', $travelModuleKeys)
            ->pluck('id', 'key');

        Company::query()
            ->where('industry', 'travel')
            ->get()
            ->each(function (Company $company) use ($travelModuleIds, $now) {
                foreach ($travelModuleIds as $moduleId) {
                    $company->modules()->syncWithoutDetaching([
                        $moduleId => [
                            'enabled_at' => $now,
                            'settings_json' => null,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ],
                    ]);
                }
            });
    }
}
