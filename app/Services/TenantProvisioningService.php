<?php

namespace App\Services;

use App\Models\Company;
use App\Models\MainGroupCompany;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class TenantProvisioningService
{
    public function __construct(
        private readonly RbacMatrixService $rbacMatrix,
    ) {}

    public function provision(array $validated): array
    {
        $createdMainGroupId = null;
        $createdCompanyId = null;
        $createdDatabaseName = null;

        try {
            [$mainGroup, $company] = DB::connection('control')->transaction(function () use ($validated, &$createdMainGroupId, &$createdCompanyId) {
                $mainGroup = $this->resolveMainGroupCompany($validated);

                if ($validated['main_group_mode'] === 'new') {
                    $createdMainGroupId = $mainGroup->id;
                }

                $company = $this->createCompany($mainGroup, $validated['company'] ?? []);
                $createdCompanyId = $company->id;

                return [$mainGroup, $company];
            });

            $createdDatabaseName = $company->db_name;

            $this->createTenantDatabase($company->db_name);
            $this->runTenantMigrations($company);
            $this->rbacMatrix->bootstrapTenantRoles((int) $company->id);

            return [$mainGroup, $company];
        } catch (Throwable $exception) {
            if ($createdDatabaseName) {
                $this->dropTenantDatabaseIfExists($createdDatabaseName);
            }

            if ($createdCompanyId) {
                Company::query()->whereKey($createdCompanyId)->delete();
            }

            if ($createdMainGroupId) {
                $hasChildren = Company::query()
                    ->where('main_group_company_id', $createdMainGroupId)
                    ->exists();

                if (! $hasChildren) {
                    MainGroupCompany::query()->whereKey($createdMainGroupId)->delete();
                }
            }

            throw $exception;
        }
    }

    private function resolveMainGroupCompany(array $validated): MainGroupCompany
    {
        if (($validated['main_group_mode'] ?? null) === 'existing') {
            return MainGroupCompany::query()->findOrFail($validated['main_group_company_id']);
        }

        $payload = $validated['main_group'] ?? [];

        return MainGroupCompany::create([
            'name' => $payload['name'],
            'registration_number' => $payload['registration_number'] ?? null,
            'address' => $payload['address'] ?? null,
            'phones' => $payload['phones'] ?? [],
            'enterprise_types' => $payload['enterprise_types'] ?? [],
            'logo_path' => $this->storeLogo($payload['logo'] ?? null, 'group-logos'),
            'is_active' => true,
        ]);
    }

    private function createCompany(MainGroupCompany $mainGroup, array $payload): Company
    {
        $subdomain = Str::lower($payload['subdomain']);
        $dbName = ! empty($payload['db_name'])
            ? $this->normalizeDatabaseName($payload['db_name'])
            : $this->generateDatabaseName($subdomain);

        return Company::create([
            'main_group_company_id' => $mainGroup->id,
            'name' => $payload['name'],
            'registration_number' => $payload['registration_number'] ?? null,
            'subdomain' => $subdomain,
            'db_name' => $dbName,
            'industry' => $payload['industry'],
            'address' => $payload['address'] ?? null,
            'phones' => $payload['phones'] ?? [],
            'enterprise_types' => $payload['enterprise_types'] ?? [],
            'logo_path' => $this->storeLogo($payload['logo'] ?? null, 'company-logos'),
            'theme_color' => $payload['theme_color'] ?? null,
            'is_active' => $payload['is_active'] ?? true,
        ]);
    }

    private function storeLogo(?UploadedFile $file, string $folder): ?string
    {
        if (! $file) {
            return null;
        }

        return $file->store($folder, 'public');
    }

    private function generateDatabaseName(string $subdomain): string
    {
        return $this->normalizeDatabaseName('tenant_' . $subdomain);
    }

    private function normalizeDatabaseName(string $value): string
    {
        $normalized = Str::of($value)
            ->lower()
            ->replace('-', '_')
            ->replace('.', '_')
            ->replace(' ', '_')
            ->value();

        if (! preg_match('/^[a-zA-Z0-9_]+$/', $normalized)) {
            throw new \RuntimeException('Tenant database name is invalid.');
        }

        return $normalized;
    }

    private function createTenantDatabase(string $databaseName): void
    {
        $databaseName = $this->normalizeDatabaseName($databaseName);

        $control = config('database.connections.control');

        Config::set('database.connections.control_provisioner', [
            'driver' => $control['driver'],
            'host' => $control['host'],
            'port' => $control['port'],
            'database' => 'postgres',
            'username' => $control['username'],
            'password' => $control['password'],
            'charset' => $control['charset'] ?? 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => $control['schema'] ?? 'public',
            'sslmode' => $control['sslmode'] ?? 'prefer',
        ]);

        DB::purge('control_provisioner');
        DB::reconnect('control_provisioner');

        $exists = DB::connection('control_provisioner')
            ->selectOne('SELECT 1 FROM pg_database WHERE datname = ?', [$databaseName]);

        if ($exists) {
            throw new \RuntimeException("Tenant database [{$databaseName}] already exists.");
        }

        DB::connection('control_provisioner')
            ->statement('CREATE DATABASE "' . str_replace('"', '""', $databaseName) . '"');
    }

    private function dropTenantDatabaseIfExists(string $databaseName): void
    {
        $databaseName = $this->normalizeDatabaseName($databaseName);

        $control = config('database.connections.control');

        Config::set('database.connections.control_provisioner', [
            'driver' => $control['driver'],
            'host' => $control['host'],
            'port' => $control['port'],
            'database' => 'postgres',
            'username' => $control['username'],
            'password' => $control['password'],
            'charset' => $control['charset'] ?? 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => $control['schema'] ?? 'public',
            'sslmode' => $control['sslmode'] ?? 'prefer',
        ]);

        DB::purge('control_provisioner');
        DB::reconnect('control_provisioner');

        DB::connection('control_provisioner')->statement("
            SELECT pg_terminate_backend(pid)
            FROM pg_stat_activity
            WHERE datname = ?
              AND pid <> pg_backend_pid()
        ", [$databaseName]);

        DB::connection('control_provisioner')
            ->statement('DROP DATABASE IF EXISTS "' . str_replace('"', '""', $databaseName) . '"');
    }

    private function runTenantMigrations(Company $company): void
    {
        Config::set('database.connections.tenant.database', $company->db_name);
        DB::purge('tenant');
        DB::reconnect('tenant');

        $sharedPath = 'database/migrations/tenant/shared';
        if (is_dir(base_path($sharedPath))) {
            Artisan::call('migrate', [
                '--database' => 'tenant',
                '--path' => $sharedPath,
                '--force' => true,
            ]);
        }

        $industryPath = 'database/migrations/tenant/' . strtolower($company->industry);
        if (is_dir(base_path($industryPath))) {
            Artisan::call('migrate', [
                '--database' => 'tenant',
                '--path' => $industryPath,
                '--force' => true,
            ]);
        }

        DB::purge('tenant');
        DB::reconnect('tenant');
    }
}
