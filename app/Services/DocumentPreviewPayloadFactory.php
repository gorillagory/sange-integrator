<?php

namespace App\Services;

use App\Services\Handlers\HandlerRegistry;

class DocumentPreviewPayloadFactory
{
    public function __construct(
        private readonly HandlerRegistry $handlers,
    ) {
    }

    public function make(string $documentType): array
    {
        $handler = $this->handlers->default();
        $payload = match ($documentType) {
            'invoice' => $this->invoice($handler->toArray()),
            'receipt' => $this->receipt($handler->toArray()),
            'quote' => $this->quote($handler->toArray()),
            'itinerary' => $this->itinerary($handler->toArray()),
            default => $this->invoice($handler->toArray()),
        };

        return $payload;
    }

    private function shared(): array
    {
        return [
            'company' => [
                'name' => 'Bayam Travel Sdn Bhd',
                'logo_url' => 'https://dummyimage.com/240x120/ffffff/e11d48.png&text=Bayam+Travel',
                'email' => 'hello@bayam.test',
                'phone' => '+60 12-345 6789',
                'address' => 'Level 12, Bayam Tower, Kuala Lumpur, Malaysia',
            ],
            'main_group' => [
                'name' => 'Bayam Group',
                'logo_url' => 'https://dummyimage.com/240x120/ffffff/0f172a.png&text=Bayam+Group',
                'address' => 'Wisma Bayam, Kota Bharu, Kelantan',
            ],
            'client' => [
                'name' => 'Acme Corporation Sdn Bhd',
                'email' => 'billing@acme.test',
                'address' => 'Suite 101, Innovation Tower, Cyberjaya, Selangor',
                'remarks' => "Patient EID: EID-77821\nTravel Code: AZFA\nBill under Mubadala operations allocation.",
            ],
        ];
    }

    private function invoice(array $handler): array
    {
        return array_replace_recursive($this->shared(), [
            'handler' => $handler,
            'finance' => [
                'subtotal' => 3850,
                'tax_total' => 231,
                'grand_total' => 4081,
                'formatted_subtotal' => 'RM 3,850.00',
                'formatted_tax_total' => 'RM 231.00',
                'formatted_grand_total' => 'RM 4,081.00',
            ],
            'service_record' => [
                'id' => 1,
                'service_group_key' => $handler['service_group_key'] ?? $handler['handler_key'],
                'handler_key' => $handler['handler_key'],
                'reference' => 'SRV-2026-88992',
                'reference_no' => 'SRV-2026-88992',
                'document_no' => 'DOC-2026-001',
                'status' => 'DocumentLocked',
                'remarks' => "Patient EID: EID-77821\nTravel Code: AZFA\nBill under Mubadala operations allocation.",
                'captured_at' => now()->toIso8601String(),
                'start_date' => now()->format('d M Y'),
                'end_date' => now()->addDays(6)->format('d M Y'),
            ],
            'service_rows' => $this->sampleServices(),
            'schema_vectors' => array_map(fn (array $row) => $row['schema_vector'], $this->sampleServices()),
            'operation' => [
                'id' => 1,
                'handler_key' => $handler['handler_key'],
                'reference' => 'SRV-2026-88992',
                'reference_no' => 'SRV-2026-88992',
                'document_no' => 'DOC-2026-001',
                'status' => 'DocumentLocked',
                'captured_at' => now()->toIso8601String(),
                'start_date' => now()->format('d M Y'),
                'end_date' => now()->addDays(6)->format('d M Y'),
            ],
            'services' => $this->sampleServices(),
            'service_instances' => $this->sampleServices(),
            'remarks' => "Patient EID: EID-77821\nTravel Code: AZFA\nBill under Mubadala operations allocation.",
            'invoice' => [
                'number' => 'DOC-2026-001',
                'issue_date' => now()->format('d M Y'),
                'due_date' => now()->addDays(14)->format('d M Y'),
                'subtotal' => 'RM 3,850.00',
                'tax_total' => 'RM 231.00',
                'grand_total' => 'RM 4,081.00',
                'line_items' => [
                    [
                        'description' => 'Roundtrip Flight (KUL - NRT)',
                        'unit' => 'ticket',
                        'quantity' => 1,
                        'unit_price' => 'RM 2,500.00',
                        'total' => 'RM 2,500.00',
                    ],
                    [
                        'description' => 'Hotel Accommodation (3 Nights)',
                        'unit' => 'night',
                        'quantity' => 3,
                        'unit_price' => 'RM 400.00',
                        'total' => 'RM 1,200.00',
                    ],
                    [
                        'description' => 'Private Airport Transfer',
                        'unit' => 'trip',
                        'quantity' => 1,
                        'unit_price' => 'RM 150.00',
                        'total' => 'RM 150.00',
                    ],
                ],
            ],
        ]);
    }

    private function receipt(array $handler): array
    {
        return array_replace_recursive($this->shared(), [
            'handler' => $handler,
            'receipt' => [
                'number' => 'REC-2026-031',
                'payment_date' => now()->format('d M Y'),
                'amount_paid' => 'RM 4,081.00',
                'payment_method' => 'Bank Transfer',
                'reference_id' => 'BTX-2026-99118',
            ],
        ]);
    }

