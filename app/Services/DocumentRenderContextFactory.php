<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Contract;
use App\Models\SchemaVector;
use App\Models\ServiceRecord;
use App\Models\ServiceRecordRow;
use Illuminate\Database\Eloquent\Model;
use App\Services\Handlers\HandlerRegistry;
use DOMDocument;
use DOMNode;

class DocumentRenderContextFactory
{
    public function __construct(
        private readonly HandlerRegistry $handlers,
        private readonly DocumentQrCodeService $qrCodes,
    ) {
    }

    public function makePreview(string $documentType, array $payload): array
    {
        return $this->withMeta($documentType, $payload);
    }

    public function makeDocumentFromServiceRecord(
        string $documentType,
        ServiceRecord $serviceRecord,
        ?Company $company = null,
        ?Contract $contract = null,
        ?string $documentNumber = null,
    ): array {
        $payload = $this->makeInvoiceFromServiceRecord($serviceRecord, $company, $contract);
        $resolvedNumber = $documentNumber ?: (string) ($serviceRecord->document_no ?: $serviceRecord->reference_no ?: ('DOC-'.$serviceRecord->id));

        $payload['service_record']['document_no'] = $resolvedNumber;
        $payload['operation']['document_no'] = $resolvedNumber;
        $payload['document_links']['reference_value'] = $resolvedNumber;
        $payload['document_links']['reference_qr_data_uri'] = $this->qrCodes->generateDataUri($resolvedNumber);
        $payload['generated_document'] = [
            'number' => $resolvedNumber,
            'document_type' => $documentType,
        ];

        return match ($documentType) {
            'invoice' => $this->withMeta('invoice', array_replace_recursive($payload, [
                'invoice' => [
                    'number' => $resolvedNumber,
                ],
            ])),
            'receipt' => $this->withMeta('receipt', array_replace_recursive($payload, [
                'receipt' => [
                    'number' => $resolvedNumber,
                    'payment_date' => $payload['invoice']['issue_date'] ?? now()->format('d M Y'),
                    'amount_paid' => $payload['invoice']['grand_total'] ?? ($payload['finance']['formatted_grand_total'] ?? ''),
                    'payment_method' => 'Pending capture',
                    'reference_id' => $payload['service_record']['reference_no'] ?? '',
                ],
            ])),
            'quote' => $this->withMeta('quote', array_replace_recursive($payload, [
                'quote' => [
                    'number' => $resolvedNumber,
                    'valid_until' => now()->addDays(14)->format('d M Y'),
                    'subtotal' => $payload['invoice']['subtotal'] ?? '',
                    'tax_total' => $payload['invoice']['tax_total'] ?? '',
                    'grand_total' => $payload['invoice']['grand_total'] ?? '',
                    'line_items' => $payload['invoice']['line_items'] ?? [],
                ],
            ])),
            'itinerary' => $this->withMeta('itinerary', $payload),
            'letter' => $this->withMeta('letter', array_replace_recursive($payload, [
                'letter' => [
                    'reference_no' => $resolvedNumber,
                    'date' => now()->format('d M Y'),
                ],
            ])),
            'memo' => $this->withMeta('memo', array_replace_recursive($payload, [
                'memo' => [
                    'reference_no' => $resolvedNumber,
                    'date' => now()->format('d M Y'),
                ],
            ])),
            'reply' => $this->withMeta('reply', array_replace_recursive($payload, [
                'reply' => [
                    'reference_no' => $resolvedNumber,
                    'date' => now()->format('d M Y'),
                ],
            ])),
            default => $this->withMeta($documentType, $payload),
        };
    }

