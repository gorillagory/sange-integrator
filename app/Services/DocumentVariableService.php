<?php

namespace App\Services;

class DocumentVariableService
{
    public static function supportedDocumentTypes(): array
    {
        return [
            'invoice',
            'receipt',
            'quote',
            'itinerary',
        ];
    }

    public static function getDictionary(string $documentType): array
    {
        $shared = self::getSharedVariables();

        $specific = match ($documentType) {
            'invoice' => self::getInvoiceVariables(),
            'receipt' => self::getReceiptVariables(),
            'quote' => self::getQuoteVariables(),
            'itinerary' => self::getItineraryVariables(),
            default => [],
        };

        return [
            'Global Variables' => $shared,
            'Document Specific' => $specific,
        ];
    }

    private static function getSharedVariables(): array
    {
        return [
            [
                'key' => 'company.name',
                'description' => 'Company name',
                'example' => 'Bayam Travel Sdn Bhd',
            ],
            [
                'key' => 'company.logo_url',
                'description' => 'Company logo image path or URL',
                'example' => '/storage/logos/company.png',
                'is_image' => true,
            ],
            [
                'key' => 'company.email',
                'description' => 'Company email',
                'example' => 'hello@bayam.test',
            ],
            [
                'key' => 'company.phone',
                'description' => 'Company phone number',
                'example' => '+60 123 456 789',
            ],
            [
                'key' => 'company.address',
                'description' => 'Company full address',
                'example' => '123 Travel Suite, Kuala Lumpur',
            ],
            [
                'key' => 'main_group.name',
                'description' => 'Main group company name',
                'example' => 'Bayam Group',
            ],
            [
                'key' => 'main_group.logo_url',
                'description' => 'Main group logo image path or URL',
                'example' => '/storage/logos/group.png',
                'is_image' => true,
            ],
            [
                'key' => 'main_group.address',
                'description' => 'Main group company address',
                'example' => 'Wisma Bayam, Kelantan',
            ],
            [
                'key' => 'client.name',
                'description' => 'Customer or company name',
                'example' => 'Acme Corp',
            ],
            [
                'key' => 'client.email',
                'description' => 'Customer contact email',
                'example' => 'billing@acme.test',
            ],
            [
                'key' => 'client.address',
                'description' => 'Customer billing address',
                'example' => '456 Buyer Lane, Penang',
            ],
        ];
    }

    private static function getInvoiceVariables(): array
    {
        return [
            [
                'key' => 'service_record.reference_no',
                'description' => 'Service record reference number',
                'example' => 'SRV-202605-AB123',
            ],
            [
                'key' => 'service_record.service_group_key',
                'description' => 'Service group identity for this captured service record',
                'example' => 'travel.services',
            ],
            [
                'key' => 'service_record.document_no',
                'description' => 'Canonical document number attached to this service record',
                'example' => 'DOC-2026-001',
            ],
            [
                'key' => 'invoice.number',
                'description' => 'System-generated invoice ID',
                'example' => 'INV-2026-001',
            ],
            [
                'key' => 'invoice.issue_date',
                'description' => 'Invoice issue date',
                'example' => '01 May 2026',
            ],
            [
                'key' => 'invoice.due_date',
                'description' => 'Payment due date',
                'example' => '15 May 2026',
            ],
            [
                'key' => 'invoice.subtotal',
                'description' => 'Amount before tax',
                'example' => 'RM 1,000.00',
            ],
            [
                'key' => 'invoice.tax_total',
                'description' => 'Tax total',
                'example' => 'RM 60.00',
            ],
            [
                'key' => 'invoice.grand_total',
                'description' => 'Final amount due',
                'example' => 'RM 1,060.00',
            ],
            [
                'key' => 'invoice.line_items',
                'description' => 'ARRAY: Source for table blocks',
                'example' => '[Loop Target]',
                'is_array' => true,
                'children' => [
                    ['key' => 'description', 'description' => 'Service name'],
                    ['key' => 'unit', 'description' => 'Unit label such as pax, night, or ticket'],
                    ['key' => 'quantity', 'description' => 'Qty billed'],
                    ['key' => 'unit_price', 'description' => 'Unit cost'],
                    ['key' => 'total', 'description' => 'Row total'],
                ],
            ],
            [
                'key' => 'finance.formatted_grand_total',
                'description' => 'Formatted total from the service record finance payload',
                'example' => 'RM 1,060.00',
            ],
        ];
    }

