<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\MainGroupCompany;
use App\Models\Module;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanyModuleSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::connection('control')->transaction(function () use ($now) {
            $group = MainGroupCompany::query()->updateOrCreate(
                ['name' => 'Bayam Group'],
                [
                    'registration_number' => 'BAYAM-GROUP',
                    'address' => null,
                    'phones' => [],
                    'enterprise_types' => ['medical', 'travel', 'enterprise'],
                    'logo_path' => null,
                    'is_active' => true,
                    'updated_at' => $now,
                ]
            );

            $companies = [
                [
                    'name' => 'Bayamedic Services Sdn Bhd',
                    'registration_number' => 'BAYAM-MEDICAL-001',
                    'subdomain' => 'bner',
                    'db_name' => 'sange_tenant_bner',
                    'industry' => 'medical',
                    'theme_color' => '#1D4ED8',
                ],
                [
                    'name' => 'Bayam Travel Sdn Bhd',
                    'registration_number' => 'BAYAM-TRAVEL-001',
                    'subdomain' => 'bt',
                    'db_name' => 'sange_tenant_bt',
                    'industry' => 'travel',
                    'theme_color' => '#0F766E',
                ],
                [
                    'name' => 'Bayam Enterprise Sdn Bhd',
                    'registration_number' => 'BAYAM-ENTERPRISE-001',
                    'subdomain' => 'enterprise',
                    'db_name' => 'sange_tenant_enterprise',
                    'industry' => 'enterprise',
                    'theme_color' => '#7C3AED',
                ],
            ];

            $seededCompanies = collect($companies)->map(function (array $payload) use ($group, $now) {
                return Company::query()->updateOrCreate(
                    ['subdomain' => $payload['subdomain']],
                    [
                        'main_group_company_id' => $group->id,
                        'name' => $payload['name'],
                        'registration_number' => $payload['registration_number'],
                        'db_name' => $payload['db_name'],
                        'industry' => $payload['industry'],
                        'address' => null,
                        'phones' => [],
                        'enterprise_types' => [$payload['industry']],
                        'logo_path' => null,
                        'theme_color' => $payload['theme_color'],
                        'is_active' => true,
                        'updated_at' => $now,
                    ]
                );
            });

            $moduleIdsByIndustry = Module::query()
                ->where('is_active', true)
                ->get(['id', 'industry'])
                ->groupBy('industry')
                ->map(fn ($rows) => $rows->pluck('id')->values());

            foreach ($seededCompanies as $company) {
                $moduleIds = $moduleIdsByIndustry->get($company->industry, collect());

                foreach ($moduleIds as $moduleId) {
                    $company->modules()->syncWithoutDetaching([
                        $moduleId => [
                            'enabled_at' => $now,
                            'settings_json' => null,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ],
                    ]);
                }
            }
        });

        $this->command?->info('Seeded Bayam Group companies and module assignments.');
    }
}
