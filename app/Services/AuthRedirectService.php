<?php

namespace App\Services;

use App\Models\Company;
use App\Models\User;
use App\Support\AppHost;
use Illuminate\Http\Request;

class AuthRedirectService
{
    public function redirectAfterLogin(Request $request, User $user): string
    {
        $host = $request->getHost();

        if ($this->isSystemHost($host)) {
            if ($user->isSystemUser()) {
                return $this->systemDashboardUrl();
            }

            $company = $user->firstAccessibleCompany();

            return $company
                ? $this->tenantDashboardUrlForCompany($company)
                : $this->systemDashboardUrl();
        }

        if ($this->isRootHost($host)) {
            if ($user->isSystemUser()) {
                return $this->systemDashboardUrl();
            }

            $company = $user->firstAccessibleCompany();

            return $company
                ? $this->tenantDashboardUrlForCompany($company)
                : $this->systemDashboardUrl();
        }

        $subdomain = $this->extractSubdomain($host);

        if (! $subdomain) {
            return $user->isSystemUser()
                ? $this->systemDashboardUrl()
                : ($user->firstAccessibleCompany()
                    ? $this->tenantDashboardUrlForCompany($user->firstAccessibleCompany())
                    : $this->systemDashboardUrl());
        }

        $company = Company::query()
            ->where('subdomain', $subdomain)
            ->where('is_active', true)
            ->first();

        if (! $company) {
            return $user->isSystemUser()
                ? $this->systemDashboardUrl()
                : ($user->firstAccessibleCompany()
                    ? $this->tenantDashboardUrlForCompany($user->firstAccessibleCompany())
                    : $this->systemDashboardUrl());
        }

        if ($user->isSuperAdmin()) {
            return $this->tenantDashboardUrlForCompany($company);
        }

        if ($user->belongsToCompany($company->id)) {
            return $this->tenantDashboardUrlForCompany($company);
        }

        if ($user->isSystemUser()) {
            return $this->systemDashboardUrl();
        }

        $fallbackCompany = $user->firstAccessibleCompany();

        return $fallbackCompany
            ? $this->tenantDashboardUrlForCompany($fallbackCompany)
            : $this->systemDashboardUrl();
    }

    public function redirectForDeniedTenantAccess(User $user, Company $attemptedCompany): string
    {
        if ($user->isSystemUser()) {
            return $this->systemDashboardUrl();
        }

        $fallbackCompany = $user->firstAccessibleCompany();

        if ($fallbackCompany && $fallbackCompany->id !== $attemptedCompany->id) {
            return $this->tenantDashboardUrlForCompany($fallbackCompany);
        }

        return $this->systemDashboardUrl();
    }

    public function systemDashboardUrl(): string
    {
        return AppHost::absoluteUrlForRequest(request(), AppHost::systemHost(), '/dashboard');
    }

    public function tenantDashboardUrlForCompany(Company $company): string
    {
        return AppHost::absoluteUrlForRequest(request(), AppHost::tenantHost($company->subdomain), '/dashboard');
    }

    private function isSystemHost(string $host): bool
    {
        return AppHost::isSystemHost($host);
    }

    private function isRootHost(string $host): bool
    {
        return AppHost::isRootHost($host);
    }

    private function extractSubdomain(string $host): ?string
    {
        return AppHost::extractSubdomain($host);
    }
}
