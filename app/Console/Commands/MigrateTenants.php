<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;

class MigrateTenants extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'tenants:migrate {--fresh : Drop all tables and re-migrate}';

    /**
     * The console command description.
     */
    protected $description = 'Run migrations specifically for all tenant databases';

    public function handle()
    {
        $this->info('Starting Modular Tenant Migrations...');

        // Fetch all active companies
        $companies = DB::connection('control')->table('companies')->where('is_active', true)->get();

        foreach ($companies as $company) {
            $this->info("==========================================");
            $this->info("Migrating Vault: {$company->name} | Industry: [" . strtoupper($company->industry) . "]");
            $this->info("==========================================");

            // Connect to the specific tenant
            Config::set('database.connections.tenant.database', $company->db_name);
            DB::purge('tenant');
            DB::reconnect('tenant');

            // --- STEP 1: RUN SHARED MIGRATIONS (Applies to all tenants) ---
            $this->line("-> Running Shared Core Migrations...");
            Artisan::call('migrate', [
                '--database' => 'tenant',
                '--path' => 'database/migrations/tenant/shared',
                '--force' => true,
            ], $this->output);

            // --- STEP 2: RUN INDUSTRY-SPECIFIC MIGRATIONS ---
            $industryFolder = 'database/migrations/tenant/' . strtolower($company->industry);

            // Check if the industry folder actually exists on the server
            if (is_dir(base_path($industryFolder))) {
                $this->line("-> Running [{$company->industry}] Module Migrations...");
                Artisan::call('migrate', [
                    '--database' => 'tenant',
                    '--path' => $industryFolder,
                    '--force' => true,
                ], $this->output);
            } else {
                $this->line("-> No specific module folder found for [{$company->industry}]. Skipping.");
            }
        }

        $this->info('All Tenant Vaults updated successfully.');
    }
}
