<?php

namespace App\Services;

use App\Models\Company;
use App\Models\User;
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
        return $this->absoluteUrl(
            host: 'sys.'.$this->baseDomain(),
            path: '/dashboard'
        );
    }

    public function tenantDashboardUrlForCompany(Company $company): string
    {
        return $this->absoluteUrl(
            host: $company->subdomain.'.'.$this->baseDomain(),
            path: '/dashboard'
        );
    }

    private function isSystemHost(string $host): bool
    {
        return $host === 'sys.'.$this->baseDomain();
    }

    private function isRootHost(string $host): bool
    {
        return in_array($host, [
            $this->baseDomain(),
            'www.'.$this->baseDomain(),
        ], true);
    }

    private function extractSubdomain(string $host): ?string
    {
        $baseDomain = $this->baseDomain();

        if (! str_ends_with($host, '.'.$baseDomain)) {
            return null;
        }

        $subdomain = substr($host, 0, -1 * (strlen($baseDomain) + 1));

        if ($subdomain === '' || in_array($subdomain, ['www', 'sys'], true)) {
            return null;
        }

        return $subdomain;
    }

    private function absoluteUrl(string $host, string $path): string
    {
        $appUrl = rtrim((string) config('app.url', 'http://bayam.test:8000'), '/');
        $parts = parse_url($appUrl);

        $scheme = $parts['scheme'] ?? 'http';
        $port = isset($parts['port']) ? ':'.$parts['port'] : '';

        return "{$scheme}://{$host}{$port}{$path}";
    }

    private function baseDomain(): string
    {
        $appUrl = rtrim((string) config('app.url', 'http://bayam.test:8000'), '/');
        $parts = parse_url($appUrl);

        $host = $parts['host'] ?? 'bayam.test';

        if (str_starts_with($host, 'sys.')) {
            return substr($host, 4);
        }

        if (str_starts_with($host, 'www.')) {
            return substr($host, 4);
        }

        return $host;
    }
}