    private function quote(array $handler): array
    {
        return array_replace_recursive($this->shared(), [
            'handler' => $handler,
            'quote' => [
                'number' => 'QT-2026-018',
                'valid_until' => now()->addDays(7)->format('d M Y'),
                'subtotal' => 'RM 5,250.00',
                'tax_total' => 'RM 315.00',
                'grand_total' => 'RM 5,565.00',
                'line_items' => [
                    [
                        'description' => 'Family Package - Tokyo',
                        'unit' => 'pax',
                        'quantity' => 4,
                        'unit_price' => 'RM 1,200.00',
                        'total' => 'RM 4,800.00',
                    ],
                    [
                        'description' => 'Airport Meet And Greet',
                        'unit' => 'service',
                        'quantity' => 1,
                        'unit_price' => 'RM 450.00',
                        'total' => 'RM 450.00',
                    ],
                ],
            ],
        ]);
    }

    private function itinerary(array $handler): array
    {
        return array_replace_recursive($this->shared(), [
            'handler' => $handler,
            'service_record' => [
                'id' => 1,
                'service_group_key' => $handler['service_group_key'] ?? $handler['handler_key'],
                'handler_key' => $handler['handler_key'],
                'reference' => 'SRV-88992',
                'reference_no' => 'SRV-88992',
                'document_no' => 'DOC-2026-001',
                'status' => 'Draft',
                'remarks' => "Patient EID: EID-77821\nTravel Code: AZFA\nBill under Mubadala operations allocation.",
                'captured_at' => now()->toIso8601String(),
                'start_date' => now()->addDays(10)->format('d M Y'),
                'end_date' => now()->addDays(16)->format('d M Y'),
            ],
            'operation' => [
                'id' => 1,
                'handler_key' => $handler['handler_key'],
                'reference' => 'SRV-88992',
                'reference_no' => 'SRV-88992',
                'document_no' => 'DOC-2026-001',
                'status' => 'Draft',
                'captured_at' => now()->toIso8601String(),
                'start_date' => now()->addDays(10)->format('d M Y'),
                'end_date' => now()->addDays(16)->format('d M Y'),
            ],
            'service_rows' => $this->sampleServices(),
            'schema_vectors' => array_map(fn (array $row) => $row['schema_vector'], $this->sampleServices()),
            'services' => $this->sampleServices(),
            'service_instances' => $this->sampleServices(),
            'remarks' => "Patient EID: EID-77821\nTravel Code: AZFA\nBill under Mubadala operations allocation.",
        ]);
    }

    private function sampleServices(): array
    {
        return [
            [
                'schema_vector' => [
                    'id' => 1,
                    'service_code' => 'flight',
                    'service_type' => 'flight',
                    'service_name' => 'Flight',
                    'version' => 3,
                ],
                'service' => [
                    'date' => now()->addDays(10)->format('d M Y'),
                    'time' => '09:00',
                    'title' => 'Malaysia Airlines MH070',
                    'details' => 'Depart KUL to NRT',
                    'confirmation' => 'PNR-7788',
                ],
                'fields' => [
                    'flight_no' => 'MH070',
                    'route' => 'KUL - NRT',
                ],
                'details' => [
                    'flight_no' => 'MH070',
                    'route' => 'KUL - NRT',
                ],
                'details_extra' => [],
                'finance' => [
                    'qty' => 1,
                    'unit_name' => 'ticket',
                    'base_cost' => 'RM 2,500.00',
                    'supplier_cost' => 'RM 2,500.00',
                    'discount_amount' => 'RM 0.00',
                    'tax_amount' => 'RM 0.00',
                    'sell_price' => 'RM 2,500.00',
                    'line_total' => 'RM 2,500.00',
                ],
                'pricing' => [
                    'qty' => 1,
                    'unit_fare' => 'RM 2,500.00',
                    'tax_amount' => 'RM 0.00',
                    'line_total' => 'RM 2,500.00',
                ],
                'snapshot' => [
                    'captured_at' => now()->toIso8601String(),
                ],
            ],
            [
                'schema_vector' => [
                    'id' => 2,
                    'service_code' => 'tour',
                    'service_type' => 'tour',
                    'service_name' => 'Tour',
                    'version' => 1,
                ],
                'service' => [
                    'date' => now()->addDays(11)->format('d M Y'),
                    'time' => '15:00',
                    'title' => 'Tokyo City Tour',
                    'details' => 'Private guided city tour',
                    'confirmation' => 'TOUR-991',
                ],
                'fields' => [
                    'tour_type' => 'Private',
                    'duration' => 'Half Day',
                ],
                'details' => [
                    'tour_type' => 'Private',
                    'duration' => 'Half Day',
                ],
                'details_extra' => [],
                'finance' => [
                    'qty' => 1,
                    'unit_name' => 'package',
                    'base_cost' => 'RM 1,350.00',
                    'supplier_cost' => 'RM 1,350.00',
                    'discount_amount' => 'RM 0.00',
                    'tax_amount' => 'RM 0.00',
                    'sell_price' => 'RM 1,350.00',
                    'line_total' => 'RM 1,350.00',
                ],
                'pricing' => [
                    'qty' => 1,
                    'unit_fare' => 'RM 1,350.00',
                    'tax_amount' => 'RM 0.00',
                    'line_total' => 'RM 1,350.00',
                ],
                'snapshot' => [
                    'captured_at' => now()->toIso8601String(),
                ],
            ],
        ];
    }

}
