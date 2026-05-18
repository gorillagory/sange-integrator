<?php

namespace App\Support;

class AppHost
{
    public static function appUrl(): string
    {
        return rtrim((string) config('app.url', 'http://localhost'), '/');
    }

    public static function appUrlParts(): array
    {
        return parse_url(self::appUrl()) ?: [];
    }

    public static function scheme(): string
    {
        return self::appUrlParts()['scheme'] ?? 'http';
    }

    public static function port(): ?int
    {
        return isset(self::appUrlParts()['port'])
            ? (int) self::appUrlParts()['port']
            : null;
    }

    public static function portSegment(): string
    {
        $port = self::port();

        return $port ? ':'.$port : '';
    }

    public static function baseDomain(): string
    {
        $configured = trim((string) config('app.base_domain', ''));

        if ($configured !== '') {
            return self::normalizeHost($configured);
        }

        $host = self::appUrlParts()['host'] ?? 'localhost';

        return self::normalizeHost($host);
    }

    public static function systemSubdomain(): string
    {
        return trim((string) config('app.system_subdomain', 'sys')) ?: 'sys';
    }

    public static function systemHost(): string
    {
        return self::systemSubdomain().'.'.self::baseDomain();
    }

    public static function tenantHost(?string $subdomain): string
    {
        $label = trim((string) $subdomain);

        if ($label === '') {
            return self::baseDomain();
        }

        return strtolower($label).'.'.self::baseDomain();
    }

    public static function absoluteUrl(string $host, string $path = '/'): string
    {
        $normalizedPath = '/'.ltrim($path, '/');

        return self::scheme().'://'.self::normalizeHost($host).self::portSegment().$normalizedPath;
    }

    public static function normalizeHost(string $host): string
    {
        $value = strtolower(trim($host));
        $value = preg_replace('/:\d+$/', '', $value) ?? $value;

        return rtrim($value, '.');
    }

    public static function isSystemHost(string $host): bool
    {
        return self::normalizeHost($host) === self::systemHost();
    }

    public static function isRootHost(string $host): bool
    {
        return in_array(self::normalizeHost($host), [
            self::baseDomain(),
            'www.'.self::baseDomain(),
        ], true);
    }

    public static function extractSubdomain(string $host): ?string
    {
        $normalizedHost = self::normalizeHost($host);
        $baseDomain = self::baseDomain();

        if (! str_ends_with($normalizedHost, '.'.$baseDomain)) {
            return null;
        }

        $subdomain = substr($normalizedHost, 0, -1 * (strlen($baseDomain) + 1));

        if ($subdomain === '' || in_array($subdomain, ['www', self::systemSubdomain()], true)) {
            return null;
        }

        return $subdomain;
    }

    public static function defaultSessionDomain(): string
    {
        return '.'.self::baseDomain();
    }
}