    public function makeInvoiceFromServiceRecord(ServiceRecord $serviceRecord, ?Company $company = null, ?Contract $contract = null): array
    {
        $companyModel = $company ?: $this->resolveRelation($serviceRecord, 'company');
        $mainGroup = $companyModel instanceof Company
            ? $this->resolveRelation($companyModel, 'mainGroupCompany', false)
            : null;
        $client = $this->resolveRelation($serviceRecord, 'client');
        $author = $this->resolveRelation($serviceRecord, 'author');
        $assignedUser = $this->resolveRelation($serviceRecord, 'assignedUser');
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
                'description' => $this->buildLineItemDescription($row),
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
                    'details' => $this->formattedDocumentOutputText($row, $details) ?: $this->detailsToString($details),
                    'document_output_html' => $this->buildLineItemDescription($row),
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
            'author_name' => (string) ($author?->name ?? ''),
            'author_email' => (string) ($author?->email ?? ''),
            'assigned_user_name' => (string) ($assignedUser?->name ?? ''),
            'assigned_user_email' => (string) ($assignedUser?->email ?? ''),
            'captured_at' => optional($serviceRecord->created_at)->toIso8601String(),
            'start_date' => optional($serviceRecord->created_at)->format('d M Y') ?: now()->format('d M Y'),
            'end_date' => optional($serviceRecord->created_at)->addDays(1)->format('d M Y') ?: now()->addDay()->format('d M Y'),
            'rows' => $serviceRows,
        ];
        $documentReference = (string) ($serviceRecord->document_no ?: $serviceRecord->reference_no ?: ('DOC-'.$serviceRecord->id));

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
                'address' => $this->formatAddress($client?->address) ?: $this->formatAddress($contract?->billing_address),
                'profile' => (string) ($client?->profile ?? ''),
                'remarks' => (string) ($serviceRecord->remarks ?? ''),
            ],
            'author' => [
                'id' => $author?->id,
                'name' => (string) ($author?->name ?? ''),
                'email' => (string) ($author?->email ?? ''),
            ],
            'user' => [
                'id' => $author?->id,
                'name' => (string) ($author?->name ?? ''),
                'email' => (string) ($author?->email ?? ''),
            ],
            'assigned_user' => [
                'id' => $assignedUser?->id,
                'name' => (string) ($assignedUser?->name ?? ''),
                'email' => (string) ($assignedUser?->email ?? ''),
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
            'document_links' => [
                'reference_value' => $documentReference,
                'reference_label' => 'Document Reference',
                'reference_qr_data_uri' => $this->qrCodes->generateDataUri($documentReference),
            ],
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

    private function buildLineItemDescription(ServiceRecordRow $row): string
    {
        $title = trim((string) ($row->service_name ?: 'Service'));
        $details = is_array($row->service_details ?? null) ? $row->service_details : [];
        $formatted = $this->formattedDocumentOutputHtml($row, $details);

        if ($formatted === '') {
            $fallback = $this->detailsToString($details);

            if ($fallback === '') {
                return '<strong>'.$this->escapeInlineHtml($title).'</strong>';
            }

            return '<strong>'.$this->escapeInlineHtml($title).'</strong><br>'.$this->escapeInlineHtml($fallback);
        }

        return '<strong>'.$this->escapeInlineHtml($title).'</strong><br>'.$formatted;
    }

    private function formattedDocumentOutputText(ServiceRecordRow $row, array $details): string
    {
        $html = $this->formattedDocumentOutputHtml($row, $details);

        if ($html === '') {
            return '';
        }

        $text = preg_replace('/<br\s*\/?>/i', "\n", $html) ?? $html;
        $text = strip_tags($text);

        return trim(html_entity_decode($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
    }

    private function formattedDocumentOutputHtml(ServiceRecordRow $row, array $details): string
    {
        $schemaVector = $this->resolveRowSchemaVector($row);
        $schemaPayload = is_array($schemaVector?->schema_payload ?? null) ? $schemaVector->schema_payload : [];
        $documentOutput = trim((string) ($schemaPayload['document_output'] ?? ''));

        if ($documentOutput === '') {
            return '';
        }

        return $this->renderDocumentOutputHtml($documentOutput, $details);
    }

    private function resolveRowSchemaVector(ServiceRecordRow $row): ?SchemaVector
    {
        if ($row->relationLoaded('schemaVector')) {
            $related = $row->getRelation('schemaVector');

            return $related instanceof SchemaVector ? $related : null;
        }

        $related = $row->schemaVector;

        return $related instanceof SchemaVector ? $related : null;
    }

    private function renderDocumentOutputHtml(string $documentOutput, array $details): string
    {
        $html = preg_replace_callback(
            '/<span\b[^>]*data-variable="([^"]+)"[^>]*>.*?<\/span>/is',
            fn (array $matches) => $this->escapeInlineHtml($this->resolveDocumentOutputValue($matches[1], $details)),
            $documentOutput
        ) ?? $documentOutput;

        $html = preg_replace_callback(
            '/\{\{\s*([a-zA-Z0-9._-]+)\s*\}\}/',
            fn (array $matches) => $this->escapeInlineHtml($this->resolveDocumentOutputValue($matches[1], $details)),
            $html
        ) ?? $html;

        if (trim(strip_tags($html)) === '') {
            return '';
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        @$dom->loadHTML(
            '<?xml encoding="utf-8" ?><body>'.$html.'</body>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        $body = $dom->getElementsByTagName('body')->item(0);

        if (! $body instanceof DOMNode) {
            return '';
        }

        return trim($this->renderDocumentOutputNodeChildren($body));
    }

    private function renderDocumentOutputNodeChildren(DOMNode $node): string
    {
        $html = '';

        foreach ($node->childNodes as $child) {
            $html .= $this->renderDocumentOutputNode($child);
        }

        return $html;
    }

    private function renderDocumentOutputNode(DOMNode $node): string
    {
        if ($node->nodeType === XML_TEXT_NODE) {
            $value = (string) ($node->nodeValue ?? '');

            return $this->escapeInlineHtml($value);
        }

        if ($node->nodeType !== XML_ELEMENT_NODE) {
            return '';
        }

        $name = strtolower($node->nodeName);
        $inner = $this->renderDocumentOutputNodeChildren($node);

        return match ($name) {
            'strong', 'b' => $inner === '' ? '' : '<strong>'.$inner.'</strong>',
            'em', 'i' => $inner === '' ? '' : '<em>'.$inner.'</em>',
            'u' => $inner === '' ? '' : '<u>'.$inner.'</u>',
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6' => $inner === '' ? '' : '<strong>'.$inner.'</strong><br>',
            'p', 'div' => $inner === '' ? '' : $inner.'<br>',
            'br' => '<br>',
            'ul', 'ol' => $this->renderDocumentOutputList($node, $name === 'ol'),
            'li' => $inner === '' ? '' : '&#8226; '.$inner.'<br>',
            'span' => $inner,
            default => $inner,
        };
    }

    private function renderDocumentOutputList(DOMNode $node, bool $ordered = false): string
    {
        $html = '';
        $index = 1;

        foreach ($node->childNodes as $child) {
            if ($child->nodeType !== XML_ELEMENT_NODE || strtolower($child->nodeName) !== 'li') {
                continue;
            }

            $inner = trim($this->renderDocumentOutputNodeChildren($child));

            if ($inner === '') {
                continue;
            }

            $prefix = $ordered ? $index.'. ' : '&#8226; ';
            $html .= $prefix.$inner.'<br>';
            $index++;
        }

        return $html;
    }

    private function resolveDocumentOutputValue(string $key, array $details): string
    {
        $value = data_get($details, $key, '');

        if (is_array($value)) {
            $value = implode(', ', array_map(
                fn ($item) => is_scalar($item) ? trim((string) $item) : '',
                $value
            ));
        }

        return is_scalar($value) ? trim((string) $value) : '';
    }

    private function escapeInlineHtml(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
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
