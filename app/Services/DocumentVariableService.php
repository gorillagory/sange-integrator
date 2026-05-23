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
            'letter',
            'memo',
            'reply',
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
            'letter' => self::getLetterVariables(),
            'memo' => self::getMemoVariables(),
            'reply' => self::getReplyVariables(),
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
            [
                'key' => 'client.profile',
                'description' => 'Client profile or operating summary',
                'example' => 'Energy enterprise account with travel-code and billing controls.',
            ],
            [
                'key' => 'client.remarks',
                'description' => 'Reusable client remarks or operational instructions',
                'example' => "Patient EID: EID-77821\nTravel Code: AZFA",
            ],
            [
                'key' => 'author.name',
                'description' => 'Service record author or creator name',
                'example' => 'Muhammad Faizal',
            ],
            [
                'key' => 'author.email',
                'description' => 'Service record author email',
                'example' => 'faizal@bayam.test',
            ],
            [
                'key' => 'user.name',
                'description' => 'Alias of the service record author name',
                'example' => 'Muhammad Faizal',
            ],
            [
                'key' => 'user.email',
                'description' => 'Alias of the service record author email',
                'example' => 'faizal@bayam.test',
            ],
            [
                'key' => 'assigned_user.name',
                'description' => 'Assigned personnel name',
                'example' => 'Nur Amalina',
            ],
            [
                'key' => 'assigned_user.email',
                'description' => 'Assigned personnel email',
                'example' => 'amalina@bayam.test',
            ],
            [
                'key' => 'remarks',
                'description' => 'Top-level remarks snapshot for the current document',
                'example' => "Patient EID: EID-77821\nTravel Code: AZFA",
            ],
            [
                'key' => 'document_links.reference_value',
                'description' => 'Canonical document reference value',
                'example' => 'DOC-2026-001',
            ],
            [
                'key' => 'document_links.reference_label',
                'description' => 'Human-readable label for the digital document identifier',
                'example' => 'Document Reference',
            ],
            [
                'key' => 'document_links.reference_qr_data_uri',
                'description' => 'QR code image for the canonical document reference',
                'example' => 'data:image/svg+xml;base64,...',
                'is_image' => true,
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
                'key' => 'service_record.remarks',
                'description' => 'Captured service-record remarks snapshot',
                'example' => "Patient EID: EID-77821\nTravel Code: AZFA",
            ],
            [
                'key' => 'service_record.author_name',
                'description' => 'Service record author name',
                'example' => 'Muhammad Faizal',
            ],
            [
                'key' => 'service_record.assigned_user_name',
                'description' => 'Assigned personnel name',
                'example' => 'Nur Amalina',
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
                'key' => 'service_record.remarks',
                'description' => 'Service record remarks snapshot',
                'example' => "Patient EID: EID-77821\nTravel Code: AZFA",
            ],
            [
                'key' => 'service_rows',
                'description' => 'ARRAY: Canonical service record rows',
                'example' => '[Loop Target]',
                'is_array' => true,
                'children' => [
                    ['key' => 'service.title', 'description' => 'Service title'],
                    ['key' => 'service.date', 'description' => 'Service date'],
                    ['key' => 'service.time', 'description' => 'Service time'],
                    ['key' => 'service.details', 'description' => 'Service detail summary'],
                    ['key' => 'service.confirmation', 'description' => 'Confirmation value'],
                    ['key' => 'finance.line_total', 'description' => 'Formatted row total'],
                ],
            ],
        ];
    }

    private static function getLetterVariables(): array
    {
        return [
            ['key' => 'letter.reference_no', 'description' => 'Letter reference number', 'example' => 'LTR-2026-014'],
            ['key' => 'letter.date', 'description' => 'Letter issue date', 'example' => '19 May 2026'],
            ['key' => 'letter.recipient_name', 'description' => 'Recipient name', 'example' => 'Ms. Farah Azlan'],
            ['key' => 'letter.recipient_title', 'description' => 'Recipient title', 'example' => 'Corporate Travel Lead'],
            ['key' => 'letter.recipient_company', 'description' => 'Recipient company', 'example' => 'Acme Corporation Sdn Bhd'],
            ['key' => 'letter.recipient_address', 'description' => 'Recipient address block', 'example' => "Suite 101, Innovation Tower\nCyberjaya, Selangor"],
            ['key' => 'letter.subject', 'description' => 'Subject line', 'example' => 'Travel Arrangement Confirmation'],
            ['key' => 'letter.salutation', 'description' => 'Opening salutation', 'example' => 'Dear Ms. Farah,'],
            ['key' => 'letter.body', 'description' => 'Main body text for a formal letter', 'example' => "Paragraph one.\n\nParagraph two."],
            ['key' => 'letter.closing', 'description' => 'Closing phrase', 'example' => 'Yours faithfully,'],
            ['key' => 'letter.signature_name', 'description' => 'Signer name', 'example' => 'Bayam Travel Operations'],
            ['key' => 'letter.signature_title', 'description' => 'Signer role', 'example' => 'Corporate Travel Desk'],
        ];
    }

    private static function getMemoVariables(): array
    {
        return [
            ['key' => 'memo.reference_no', 'description' => 'Memo reference number', 'example' => 'MEMO-2026-009'],
            ['key' => 'memo.date', 'description' => 'Memo date', 'example' => '19 May 2026'],
            ['key' => 'memo.to', 'description' => 'Memo recipient line', 'example' => 'All Travel Operations Staff'],
            ['key' => 'memo.from', 'description' => 'Memo sender line', 'example' => 'Agency Admin Office'],
            ['key' => 'memo.subject', 'description' => 'Memo subject', 'example' => 'Updated Service Record Capture Standard'],
            ['key' => 'memo.body', 'description' => 'Memo body content', 'example' => "Please ensure every service record includes...\n\nThis memo is effective immediately."],
            ['key' => 'memo.footer_note', 'description' => 'Optional footer note', 'example' => 'This memo is effective immediately.'],
        ];
    }

    private static function getReplyVariables(): array
    {
        return [
            ['key' => 'reply.reference_no', 'description' => 'Reply reference number', 'example' => 'RPL-2026-003'],
            ['key' => 'reply.date', 'description' => 'Reply date', 'example' => '19 May 2026'],
            ['key' => 'reply.to', 'description' => 'Reply recipient', 'example' => 'Procurement Unit'],
            ['key' => 'reply.attention', 'description' => 'Attention line', 'example' => 'Mr. Hafiz Rahman'],
            ['key' => 'reply.subject', 'description' => 'Reply subject', 'example' => 'Reply to Document Clarification Request'],
            ['key' => 'reply.opening', 'description' => 'Opening line', 'example' => 'We refer to your clarification request...'],
            ['key' => 'reply.body', 'description' => 'Reply body text', 'example' => "The attached service record and invoice reflect...\n\nPlease review the enclosed supporting note."],
            ['key' => 'reply.closing', 'description' => 'Closing line', 'example' => 'Thank you.'],
            ['key' => 'reply.signature_name', 'description' => 'Signer name', 'example' => 'Document Control Desk'],
        ];
    }
}
