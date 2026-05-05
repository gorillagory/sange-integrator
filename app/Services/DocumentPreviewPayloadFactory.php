<?php

namespace App\Services;

class DocumentPreviewPayloadFactory
{
    public function make(string $documentType): array
    {
        return match ($documentType) {
            'invoice' => $this->invoice(),
            'receipt' => $this->receipt(),
            'quote' => $this->quote(),
            'itinerary' => $this->itinerary(),
            default => $this->invoice(),
        };
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
            ],
        ];
    }

    private function invoice(): array
    {
        return array_replace_recursive($this->shared(), [
            'invoice' => [
                'number' => 'INV-2026-001',
                'issue_date' => now()->format('d M Y'),
                'due_date' => now()->addDays(14)->format('d M Y'),
                'subtotal' => 'RM 3,850.00',
                'tax_total' => 'RM 0.00',
                'grand_total' => 'RM 3,850.00',
                'line_items' => [
                    [
                        'description' => 'Roundtrip Flight (KUL - NRT)',
                        'quantity' => 1,
                        'unit_price' => 'RM 2,500.00',
                        'total' => 'RM 2,500.00',
                    ],
                    [
                        'description' => 'Hotel Accommodation (3 Nights)',
                        'quantity' => 1,
                        'unit_price' => 'RM 1,200.00',
                        'total' => 'RM 1,200.00',
                    ],
                    [
                        'description' => 'Private Airport Transfer',
                        'quantity' => 1,
                        'unit_price' => 'RM 150.00',
                        'total' => 'RM 150.00',
                    ],
                ],
            ],
        ]);
    }

    private function receipt(): array
    {
        return array_replace_recursive($this->shared(), [
            'receipt' => [
                'number' => 'REC-2026-031',
                'payment_date' => now()->format('d M Y'),
                'amount_paid' => 'RM 3,850.00',
                'payment_method' => 'Bank Transfer',
                'reference_id' => 'BTX-2026-99118',
            ],
        ]);
    }

    private function quote(): array
    {
        return array_replace_recursive($this->shared(), [
            'quote' => [
                'number' => 'QT-2026-018',
                'valid_until' => now()->addDays(7)->format('d M Y'),
                'grand_total' => 'RM 5,250.00',
                'line_items' => [
                    [
                        'description' => 'Family Package - Tokyo',
                        'quantity' => 4,
                        'unit_price' => 'RM 1,312.50',
                        'total' => 'RM 5,250.00',
                    ],
                ],
            ],
        ]);
    }

    private function itinerary(): array
    {
        return array_replace_recursive($this->shared(), [
            'booking' => [
                'reference' => 'BKG-88992',
                'start_date' => now()->addDays(10)->format('d M Y'),
                'end_date' => now()->addDays(16)->format('d M Y'),
                'pax_count' => 4,
                'passengers' => [
                    [
                        'passenger' => [
                            'full_name' => 'Adam Iskandar',
                            'passport' => 'A12345678',
                            'type' => 'Adult',
                        ],
                    ],
                    [
                        'passenger' => [
                            'full_name' => 'Nur Alya',
                            'passport' => 'A87654321',
                            'type' => 'Adult',
                        ],
                    ],
                ],
                'services' => [
                    [
                        'service' => [
                            'date' => now()->addDays(10)->format('d M Y'),
                            'time' => '09:00',
                            'title' => 'Malaysia Airlines MH070',
                            'details' => 'Depart KUL to NRT',
                            'confirmation' => 'PNR-7788',
                        ],
                    ],
                    [
                        'service' => [
                            'date' => now()->addDays(11)->format('d M Y'),
                            'time' => '15:00',
                            'title' => 'Tokyo City Tour',
                            'details' => 'Private guided city tour',
                            'confirmation' => 'TOUR-991',
                        ],
                    ],
                ],
            ],
        ]);
    }
}
