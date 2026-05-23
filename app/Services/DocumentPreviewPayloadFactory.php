<?php

namespace App\Services;

use App\Services\Handlers\HandlerRegistry;

class DocumentPreviewPayloadFactory
{
    public function __construct(
        private readonly HandlerRegistry $handlers,
        private readonly DocumentQrCodeService $qrCodes,
    ) {
    }

    public function make(string $documentType): array
    {
        $handler = $this->handlers->default()->toArray();

        return match ($documentType) {
            'invoice' => $this->invoice($handler),
            'receipt' => $this->receipt($handler),
            'quote' => $this->quote($handler),
            'itinerary' => $this->itinerary($handler),
            'letter' => $this->letter($handler),
            'memo' => $this->memo($handler),
            'reply' => $this->reply($handler),
            default => $this->invoice($handler),
        };
    }

    private function shared(): array
    {
        $remarks = "Patient EID: EID-77821\nTravel Code: AZFA\nBill under Mubadala operations allocation.";
        $referenceValue = 'DOC-2026-001';

        return [
            'company' => [
                'name' => 'Bayam Travel Sdn Bhd',
                'logo_url' => $this->placeholderDataUri('Bayam Travel', '#ffffff', '#e11d48'),
                'email' => 'hello@bayam.test',
                'phone' => '+60 12-345 6789',
                'address' => 'Level 12, Bayam Tower, Kuala Lumpur, Malaysia',
            ],
            'main_group' => [
                'name' => 'Bayam Group',
                'logo_url' => $this->placeholderDataUri('Bayam Group', '#ffffff', '#0f172a'),
                'address' => 'Wisma Bayam, Kota Bharu, Kelantan',
            ],
            'client' => [
                'name' => 'Acme Corporation Sdn Bhd',
                'email' => 'billing@acme.test',
                'address' => 'Suite 101, Innovation Tower, Cyberjaya, Selangor',
                'profile' => 'Regional energy client with controlled travel approvals and document-linked traveler IDs.',
                'remarks' => $remarks,
            ],
            'author' => [
                'id' => 7,
                'name' => 'Muhammad Faizal Abdul',
                'email' => 'faizal@bayam.test',
            ],
            'user' => [
                'id' => 7,
                'name' => 'Muhammad Faizal Abdul',
                'email' => 'faizal@bayam.test',
            ],
            'assigned_user' => [
                'id' => 12,
                'name' => 'Nur Amalina Rahim',
                'email' => 'amalina@bayam.test',
            ],
            'remarks' => $remarks,
            'document_links' => [
                'reference_value' => $referenceValue,
                'reference_label' => 'Document Reference',
                'reference_qr_data_uri' => $this->qrCodes->generateDataUri($referenceValue),
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
            'service_record' => $this->serviceRecordPayload($handler, 'DocumentLocked'),
            'service_rows' => $this->sampleServices(),
            'schema_vectors' => array_map(fn (array $row) => $row['schema_vector'], $this->sampleServices()),
            'operation' => $this->operationPayload($handler, 'DocumentLocked'),
            'services' => $this->sampleServices(),
            'service_instances' => $this->sampleServices(),
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
            'service_record' => $this->serviceRecordPayload($handler, 'Draft'),
            'operation' => $this->operationPayload($handler, 'Draft'),
            'service_rows' => $this->sampleServices(),
            'schema_vectors' => array_map(fn (array $row) => $row['schema_vector'], $this->sampleServices()),
            'services' => $this->sampleServices(),
            'service_instances' => $this->sampleServices(),
        ]);
    }

    private function letter(array $handler): array
    {
        return array_replace_recursive($this->shared(), [
            'handler' => $handler,
            'letter' => [
                'reference_no' => 'LTR-2026-014',
                'date' => now()->format('d M Y'),
                'recipient_name' => 'Ms. Farah Azlan',
                'recipient_title' => 'Corporate Travel Lead',
                'recipient_company' => 'Acme Corporation Sdn Bhd',
                'recipient_address' => "Suite 101, Innovation Tower\nCyberjaya, Selangor",
                'subject' => 'Travel Arrangement Confirmation',
                'salutation' => 'Dear Ms. Farah,',
                'body' => "We are pleased to confirm the travel arrangements for the requested movement.\n\nAll services have been secured under the approved travel code and are attached for your reference.\n\nPlease contact our operations desk should any amendment be required before departure.",
                'closing' => 'Yours faithfully,',
                'signature_name' => 'Bayam Travel Operations',
                'signature_title' => 'Corporate Travel Desk',
            ],
        ]);
    }

    private function memo(array $handler): array
    {
        return array_replace_recursive($this->shared(), [
            'handler' => $handler,
            'memo' => [
                'reference_no' => 'MEMO-2026-009',
                'date' => now()->format('d M Y'),
                'to' => 'All Travel Operations Staff',
                'from' => 'Agency Admin Office',
                'subject' => 'Updated Service Record Capture Standard',
                'body' => "Please ensure every service record includes the client-specific remarks snapshot before locking the document.\n\nThe captured remarks must match the client instruction preset used at the time of booking.",
                'footer_note' => 'This memo is effective immediately.',
            ],
        ]);
    }

    private function reply(array $handler): array
    {
        return array_replace_recursive($this->shared(), [
            'handler' => $handler,
            'reply' => [
                'reference_no' => 'RPL-2026-003',
                'date' => now()->format('d M Y'),
                'to' => 'Procurement Unit',
                'attention' => 'Mr. Hafiz Rahman',
                'subject' => 'Reply to Document Clarification Request',
                'opening' => 'We refer to your clarification request regarding the submitted invoice package.',
                'body' => "The attached service record and invoice reflect the final approved scope, including traveler identifiers and the validated travel code.\n\nPlease review the enclosed supporting note for the itemized reconciliation.",
                'closing' => 'Thank you.',
                'signature_name' => 'Document Control Desk',
            ],
        ]);
    }

    private function serviceRecordPayload(array $handler, string $status): array
    {
        return [
            'id' => 1,
            'service_group_key' => $handler['service_group_key'] ?? $handler['handler_key'],
            'handler_key' => $handler['handler_key'],
            'reference' => 'SRV-2026-88992',
            'reference_no' => 'SRV-2026-88992',
            'document_no' => 'DOC-2026-001',
            'status' => $status,
            'remarks' => $this->shared()['remarks'],
            'author_name' => 'Muhammad Faizal Abdul',
            'author_email' => 'faizal@bayam.test',
            'assigned_user_name' => 'Nur Amalina Rahim',
            'assigned_user_email' => 'amalina@bayam.test',
            'captured_at' => now()->toIso8601String(),
            'start_date' => now()->format('d M Y'),
            'end_date' => now()->addDays(6)->format('d M Y'),
        ];
    }

    private function operationPayload(array $handler, string $status): array
    {
        return [
            'id' => 1,
            'handler_key' => $handler['handler_key'],
            'reference' => 'SRV-2026-88992',
            'reference_no' => 'SRV-2026-88992',
            'document_no' => 'DOC-2026-001',
            'status' => $status,
            'captured_at' => now()->toIso8601String(),
            'start_date' => now()->format('d M Y'),
            'end_date' => now()->addDays(6)->format('d M Y'),
        ];
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

    private function placeholderDataUri(string $text, string $background, string $foreground): string
    {
        $svg = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 120"><rect width="240" height="120" rx="18" fill="%s"/><text x="120" y="66" text-anchor="middle" font-family="Helvetica, Arial, sans-serif" font-size="24" font-weight="700" fill="%s">%s</text></svg>',
            htmlspecialchars($background, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            htmlspecialchars($foreground, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
        );

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
