<?php

namespace App\Services\ServiceRecords;

use App\Models\ServiceRecord;
use App\Models\ServiceRecordRow;
use Illuminate\Support\Arr;
use Throwable;

class GenericServiceRecordExtractor implements ServiceRecordExtractor
{
    public function supports(ServiceRecord $serviceRecord, ServiceRecordRow $serviceRecordRow): bool
    {
        return true;
    }

    public function extract(ServiceRecord $serviceRecord, ServiceRecordRow $serviceRecordRow): array
    {
        $snapshot = is_array($serviceRecordRow->payload_snapshot) ? $serviceRecordRow->payload_snapshot : [];
        $details = is_array($serviceRecordRow->service_details) ? $serviceRecordRow->service_details : [];

        $serviceGroupKey = (string) ($serviceRecord->service_group_key ?: $serviceRecord->handler_key ?: $this->defaultServiceGroupKey());
        $schemaVectorId = $serviceRecordRow->schema_vector_id ?: $serviceRecordRow->service_schema_id;

        return [
            'service_group_key' => $serviceGroupKey,
            'handler_key' => $serviceGroupKey,
            'service_record_id' => (int) $serviceRecord->id,
            'operation_id' => (int) $serviceRecord->id,
            'service_record_row_id' => (int) $serviceRecordRow->id,
            'service_instance_id' => (int) $serviceRecordRow->id,
            'schema_vector_id' => $schemaVectorId ? (int) $schemaVectorId : null,
            'service_schema_id' => $schemaVectorId ? (int) $schemaVectorId : null,
            'service_code' => (string) ($serviceRecordRow->service_code ?: ''),
            'schema_version' => (int) ($serviceRecordRow->schema_version ?? 1),
            'captured_at' => (string) (Arr::get($snapshot, 'captured_at') ?: optional($serviceRecordRow->created_at)->toIso8601String()),
            'dimensions' => array_filter([
                'company_id' => $serviceRecord->company_id ? (string) $serviceRecord->company_id : null,
                'client_id' => $serviceRecord->client_id ? (string) $serviceRecord->client_id : null,
                'contract_no' => $serviceRecord->contract_no,
                'document_no' => $serviceRecord->document_no,
                'status' => $serviceRecord->status,
                'reference_no' => $serviceRecord->reference_no,
                'service_name' => $serviceRecordRow->service_name,
                'service_type' => $serviceRecordRow->service_type,
                'unit_name' => $serviceRecordRow->unit_name,
            ], fn ($value) => $value !== null && $value !== ''),
            'metrics' => [
                'qty' => (float) ($serviceRecordRow->qty ?? 0),
                'base_cost' => round((float) ($serviceRecordRow->base_cost ?? $serviceRecordRow->unit_fare ?? 0), 2),
                'supplier_cost' => round((float) ($serviceRecordRow->supplier_cost ?? $serviceRecordRow->base_cost ?? $serviceRecordRow->unit_fare ?? 0), 2),
                'discount_value' => round((float) ($serviceRecordRow->discount_value ?? 0), 2),
                'discount_amount' => round((float) ($serviceRecordRow->discount_amount ?? 0), 2),
                'tax_value' => round((float) ($serviceRecordRow->tax_value ?? 0), 2),
                'tax_amount' => round((float) ($serviceRecordRow->tax_amount ?? 0), 2),
                'sell_price' => round((float) ($serviceRecordRow->sell_price ?? $serviceRecordRow->client_price ?? 0), 2),
                'line_total' => round((float) ($serviceRecordRow->line_total ?? 0), 2),
                'service_record_total_amount' => round((float) ($serviceRecord->total_amount ?? 0), 2),
            ],
            'raw_payload' => [
                'service_details' => $details,
                'service_details_extra' => is_array($serviceRecordRow->service_details_extra) ? $serviceRecordRow->service_details_extra : [],
                'payload' => is_array($serviceRecordRow->payload) ? $serviceRecordRow->payload : [],
                'payload_snapshot' => $snapshot,
            ],
        ];
    }

    private function defaultServiceGroupKey(): string
    {
        try {
            return (string) config('handlers.default', 'travel.services');
        } catch (Throwable) {
            return 'travel.services';
        }
    }
}
