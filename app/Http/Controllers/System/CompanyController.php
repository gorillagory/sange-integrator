<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Http\Requests\System\StoreCompanyRequest;
use App\Http\Requests\System\UpdateCompanyRequest;
use App\Http\Requests\System\UpdateMainGroupCompanyRequest;
use App\Models\Company;
use App\Models\MainGroupCompany;
use App\Services\TenantProvisioningService;
use App\Support\AppHost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->string('search'));

        $companySearch = function ($query) use ($search) {
            $query->where(function ($inner) use ($search) {
                $inner->where('name', 'like', "%{$search}%")
                    ->orWhere('subdomain', 'like', "%{$search}%")
                    ->orWhere('db_name', 'like', "%{$search}%")
                    ->orWhere('industry', 'like', "%{$search}%")
                    ->orWhere('registration_number', 'like', "%{$search}%");
            });
        };

        $companyQuery = Company::query()
            ->with('mainGroupCompany:id,name,registration_number,address,phones,enterprise_types,logo_path,is_active')
            ->when($search !== '', function ($query) use ($search, $companySearch) {
                $query->where(function ($inner) use ($search, $companySearch) {
                    $companySearch($inner);

                    $inner->orWhereHas('mainGroupCompany', function ($groupQuery) use ($search) {
                        $groupQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('registration_number', 'like', "%{$search}%");
                    });
                });
            });

        $companies = (clone $companyQuery)
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Company $company) => $this->presentCompany($company));

        $activeCompanyCount = (clone $companyQuery)
            ->where('is_active', true)
            ->count();

        $mainGroupCompanies = MainGroupCompany::query()
            ->where('is_active', true)
            ->with([
                'companies' => function ($query) {
                    $query->with('mainGroupCompany:id,name,registration_number,address,phones,enterprise_types,logo_path,is_active')
                        ->orderBy('name');
                },
            ])
            ->when($search !== '', function ($query) use ($search, $companySearch) {
                $query->where(function ($inner) use ($search, $companySearch) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('registration_number', 'like', "%{$search}%")
                        ->orWhereHas('companies', function ($companyQuery) use ($companySearch) {
                            $companySearch($companyQuery);
                        });
                });
            })
            ->orderBy('name')
            ->get()
            ->map(fn (MainGroupCompany $group) => $this->presentMainGroup($group))
            ->values();

        $ungroupedCompanies = Company::query()
            ->with('mainGroupCompany:id,name,registration_number,address,phones,enterprise_types,logo_path,is_active')
            ->whereNull('main_group_company_id')
            ->when($search !== '', fn ($query) => $companySearch($query))
            ->orderBy('name')
            ->get()
            ->map(fn (Company $company) => $this->presentCompany($company))
            ->values();

        return Inertia::render('System/Companies/Index', [
            'companies' => $companies,
            'mainGroupCompanies' => $mainGroupCompanies,
            'ungroupedCompanies' => $ungroupedCompanies,
            'industries' => ['travel', 'medical', 'enterprise'],
            'filters' => [
                'search' => $search,
            ],
            'metrics' => [
                'active_company_count' => $activeCompanyCount,
            ],
        ]);
    }

    public function store(StoreCompanyRequest $request, TenantProvisioningService $provisioningService): RedirectResponse
    {
        [$mainGroup, $company] = $provisioningService->provision($request->validated());

        return redirect()
            ->route('system.companies.index')
            ->with('success', "Company [{$company->name}] provisioned under group [{$mainGroup->name}] successfully.");
    }

    public function update(UpdateCompanyRequest $request, Company $company): RedirectResponse
    {
        $payload = $request->validated('company');

        $logoPath = $company->logo_path;
        if (($payload['logo'] ?? null) instanceof UploadedFile) {
            $logoPath = $this->storeLogo($payload['logo'], 'company-logos');
        }

        $company->update([
            'main_group_company_id' => $payload['main_group_company_id'] ?? null,
            'name' => $payload['name'],
            'registration_number' => $payload['registration_number'] ?? null,
            'subdomain' => Str::lower($payload['subdomain']),
            'industry' => $payload['industry'],
            'address' => $payload['address'] ?? null,
            'phones' => $payload['phones'] ?? [],
            'enterprise_types' => $payload['enterprise_types'] ?? [],
            'logo_path' => $logoPath,
            'theme_color' => $payload['theme_color'] ?? null,
            'is_active' => (bool) ($payload['is_active'] ?? true),
        ]);

        return redirect()
            ->route('system.companies.index')
            ->with('success', "Company [{$company->name}] updated successfully.");
    }

    public function updateMainGroupCompany(UpdateMainGroupCompanyRequest $request, MainGroupCompany $mainGroupCompany): RedirectResponse
    {
        $payload = $request->validated('group');

        $logoPath = $mainGroupCompany->logo_path;
        if (($payload['logo'] ?? null) instanceof UploadedFile) {
            $logoPath = $this->storeLogo($payload['logo'], 'group-logos');
        }

        $mainGroupCompany->update([
            'name' => $payload['name'],
            'registration_number' => $payload['registration_number'] ?? null,
            'address' => $payload['address'] ?? null,
            'phones' => $payload['phones'] ?? [],
            'enterprise_types' => $payload['enterprise_types'] ?? [],
            'logo_path' => $logoPath,
            'is_active' => (bool) ($payload['is_active'] ?? true),
        ]);

        return redirect()
            ->route('system.companies.index')
            ->with('success', "Main group [{$mainGroupCompany->name}] updated successfully.");
    }

    private function tenantDashboardUrl(Company $company): string
    {
        return AppHost::absoluteUrl(AppHost::tenantHost($company->subdomain), '/dashboard');
    }

    private function storeLogo(UploadedFile $file, string $folder): string
    {
        return $file->store($folder, 'public');
    }

    private function presentCompany(Company $company): array
    {
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
                    'registration_number' => $company->mainGroupCompany->registration_number,
                    'address' => $company->mainGroupCompany->address,
                    'phones' => $company->mainGroupCompany->phones,
                    'enterprise_types' => $company->mainGroupCompany->enterprise_types,
                    'logo_path' => $company->mainGroupCompany->logo_path,
                    'is_active' => $company->mainGroupCompany->is_active,
                ]
                : null,
            'created_at' => optional($company->created_at)?->toDateTimeString(),
        ];
    }

    private function presentMainGroup(MainGroupCompany $group): array
    {
        $companies = $group->relationLoaded('companies')
            ? $group->companies
            : $group->companies()->with('mainGroupCompany:id,name,registration_number,address,phones,enterprise_types,logo_path,is_active')->orderBy('name')->get();

        return [
            'id' => $group->id,
            'name' => $group->name,
            'registration_number' => $group->registration_number,
            'address' => $group->address,
            'phones' => $group->phones,
            'enterprise_types' => $group->enterprise_types,
            'logo_path' => $group->logo_path,
            'is_active' => $group->is_active,
            'companies_count' => $companies->count(),
            'active_companies_count' => $companies->where('is_active', true)->count(),
            'companies' => $companies
                ->map(fn (Company $company) => $this->presentCompany($company))
                ->values()
                ->all(),
        ];
    }
}
