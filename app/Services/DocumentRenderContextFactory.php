<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Contract;
use App\Models\ServiceRecord;
use Illuminate\Database\Eloquent\Model;
use App\Services\Handlers\HandlerRegistry;

class DocumentRenderContextFactory
{
    public function __construct(
        private readonly HandlerRegistry $handlers,
    ) {
    }

    public function makePreview(string $documentType, array $payload): array
    {
        return $this->withMeta($documentType, $payload);
    }

    public function makeInvoiceFromServiceRecord(ServiceRecord $serviceRecord, ?Company $company = null, ?Contract $contract = null): array
    {
        $companyModel = $company ?: $this->resolveRelation($serviceRecord, 'company');
        $mainGroup = $companyModel instanceof Company
            ? $this->resolveRelation($companyModel, 'mainGroupCompany', false)
            : null;
        $client = $this->resolveRelation($serviceRecord, 'client');
        $handler = $this->handlers->resolve($serviceRecord->service_group_key, $companyModel);
        $rows = collect();

        foreach (['rows', 'serviceRecordRows', 'serviceInstances', 'services'] as $relation) {
            if ($serviceRecord->relationLoaded($relation)) {
                $rows = $serviceRecord->getRelation($relation) ?? collect();
                break;
            }
        }

        $subtotal = 0.0;
        $taxTotal = 0.0;
        $grandTotal = 0.0;

        $lineItems = [];
        $serviceRows = [];

        foreach ($rows as $row) {
            $qty = (float) ($row->qty ?? 0);
            $baseCost = (float) ($row->base_cost ?? $row->unit_fare ?? 0);
            $supplierCost = (float) ($row->supplier_cost ?? $baseCost);
            $discountAmount = (float) ($row->discount_amount ?? 0);
            $taxAmount = (float) ($row->tax_amount ?? 0);
            $sellPrice = (float) ($row->sell_price ?? $row->client_price ?? 0);
            $lineTotal = (float) ($row->line_total ?? 0);
            $netUnitBeforeTax = max($sellPrice - $discountAmount, 0);
            $details = is_array($row->service_details ?? null) ? $row->service_details : [];

            $subtotal += ($netUnitBeforeTax * $qty);
            $taxTotal += ($taxAmount * $qty);
            $grandTotal += $lineTotal;

            $lineItems[] = [
                'description' => $row->service_name ?: 'Service',
                'unit' => $row->unit_name ?: 'unit',
                'quantity' => (int) $qty,
                'unit_price' => $this->money($netUnitBeforeTax + $taxAmount),
                'total' => $this->money($lineTotal),
            ];

            $serviceRows[] = [
                'schema_vector' => [
                    'id' => $row->schema_vector_id,
                    'service_code' => $row->service_code,
                    'service_type' => $row->service_type,
                    'service_name' => $row->service_name,
                    'version' => (int) ($row->schema_version ?? 1),
                ],
                'service' => [
                    'title' => $row->service_name ?: 'Service',
                    'date' => optional($serviceRecord->created_at)->format('d M Y') ?: now()->format('d M Y'),
                    'time' => '',
                    'details' => $this->detailsToString($details),
                    'confirmation' => (string) ($row->id ?? ''),
                ],
                'fields' => $details,
                'details' => $details,
                'details_extra' => is_array($row->service_details_extra ?? null) ? $row->service_details_extra : [],
                'finance' => [
                    'qty' => (int) $qty,
                    'unit_name' => $row->unit_name ?: '',
                    'base_cost' => $this->money($baseCost),
                    'supplier_cost' => $this->money($supplierCost),
                    'discount_amount' => $this->money($discountAmount),
                    'tax_amount' => $this->money($taxAmount),
                    'sell_price' => $this->money($sellPrice),
                    'line_total' => $this->money($lineTotal),
                ],
                'pricing' => [
                    'qty' => (int) $qty,
                    'unit_fare' => $this->money($baseCost),
                    'tax_amount' => $this->money($taxAmount),
                    'line_total' => $this->money($lineTotal),
                ],
                'snapshot' => is_array($row->payload_snapshot ?? null) ? $row->payload_snapshot : [],
            ];
        }

        $serviceRecordPayload = [
            'id' => $serviceRecord->id,
            'service_group_key' => $handler->key(),
            'handler_key' => $handler->key(),
            'reference' => $serviceRecord->reference_no,
            'reference_no' => $serviceRecord->reference_no,
            'document_no' => $serviceRecord->document_no,
            'status' => $serviceRecord->status,
            'remarks' => (string) ($serviceRecord->remarks ?? ''),
            'captured_at' => optional($serviceRecord->created_at)->toIso8601String(),
            'start_date' => optional($serviceRecord->created_at)->format('d M Y') ?: now()->format('d M Y'),
            'end_date' => optional($serviceRecord->created_at)->addDays(1)->format('d M Y') ?: now()->addDay()->format('d M Y'),
            'rows' => $serviceRows,
        ];

        $payload = [
            'handler' => $handler->toArray(),
            'company' => [
                'name' => $companyModel?->name ?: 'Company',
                'logo_url' => $this->resolveLogoPath($companyModel?->logo_path),
                'email' => '',
                'phone' => $this->firstPhone($companyModel?->phones),
                'address' => $this->formatAddress($companyModel?->address),
            ],
            'main_group' => [
                'name' => $mainGroup?->name ?: '',
                'logo_url' => $this->resolveLogoPath($mainGroup?->logo_path),
                'address' => $this->formatAddress($mainGroup?->address),
            ],
            'client' => [
                'name' => $client?->name ?: 'Client',
                'email' => $client?->hq_contact_email ?: '',
                'address' => $this->formatAddress($contract?->billing_address),
                'remarks' => (string) ($serviceRecord->remarks ?? ''),
            ],
            'invoice' => [
                'number' => $serviceRecord->document_no ?: ('DOC-' . str_pad((string) $serviceRecord->id, 6, '0', STR_PAD_LEFT)),
                'issue_date' => optional($serviceRecord->updated_at ?: $serviceRecord->created_at)->format('d M Y') ?: now()->format('d M Y'),
                'due_date' => optional($serviceRecord->updated_at ?: $serviceRecord->created_at)->addDays(14)->format('d M Y') ?: now()->addDays(14)->format('d M Y'),
                'subtotal' => $this->money($subtotal),
                'tax_total' => $this->money($taxTotal),
                'grand_total' => $this->money($grandTotal > 0 ? $grandTotal : ($serviceRecord->total_amount ?? 0)),
                'line_items' => $lineItems,
            ],
            'quote' => [
                'number' => '',
                'valid_until' => '',
                'grand_total' => '',
                'line_items' => [],
            ],
            'receipt' => [
                'number' => '',
                'payment_date' => '',
                'amount_paid' => '',
                'payment_method' => '',
                'reference_id' => '',
            ],
            'finance' => [
                'subtotal' => round($subtotal, 2),
                'tax_total' => round($taxTotal, 2),
                'grand_total' => round($grandTotal > 0 ? $grandTotal : (float) ($serviceRecord->total_amount ?? 0), 2),
                'formatted_subtotal' => $this->money($subtotal),
                'formatted_tax_total' => $this->money($taxTotal),
                'formatted_grand_total' => $this->money($grandTotal > 0 ? $grandTotal : ($serviceRecord->total_amount ?? 0)),
            ],
            'service_record' => $serviceRecordPayload,
            'service_rows' => $serviceRows,
            'schema_vectors' => array_map(fn (array $row) => $row['schema_vector'], $serviceRows),
            'operation' => $serviceRecordPayload,
            'services' => $serviceRows,
            'service_instances' => $serviceRows,
            'remarks' => (string) ($serviceRecord->remarks ?? ''),
        ];

        return $this->withMeta('invoice', $payload);
    }

