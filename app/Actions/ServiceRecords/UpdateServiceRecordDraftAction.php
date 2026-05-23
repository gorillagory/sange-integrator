<?php

namespace App\Actions\ServiceRecords;

use App\Models\Company;
use App\Models\SchemaVector;
use App\Models\ServiceRecord;
use App\Models\ServiceRecordRow;
use App\Services\Handlers\HandlerRegistry;
use Illuminate\Support\Facades\DB;

class UpdateServiceRecordDraftAction
{
    public function __construct(
        private readonly HandlerRegistry $handlers,
    ) {
    }

    public function execute(ServiceRecord $serviceRecord, array $validated, Company $company): ServiceRecord
    {
        $serviceGroup = $this->handlers->resolve($validated['service_group_key'] ?? null, $company);
        $vectors = SchemaVector::query()
            ->where('industry', $serviceGroup->industry() ?: $company->industry)
            ->where(function ($query) use ($serviceGroup) {
                $query->where('service_group_key', $serviceGroup->key())
                    ->orWhereNull('service_group_key')
                    ->orWhere('service_group_key', '');
            })
            ->get();
        $vectorsById = $vectors->keyBy('id');
        $vectorsByCode = $vectors->keyBy(fn (SchemaVector $vector) => $vector->service_code ?: $vector->service_type);

        return DB::connection('tenant')->transaction(function () use ($serviceRecord, $validated, $company, $vectorsById, $vectorsByCode, $serviceGroup) {
            $totalAmount = 0;

            $serviceRecord->rows()->delete();

            $serviceRecord->update([
                'service_group_key' => $serviceGroup->key(),
                'client_id' => $validated['client_id'],
                'contract_no' => $validated['contract_no'],
                'client_remark_preset_id' => $validated['client_remark_preset_id'] ?? null,
                'remarks' => $validated['remarks'] ?? null,
                'assigned_user_id' => $validated['assigned_user_id'] ?? $serviceRecord->assigned_user_id,
                'status' => 'Draft',
                'service_status' => $serviceRecord->service_status ?: 'Pending',
            ]);

            foreach ($validated['rows'] as $index => $item) {
                $qty = (int) ($item['qty'] ?? 1);
                $baseCost = (float) ($item['base_cost'] ?? 0);
                $supplierCost = is_numeric($item['supplier_cost'] ?? null) ? (float) $item['supplier_cost'] : null;
                $discountType = $item['discount_type'] ?? 'RM';
                $discountValue = (float) ($item['discount_value'] ?? 0);
                $taxType = $item['tax_type'] ?? 'RM';
                $taxValue = (float) ($item['tax_value'] ?? 0);
                $sellPrice = (float) ($item['sell_price'] ?? 0);
                $serviceCode = (string) ($item['service_code'] ?? '');
                $schemaVectorId = is_numeric($item['schema_vector_id'] ?? null) ? (int) $item['schema_vector_id'] : null;

                $discountAmount = $discountType === '%'
                    ? $sellPrice * ($discountValue / 100)
                    : $discountValue;
                $discountedUnitPrice = max($sellPrice - $discountAmount, 0);
                $taxAmount = $taxType === '%'
                    ? $discountedUnitPrice * ($taxValue / 100)
                    : $taxValue;
                $lineTotal = ($discountedUnitPrice + $taxAmount) * $qty;
                $totalAmount += $lineTotal;

                $vector = ($schemaVectorId ? ($vectorsById[$schemaVectorId] ?? null) : null)
                    ?: ($vectorsByCode[$serviceCode] ?? null);
                $vectorCode = (string) ($vector?->service_code ?: $vector?->service_type ?: $serviceCode);
                $vectorType = (string) ($vector?->service_type ?: $vectorCode);
                $vectorName = (string) ($vector?->service_name ?: $vector?->display_name ?: 'Service');
                $vectorVersion = (int) ($vector?->version ?? 1);
                $details = $item['service_details'] ?? [];
                $detailsExtra = $item['service_details_extra'] ?? [];
                $unitName = (string) ($item['unit_name'] ?: $this->resolveUnitName($vector));
                $payloadSnapshot = [
                    'captured_at' => now()->toIso8601String(),
                    'service_group_key' => $serviceGroup->key(),
                    'service_group' => $serviceGroup->toArray(),
                    'schema_vector' => [
                        'schema_vector_id' => $vector?->id,
                        'service_code' => $vectorCode,
                        'service_type' => $vectorType,
                        'service_name' => $vectorName,
                        'version' => $vectorVersion,
                        'unit_name' => $unitName,
                    ],
                    'service' => [
                        'details' => $details,
                        'details_extra' => $detailsExtra,
                    ],
                    'finance' => [
                        'qty' => $qty,
                        'unit_name' => $unitName,
                        'base_cost' => $baseCost,
                        'supplier_cost' => $supplierCost,
                        'discount_type' => $discountType,
                        'discount_value' => $discountValue,
                        'discount_amount' => $discountAmount,
                        'tax_type' => $taxType,
                        'tax_value' => $taxValue,
                        'tax_amount' => $taxAmount,
                        'sell_price' => $sellPrice,
                        'line_total' => $lineTotal,
                    ],
                ];

                ServiceRecordRow::query()->create([
                    'service_record_id' => $serviceRecord->id,
                    'company_id' => $company->id,
                    'schema_vector_id' => $vector?->id,
                    'service_type' => $vectorType,
                    'service_code' => $vectorCode,
                    'schema_version' => $vectorVersion,
                    'service_name' => $vectorName,
                    'service_details' => $details,
                    'service_details_extra' => $detailsExtra,
                    'qty' => $qty,
                    'unit_name' => $unitName,
                    'base_cost' => $baseCost,
                    'supplier_cost' => $supplierCost,
                    'unit_fare' => $baseCost,
                    'discount_type' => $discountType,
                    'discount_value' => $discountValue,
                    'discount_amount' => $discountAmount,
                    'tax_type' => $taxType,
                    'tax_value' => $taxValue,
                    'tax_amount' => $taxAmount,
                    'sell_price' => $sellPrice,
                    'client_price' => $sellPrice,
                    'line_total' => $lineTotal,
                    'sort_order' => $index,
                    'payload' => $details,
                    'payload_snapshot' => $payloadSnapshot,
                ]);
            }

            $serviceRecord->update([
                'total_amount' => $totalAmount,
            ]);

            return $serviceRecord->fresh(['client', 'clientRemarkPreset', 'rows.schemaVector']);
        });
    }

    private function resolveUnitName(?SchemaVector $vector): string
    {
        $payload = is_array($vector?->schema_payload ?? null) ? $vector->schema_payload : [];
        $pricingUnits = array_values(array_filter(array_map(
            fn ($unit) => is_string($unit) ? trim($unit) : '',
            is_array($payload['pricing_units'] ?? null) ? $payload['pricing_units'] : []
        )));
        $unit = $payload['commercial']['unit'] ?? $payload['unit'] ?? ($pricingUnits[0] ?? null);

        return is_string($unit) ? trim($unit) : '';
    }
}
