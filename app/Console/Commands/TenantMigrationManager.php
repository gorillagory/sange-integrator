<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;

class TenantMigrationManager extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'tenant:migrate
                            {--refresh : Drop all tables and re-migrate}
                            {--seed : Seed the database with records}';

    /**
     * The console command description.
     */
    protected $description = 'Run migrations against all active physical tenant databases.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Initializing Nexus Tenant Migration Manager...');

        // 1. Fetch all active companies from the control database
        try {
            $companies = DB::connection('control')->table('companies')->where('is_active', true)->get();
        } catch (\Exception $e) {
            $this->error('CRITICAL: Could not read from sange_control. Did you migrate the control database?');
            return Command::FAILURE;
        }

        if ($companies->isEmpty()) {
            $this->warn('No active companies found in the control database. Add records to the `companies` table first.');
            return Command::SUCCESS;
        }

        // 2. Loop through each physical database
        foreach ($companies as $company) {
            $this->line("\n=========================================");
            $this->info("⚙️  Migrating Tenant: {$company->name} [DB: {$company->db_name}]");
            $this->line("=========================================");

            // 3. Dynamically reconfigure the database connection
            Config::set('database.connections.tenant.database', $company->db_name);
            DB::purge('tenant');      // Destroy old connection cache
            DB::reconnect('tenant');  // Connect to the new tenant DB

            // 4. Prepare Artisan command options
            $options = [
                '--database' => 'tenant',
                '--path' => 'database/migrations/tenant', // STRICTLY use the tenant folder
                '--force' => true, // Bypass production confirmation prompts
            ];

            if ($this->option('seed')) {
                $options['--seed'] = true;
            }

            $command = $this->option('refresh') ? 'migrate:refresh' : 'migrate';

            // 5. Execute the migration securely
            try {
                Artisan::call($command, $options, $this->output);
                $this->info("✅ Successfully migrated: {$company->name}");
            } catch (\Exception $e) {
                $this->error("❌ Migration failed for {$company->name}: " . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info('All physical tenant migrations completed successfully.');
        return Command::SUCCESS;
    }
}
