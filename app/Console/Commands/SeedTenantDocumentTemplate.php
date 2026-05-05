<?php

namespace App\Console\Commands;

use App\Models\Company;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SeedTenantDocumentTemplate extends Command
{
    protected $signature = 'tenant:seed-document-template
                            {subdomain : Tenant subdomain, e.g. btravel}
                            {--class=DocumentTemplateSeeder : Seeder class to run}';

    protected $description = 'Seed document templates into a specific tenant database by subdomain';

    public function handle(): int
    {
        $subdomain = (string) $this->argument('subdomain');
        $seederClass = $this->normalizeSeederClass((string) $this->option('class'));

        /** @var \App\Models\Company|null $company */
        $company = Company::query()
            ->where('subdomain', $subdomain)
            ->where('is_active', true)
            ->first();

        if (! $company) {
            $this->error("Active company not found for subdomain [{$subdomain}].");
            return self::FAILURE;
        }

        if (! $company->db_name) {
            $this->error("Company [{$company->name}] does not have a tenant database name.");
            return self::FAILURE;
        }

        if (! class_exists($seederClass)) {
            $this->error("Seeder class [{$seederClass}] does not exist.");
            return self::FAILURE;
        }

        Config::set('database.connections.tenant.database', $company->db_name);

        DB::purge('tenant');
        DB::reconnect('tenant');

        $this->components->info("Connected tenant database [{$company->db_name}] for [{$company->name}].");

        $this->call('db:seed', [
            '--class' => $seederClass,
            '--database' => 'tenant',
            '--force' => true,
        ]);

        return self::SUCCESS;
    }

    private function normalizeSeederClass(string $class): string
    {
        $class = trim($class);

        if (Str::contains($class, '\\')) {
            return $class;
        }

        return 'Database\\Seeders\\'.$class;
    }
}