    public function makeInvoiceFromOperation(ServiceRecord $serviceRecord, ?Company $company = null, ?Contract $contract = null): array
    {
        return $this->makeInvoiceFromServiceRecord($serviceRecord, $company, $contract);
    }

    private function withMeta(string $documentType, array $payload): array
    {
        $payload['meta'] = array_merge($payload['meta'] ?? [], [
            'active_document_type' => $documentType,
        ]);

        return $payload;
    }

    private function money(float|int|string|null $value): string
    {
        return 'RM ' . number_format((float) ($value ?? 0), 2);
    }

    private function formatAddress(mixed $address): string
    {
        if (is_array($address)) {
            $parts = array_filter(array_map(fn ($value) => is_scalar($value) ? trim((string) $value) : '', $address));

            return implode(', ', $parts);
        }

        return is_scalar($address) ? trim((string) $address) : '';
    }

    private function firstPhone(mixed $phones): string
    {
        if (is_array($phones)) {
            foreach ($phones as $phone) {
                if (is_scalar($phone) && trim((string) $phone) !== '') {
                    return trim((string) $phone);
                }
            }
        }

        return is_scalar($phones) ? trim((string) $phones) : '';
    }

    private function resolveLogoPath(?string $path): string
    {
        if (! $path) {
            return '';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return str_starts_with($path, '/storage/')
            ? $path
            : '/storage/' . ltrim(str_replace('storage/', '', $path), '/');
    }

    private function detailsToString(array $details): string
    {
        $parts = [];

        foreach ($details as $key => $value) {
            if (is_array($value)) {
                $value = implode(', ', array_map(fn ($item) => is_scalar($item) ? (string) $item : '', $value));
            }

            if (! is_scalar($value)) {
                continue;
            }

            $label = ucwords(str_replace('_', ' ', (string) $key));
            $parts[] = $label . ': ' . trim((string) $value);
        }

        return implode(' | ', array_filter($parts));
    }

    private function resolveRelation(Model $model, string $relation, bool $allowLazyLoading = true): mixed
    {
        if ($model->relationLoaded($relation)) {
            return $model->getRelation($relation);
        }

        if (! $allowLazyLoading) {
            return null;
        }

        return $model->{$relation};
    }
}
