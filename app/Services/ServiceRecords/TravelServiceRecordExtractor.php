<?php

namespace App\Services\ServiceRecords;

use App\Models\ServiceRecord;
use App\Models\ServiceRecordRow;

class TravelServiceRecordExtractor extends GenericServiceRecordExtractor
{
    public function supports(ServiceRecord $serviceRecord, ServiceRecordRow $serviceRecordRow): bool
    {
        return (string) $serviceRecord->service_group_key === 'travel.services';
    }

    public function extract(ServiceRecord $serviceRecord, ServiceRecordRow $serviceRecordRow): array
    {
        $row = parent::extract($serviceRecord, $serviceRecordRow);
        $details = is_array($serviceRecordRow->service_details) ? $serviceRecordRow->service_details : [];

        $row['dimensions'] = array_merge($row['dimensions'], array_filter([
            'document_no' => $serviceRecord->document_no,
            'service_code' => $serviceRecordRow->service_code,
            'industry' => 'travel',
        ], fn ($value) => $value !== null && $value !== ''));

        $supplierBase = (float) ($serviceRecordRow->supplier_cost ?? $serviceRecordRow->base_cost ?? $serviceRecordRow->unit_fare ?? 0);
        $qty = (float) ($serviceRecordRow->qty ?? 0);
        $taxAmount = (float) ($serviceRecordRow->tax_amount ?? 0);
        $lineTotal = (float) ($serviceRecordRow->line_total ?? 0);
        $discountAmount = (float) ($serviceRecordRow->discount_amount ?? 0);

        $row['metrics'] = array_merge($row['metrics'], [
            'supplier_total' => round($supplierBase * $qty, 2),
            'discount_total' => round($discountAmount * $qty, 2),
            'markup_total' => round($lineTotal - (($supplierBase * $qty) + $taxAmount), 2),
            'detail_field_count' => count($details),
        ]);

        return $row;
    }
}
