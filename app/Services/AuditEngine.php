<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Throwable;

class AuditEngine
{
    /**
     * Log an immutable event into the audit store.
     */
    public static function log(
        string $category,
        string $action,
        array $newValues = [],
        array $oldValues = [],
        mixed $resource = null,
        array $context = []
    ): void {
        if (! config('audit.enabled', true)) {
            return;
        }

        $id = Str::uuid()->toString();
        $tenantId = self::extractTenantId();
        $userId = Auth::id();

        $resourceType = $resource ? get_class($resource) : null;
        $resourceId = $resource && isset($resource->id) ? (string) $resource->id : null;

        $meta = array_filter(array_merge(self::requestContext(), $context), static fn ($value) => ! in_array($value, [null, '', []], true));
        $newValuesJson = self::encodeJson(self::attachMeta($newValues, $meta));
        $oldValuesJson = self::encodeJson($oldValues);

        $payloadToSign = implode('|', [
            $id,
            (string) $tenantId,
            (string) $userId,
            $category,
            $action,
            (string) $resourceType,
            (string) $resourceId,
            $newValuesJson,
            $oldValuesJson,
        ]);

        $signature = hash_hmac('sha256', $payloadToSign, (string) config('app.key', ''));

        try {
            DB::connection('control')->table('audit_logs')->insert([
                'id' => $id,
                'tenant_id' => $tenantId,
                'user_id' => $userId,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'category' => $category,
                'action' => $action,
                'resource_type' => $resourceType,
                'resource_id' => $resourceId,
                'old_values' => $oldValuesJson,
                'new_values' => $newValuesJson,
                'signature' => $signature,
                'created_at' => now(),
            ]);
        } catch (Throwable $exception) {
            Log::channel(config('audit.fallback_log_channel', 'stack'))->warning('AuditEngine write failed.', [
                'category' => $category,
                'action' => $action,
                'resource_type' => $resourceType,
                'resource_id' => $resourceId,
                'tenant_id' => $tenantId,
                'user_id' => $userId,
                'exception' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Resolve tenant id in web and CLI contexts.
     */
    private static function extractTenantId(): ?int
    {
        $company = app()->bound('currentCompany')
            ? app('currentCompany')
            : view()->shared('currentCompany');

        if ($company && isset($company->id)) {
            return (int) $company->id;
        }

        $tenantDatabase = config('database.connections.tenant.database');

        if (! is_string($tenantDatabase) || $tenantDatabase === '') {
            return null;
        }

        $company = Company::query()
            ->select(['id'])
            ->where('db_name', $tenantDatabase)
            ->first();

        return $company ? (int) $company->id : null;
    }

    private static function requestContext(): array
    {
        $route = Request::route();

        return [
            'request_method' => Request::method(),
            'request_host' => Request::getHost(),
            'request_path' => Request::path(),
            'request_url' => Request::fullUrl(),
            'route_name' => $route?->getName(),
            'request_origin' => Request::header('origin'),
        ];
    }

    private static function attachMeta(array $payload, array $meta): array
    {
        if ($meta === []) {
            return $payload;
        }

        if (isset($payload['__meta']) && is_array($payload['__meta'])) {
            $payload['__meta'] = array_merge($payload['__meta'], $meta);

            return $payload;
        }

        $payload['__meta'] = $meta;

        return $payload;
    }

    private static function encodeJson(array $payload): string
    {
        return json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
    }
}
