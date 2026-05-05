<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Http\Requests\System\StoreCompanyRequest;
use App\Models\Company;
use App\Models\MainGroupCompany;
use App\Services\TenantProvisioningService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
    public function index(): Response
    {
        $companies = Company::query()
            ->with('mainGroupCompany:id,name')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->through(function (Company $company) {
                return [
                    'id' => $company->id,
                    'name' => $company->name,
                    'registration_number' => $company->registration_number,
                    'subdomain' => $company->subdomain,
                    'db_name' => $company->db_name,
                    'industry' => $company->industry,
                    'address' => $company->address,
                    'phones' => $company->phones,
                    'enterprise_types' => $company->enterprise_types,
                    'logo_path' => $company->logo_path,
                    'theme_color' => $company->theme_color,
                    'is_active' => $company->is_active,
                    'vault_url' => $this->tenantDashboardUrl($company),
                    'main_group_company' => $company->mainGroupCompany
                        ? [
                            'id' => $company->mainGroupCompany->id,
                            'name' => $company->mainGroupCompany->name,
                        ]
                        : null,
                    'created_at' => optional($company->created_at)?->toDateTimeString(),
                ];
            });

        $mainGroupCompanies = MainGroupCompany::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function (MainGroupCompany $group) {
                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'registration_number' => $group->registration_number,
                    'address' => $group->address,
                    'phones' => $group->phones,
                    'enterprise_types' => $group->enterprise_types,
                    'logo_path' => $group->logo_path,
                    'companies_count' => $group->companies()->count(),
                ];
            })
            ->values();

        return Inertia::render('System/Companies/Index', [
            'companies' => $companies,
            'mainGroupCompanies' => $mainGroupCompanies,
            'industries' => ['travel', 'medical', 'enterprise'],
        ]);
    }

    public function store(StoreCompanyRequest $request, TenantProvisioningService $provisioningService): RedirectResponse
    {
        [$mainGroup, $company] = $provisioningService->provision($request->validated());

        return redirect()
            ->route('system.companies.index')
            ->with('success', "Company [{$company->name}] provisioned under group [{$mainGroup->name}] successfully.");
    }

    private function tenantDashboardUrl(Company $company): string
    {
        $appUrl = rtrim((string) config('app.url', 'http://bayam.test:8000'), '/');
        $parts = parse_url($appUrl);

        $scheme = $parts['scheme'] ?? 'http';
        $host = $parts['host'] ?? 'bayam.test';
        $port = isset($parts['port']) ? ':'.$parts['port'] : '';

        if (str_starts_with($host, 'sys.')) {
            $host = substr($host, 4);
        }

        if (str_starts_with($host, 'www.')) {
            $host = substr($host, 4);
        }

        return "{$scheme}://{$company->subdomain}.{$host}{$port}/dashboard";
    }
}
