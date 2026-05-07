<?php

namespace App\Console\Commands;

use App\Models\Company;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Throwable;

class TenantMigrationManager extends Command
{
    protected $signature = 'tenant:migrate
                            {subdomain? : Optional tenant subdomain, e.g. btravel}
                            {--shared : Run only shared tenant migrations}
                            {--industry-only : Run only industry-specific migrations}
                            {--path= : Run one specific migration path}
                            {--refresh : Run migrate:refresh instead of migrate}
                            {--seed : Seed after migration when supported}
                            {--list : Show targeted tenants only, do not migrate}';

    protected $description = 'Run tenant migrations for one tenant or all active tenants using the dynamic tenant connection';

    public function handle(): int
    {
        $this->info('Initializing Tenant Migration Manager...');

        try {
            $companies = $this->resolveCompanies();
        } catch (Throwable $exception) {
            $this->error('Failed to resolve companies from the control database.');
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        if ($companies->isEmpty()) {
            $this->warn('No matching active companies found.');

            return self::SUCCESS;
        }

        if ($this->option('list')) {
            $this->renderCompanyList($companies);

            return self::SUCCESS;
        }

        $failures = 0;

        foreach ($companies as $company) {
            $this->newLine();
            $this->line('============================================================');
            $this->info("Tenant: {$company->name}");
            $this->line("Subdomain: {$company->subdomain}");
            $this->line("Database: {$company->db_name}");
            $this->line("Industry: {$company->industry}");
            $this->line('============================================================');

            if (! $company->db_name) {
                $this->warn("Skipping {$company->name}: missing db_name.");
                continue;
            }

            $paths = $this->resolvePathsForCompany($company);

            if (empty($paths)) {
                $this->warn("Skipping {$company->name}: no migration paths matched.");
                continue;
            }

            $this->line('Paths:');
            foreach ($paths as $path) {
                $this->line(" - {$path}");
            }

            try {
                $this->connectTenantDatabase($company->db_name);

                $command = $this->option('refresh') ? 'migrate:refresh' : 'migrate';

                $options = [
                    '--database' => 'tenant',
                    '--path' => $paths,
                    '--force' => true,
                ];

                if ($this->option('seed')) {
                    $options['--seed'] = true;
                }

                $exitCode = Artisan::call($command, $options, $this->output);

                $this->output->write(Artisan::output());

                if ($exitCode !== 0) {
                    $failures++;
                    $this->error("Migration command returned non-zero exit code for {$company->name}.");
                    continue;
                }

                $this->info("Migration completed for {$company->name}.");
            } catch (Throwable $exception) {
                $failures++;
                $this->error("Migration failed for {$company->name}.");
                $this->error($exception->getMessage());
            }
        }

        $this->newLine();

        if ($failures > 0) {
            $this->error("Tenant migration finished with {$failures} failure(s).");

            return self::FAILURE;
        }

        $this->info('Tenant migration finished successfully.');

        return self::SUCCESS;
    }

    private function resolveCompanies(): Collection
    {
        $query = Company::query()
            ->where('is_active', true)
            ->orderBy('name');

        $subdomain = $this->argument('subdomain');

        if ($subdomain) {
            $query->where('subdomain', $subdomain);
        }

        return $query->get([
            'id',
            'name',
            'subdomain',
            'db_name',
            'industry',
            'is_active',
        ]);
    }

    private function resolvePathsForCompany(Company $company): array
    {
        $customPath = $this->option('path');

        if ($customPath) {
            return $this->filterExistingPaths([$customPath]);
        }

        $paths = [];

        if (! $this->option('industry-only')) {
            $paths[] = 'database/migrations/tenant/shared';
        }

        if (! $this->option('shared')) {
            $paths[] = 'database/migrations/tenant/' . strtolower((string) $company->industry);
        }

        return $this->filterExistingPaths($paths);
    }

    private function filterExistingPaths(array $paths): array
    {
        return collect($paths)
            ->filter()
            ->map(fn ($path) => trim((string) $path))
            ->unique()
            ->filter(fn ($path) => is_dir(base_path($path)))
            ->values()
            ->all();
    }

    private function connectTenantDatabase(string $databaseName): void
    {
        Config::set('database.connections.tenant.database', $databaseName);

        DB::purge('tenant');
        DB::reconnect('tenant');
    }

    private function renderCompanyList(Collection $companies): void
    {
        $rows = $companies->map(fn (Company $company) => [
            'ID' => $company->id,
            'Name' => $company->name,
            'Subdomain' => $company->subdomain,
            'Database' => $company->db_name,
            'Industry' => $company->industry,
        ])->all();

        $this->table(
            ['ID', 'Name', 'Subdomain', 'Database', 'Industry'],
            $rows
        );
    }
}
