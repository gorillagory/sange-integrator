<?php

// app/Services/TenantDatabaseManager.php

namespace App\Services;

use App\Models\Company;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class TenantDatabaseManager
{
    public function connectCompany(Company $company): void
    {
        Config::set('database.connections.tenant.database', $company->db_name);

        DB::purge('tenant');
        DB::reconnect('tenant');
    }

    public function connectSubdomain(string $subdomain): Company
    {
        $company = Company::query()
            ->where('subdomain', $subdomain)
            ->where('is_active', true)
            ->firstOrFail();

        $this->connectCompany($company);

        return $company;
    }
}
