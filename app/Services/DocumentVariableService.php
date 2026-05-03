<?php

namespace App\Services;

class DocumentVariableService
{
    /**
     * Get the available data dictionary for a specific document type.
     */
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
                'description' => 'Your agency name',
                'example' => 'Bayam Travel'
            ],
            [
                'key' => 'company.email',
                'description' => 'Your agency contact email',
                'example' => 'hello@bayam.test'
            ],
            [
                'key' => 'company.phone',
                'description' => 'Your agency phone number',
                'example' => '+60 123 456 789'
            ],
            [
                'key' => 'company.address',
                'description' => 'Your agency physical address',
                'example' => '123 Travel Suite, Kuala Lumpur'
            ],
            [
                'key' => 'client.name',
                'description' => 'The customer or company name',
                'example' => 'Acme Corp'
            ],
            [
                'key' => 'client.email',
                'description' => 'The customer contact email',
                'example' => 'billing@acme.test'
            ],
            [
                'key' => 'client.address',
                'description' => 'The customer billing address',
                'example' => '456 Buyer Lane, Penang'
            ],
        ];
    }

    private static function getInvoiceVariables(): array
    {
        return [
            [
                'key' => 'invoice.number',
                'description' => 'The system-generated invoice ID',
                'example' => 'INV-2026-001'
            ],
            [
                'key' => 'invoice.issue_date',
                'description' => 'Date the invoice was created',
                'example' => '01 May 2026'
            ],
            [
                'key' => 'invoice.due_date',
                'description' => 'Date the payment is expected',
                'example' => '15 May 2026'
            ],
            [
                'key' => 'invoice.subtotal',
                'description' => 'Amount before tax/discounts',
                'example' => '$1,000.00'
            ],
            [
                'key' => 'invoice.tax_total',
                'description' => 'Total calculated tax',
                'example' => '$60.00'
            ],
            [
                'key' => 'invoice.grand_total',
                'description' => 'Final amount due',
                'example' => '$1,060.00'
            ],
            // Table Loop Target
            [
                'key' => 'invoice.line_items',
                'description' => 'ARRAY: Use this as the Data Source in Table blocks',
                'example' => '[Loop Target]',
                'is_array' => true,
                'children' => [
                    ['key' => 'item.description', 'description' => 'Name of the service'],
                    ['key' => 'item.quantity', 'description' => 'Qty billed'],
                    ['key' => 'item.unit_price', 'description' => 'Cost per unit'],
                    ['key' => 'item.total', 'description' => 'Row total'],
                ]
            ],
        ];
    }

    private static function getReceiptVariables(): array
    {
        return [
            [
                'key' => 'receipt.number',
                'description' => 'The system-generated receipt ID',
                'example' => 'REC-2026-089'
            ],
            [
                'key' => 'receipt.payment_date',
                'description' => 'Date the payment was received',
                'example' => '02 May 2026'
            ],
            [
                'key' => 'receipt.amount_paid',
                'description' => 'Total amount received',
                'example' => '$1,060.00'
            ],
            [
                'key' => 'receipt.payment_method',
                'description' => 'How the client paid',
                'example' => 'Credit Card (Stripe)'
            ],
            [
                'key' => 'receipt.reference_id',
                'description' => 'Gateway transaction ID',
                'example' => 'ch_3Mqw...'
            ],
        ];
    }

    private static function getQuoteVariables(): array
    {
        return [
            [
                'key' => 'quote.number',
                'description' => 'The system-generated quote ID',
                'example' => 'QT-2026-044'
            ],
            [
                'key' => 'quote.valid_until',
                'description' => 'Expiration date of the offer',
                'example' => '30 May 2026'
            ],
            [
                'key' => 'quote.grand_total',
                'description' => 'Estimated final cost',
                'example' => '$2,500.00'
            ],
            [
                'key' => 'quote.line_items',
                'description' => 'ARRAY: Use this as the Data Source in Table blocks',
                'example' => '[Loop Target]',
                'is_array' => true,
                'children' => [
                    ['key' => 'item.description', 'description' => 'Name of the service'],
                    ['key' => 'item.quantity', 'description' => 'Estimated Qty'],
                    ['key' => 'item.unit_price', 'description' => 'Estimated cost per unit'],
                    ['key' => 'item.total', 'description' => 'Estimated Row total'],
                ]
            ],
        ];
    }

    private static function getItineraryVariables(): array
    {
        return [
            [
                'key' => 'booking.reference',
                'description' => 'The master booking PNR',
                'example' => 'BKG-88992'
            ],
            [
                'key' => 'booking.start_date',
                'description' => 'Trip commencement date',
                'example' => '10 Dec 2026'
            ],
            [
                'key' => 'booking.end_date',
                'description' => 'Trip conclusion date',
                'example' => '24 Dec 2026'
            ],
            [
                'key' => 'booking.pax_count',
                'description' => 'Total number of passengers',
                'example' => '4'
            ],
            // Complex Array Loops
            [
                'key' => 'booking.passengers',
                'description' => 'ARRAY: Use this to loop passenger details',
                'example' => '[Loop Target]',
                'is_array' => true,
                'children' => [
                    ['key' => 'passenger.full_name', 'description' => 'Legal name'],
                    ['key' => 'passenger.passport', 'description' => 'Passport Number'],
                    ['key' => 'passenger.type', 'description' => 'Adult / Child / Infant'],
                ]
            ],
            [
                'key' => 'booking.services',
                'description' => 'ARRAY: Use this to loop daily itinerary items',
                'example' => '[Loop Target]',
                'is_array' => true,
                'children' => [
                    ['key' => 'service.date', 'description' => 'Day of the service'],
                    ['key' => 'service.time', 'description' => 'Start time'],
                    ['key' => 'service.title', 'description' => 'Name of the hotel/flight/tour'],
                    ['key' => 'service.details', 'description' => 'Long description or notes'],
                    ['key' => 'service.confirmation', 'description' => 'Vendor confirmation number'],
                ]
            ],
        ];
    }
}