    private static function getReceiptVariables(): array
    {
        return [
            [
                'key' => 'receipt.number',
                'description' => 'System-generated receipt ID',
                'example' => 'REC-2026-089',
            ],
            [
                'key' => 'receipt.payment_date',
                'description' => 'Date payment was received',
                'example' => '02 May 2026',
            ],
            [
                'key' => 'receipt.amount_paid',
                'description' => 'Amount received',
                'example' => 'RM 1,060.00',
            ],
            [
                'key' => 'receipt.payment_method',
                'description' => 'Payment method',
                'example' => 'Credit Card',
            ],
            [
                'key' => 'receipt.reference_id',
                'description' => 'Gateway or transaction ID',
                'example' => 'PAY-2026-991',
            ],
        ];
    }

    private static function getQuoteVariables(): array
    {
        return [
            [
                'key' => 'quote.number',
                'description' => 'System-generated quote ID',
                'example' => 'QT-2026-044',
            ],
            [
                'key' => 'quote.valid_until',
                'description' => 'Quote expiration',
                'example' => '30 May 2026',
            ],
            [
                'key' => 'quote.grand_total',
                'description' => 'Estimated final cost',
                'example' => 'RM 2,500.00',
            ],
            [
                'key' => 'quote.line_items',
                'description' => 'ARRAY: Source for table blocks',
                'example' => '[Loop Target]',
                'is_array' => true,
                'children' => [
                    ['key' => 'description', 'description' => 'Service name'],
                    ['key' => 'unit', 'description' => 'Unit label such as pax, night, or ticket'],
                    ['key' => 'quantity', 'description' => 'Estimated quantity'],
                    ['key' => 'unit_price', 'description' => 'Unit cost'],
                    ['key' => 'total', 'description' => 'Estimated total'],
                ],
            ],
        ];
    }

    private static function getItineraryVariables(): array
    {
        return [
            [
                'key' => 'service_record.reference',
                'description' => 'Service record reference number',
                'example' => 'SRV-88992',
            ],
            [
                'key' => 'service_record.start_date',
                'description' => 'Service record start date',
                'example' => '10 Dec 2026',
            ],
            [
                'key' => 'service_record.end_date',
                'description' => 'Service record end date',
                'example' => '24 Dec 2026',
            ],
            [
                'key' => 'service_record.status',
                'description' => 'Current service record status',
                'example' => 'Draft',
            ],
            [
                'key' => 'service_rows',
                'description' => 'ARRAY: Canonical service record rows',
                'example' => '[Loop Target]',
                'is_array' => true,
                'children' => [
                    ['key' => 'service.title', 'description' => 'Service title'],
                    ['key' => 'service.date', 'description' => 'Captured service date'],
                    ['key' => 'service.details', 'description' => 'Flattened service summary'],
                    ['key' => 'fields', 'description' => 'Schema-driven operational fields'],
                    ['key' => 'finance.line_total', 'description' => 'Formatted line total'],
                ],
            ],
            [
                'key' => 'schema_vectors',
                'description' => 'ARRAY: Schema vector identities tied to the service record rows',
                'example' => '[Loop Target]',
                'is_array' => true,
                'children' => [
                    ['key' => 'service_code', 'description' => 'Schema vector code'],
                    ['key' => 'service_name', 'description' => 'Schema vector display name'],
                    ['key' => 'version', 'description' => 'Schema vector version used at capture time'],
                ],
            ],
        ];
    }
}
