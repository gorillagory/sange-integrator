<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class AuditEngine
{
    /**
     * Log a tamper-proof event into the system.
     */
    public static function log(string $category, string $action, array $newValues = [], array $oldValues = [], $resource = null)
    {
        $id = Str::uuid()->toString();
        $tenantId = config('database.connections.tenant.database') ? self::extractTenantId() : null;
        $userId = Auth::id();
        $ip = Request::ip();

        $resourceType = $resource ? get_class($resource) : null;
        $resourceId = $resource ? $resource->id : null;

        $newValuesJson = json_encode($newValues);
        $oldValuesJson = json_encode($oldValues);

        // 🔒 THE CRYPTOGRAPHIC SEAL
        // We hash the exact data combination. If ANY byte changes in the DB,
        // the hash won't match when we verify it later.
        $payloadToSign = $id . $tenantId . $userId . $category . $action . $newValuesJson . $oldValuesJson;
        $signature = hash_hmac('sha256', $payloadToSign, config('app.key'));

        DB::connection('control')->table('audit_logs')->insert([
            'id' => $id,
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'ip_address' => $ip,
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
    }

    /**
     * Helper to grab the tenant ID based on the current subdomain
     */
    private static function extractTenantId()
    {
        $company = view()->shared('currentCompany');
        return $company ? $company->id : null;
    }
}
