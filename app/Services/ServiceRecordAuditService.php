<?php

namespace App\Services;

use App\Models\Company;
use App\Models\ServiceRecord;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ServiceRecordAuditService
{
    public function logCreated(ServiceRecord $serviceRecord): void
    {
        AuditEngine::log(
            'RECORD',
            'SERVICE_RECORD.CREATED',
            $this->snapshot($serviceRecord),
            [],
            $serviceRecord
        );
    }

    public function logUpdated(ServiceRecord $before, ServiceRecord $after): void
    {
        AuditEngine::log(
            'RECORD',
            'SERVICE_RECORD.UPDATED',
            $this->snapshot($after),
            $this->snapshot($before),
            $after
        );
    }

    public function logStatusChanged(ServiceRecord $before, ServiceRecord $after, string $transition): void
    {
        AuditEngine::log(
            'RECORD',
            'SERVICE_RECORD.STATUS_CHANGED',
            array_merge($this->snapshot($after), [
                'status_transition' => $transition,
            ]),
            array_merge($this->snapshot($before), [
                'status_transition' => $transition,
            ]),
            $after
        );
    }

    public function timelineFor(ServiceRecord $serviceRecord, Company $company): array
    {
        return DB::connection('control')
            ->table('audit_logs as logs')
            ->leftJoin('users', 'users.id', '=', 'logs.user_id')
            ->where('logs.tenant_id', $company->id)
            ->where('logs.resource_type', ServiceRecord::class)
            ->where('logs.resource_id', (string) $serviceRecord->id)
            ->orderByDesc('logs.created_at')
            ->get([
                'logs.id',
                'logs.action',
                'logs.new_values',
                'logs.old_values',
                'logs.created_at',
                'users.name as user_name',
                'users.email as user_email',
            ])
            ->map(function ($row) {
                $newValues = $this->decodePayload($row->new_values);
                $oldValues = $this->decodePayload($row->old_values);
                $meta = $newValues['__meta'] ?? $oldValues['__meta'] ?? [];

                unset($newValues['__meta'], $oldValues['__meta']);

                return [
                    'id' => $row->id,
                    'action' => (string) $row->action,
                    'label' => $this->labelForAction((string) $row->action, $newValues),
                    'actor' => [
                        'name' => $row->user_name ?: 'System',
                        'email' => $row->user_email,
                    ],
                    'old_values' => $oldValues,
                    'new_values' => $newValues,
                    'meta' => is_array($meta) ? $meta : [],
                    'created_at' => optional(Carbon::parse((string) $row->created_at))->toDateTimeString(),
                ];
            })
            ->values()
            ->all();
    }

    public function snapshot(ServiceRecord $serviceRecord): array
    {
        $serviceRecord->loadMissing([
            'client:id,name',
            'clientRemarkPreset:id,title',
            'rows' => fn ($query) => $query->orderBy('sort_order'),
            'rows.schemaVector:id,service_code,service_name,display_name,version',
        ]);

        return [
            'reference_no' => $serviceRecord->reference_no,
            'document_no' => $serviceRecord->document_no,
            'status' => $serviceRecord->status,
            'service_group_key' => $serviceRecord->service_group_key,
            'client' => $serviceRecord->client ? [
                'id' => $serviceRecord->client->id,
                'name' => $serviceRecord->client->name,
            ] : null,
            'contract_no' => $serviceRecord->contract_no,
            'remark_preset' => $serviceRecord->clientRemarkPreset ? [
                'id' => $serviceRecord->clientRemarkPreset->id,
                'title' => $serviceRecord->clientRemarkPreset->title,
            ] : null,
            'remarks' => $serviceRecord->remarks,
            'total_amount' => (float) ($serviceRecord->total_amount ?? 0),
            'rows_count' => $serviceRecord->rows->count(),
            'rows' => $serviceRecord->rows->map(function ($row) {
                return [
                    'id' => $row->id,
                    'service_name' => $row->service_name,
                    'service_code' => $row->service_code,
                    'schema_version' => $row->schema_version,
                    'qty' => (int) ($row->qty ?? 0),
                    'unit_name' => $row->unit_name,
                    'line_total' => (float) ($row->line_total ?? 0),
                    'base_cost' => (float) ($row->base_cost ?? 0),
                    'sell_price' => (float) ($row->sell_price ?? 0),
                    'discount_type' => $row->discount_type,
                    'discount_value' => (float) ($row->discount_value ?? 0),
                    'tax_type' => $row->tax_type,
                    'tax_value' => (float) ($row->tax_value ?? 0),
                    'service_details' => $row->service_details ?? [],
                    'service_details_extra' => $row->service_details_extra ?? [],
                ];
            })->values()->all(),
        ];
    }

    private function decodePayload(mixed $payload): array
    {
        if (is_array($payload)) {
            return $payload;
        }

        if (! is_string($payload) || trim($payload) === '') {
            return [];
        }

        $decoded = json_decode($payload, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function labelForAction(string $action, array $newValues): string
    {
        return match ($action) {
            'SERVICE_RECORD.CREATED' => 'Service record created',
            'SERVICE_RECORD.UPDATED' => 'Draft content updated',
            'SERVICE_RECORD.STATUS_CHANGED' => match ($newValues['status'] ?? null) {
                'DocumentLocked' => 'Document routing locked',
                'Draft' => 'Returned to draft for editing',
                default => 'Status changed',
            },
            default => str_replace('_', ' ', $action),
        };
    }
}
