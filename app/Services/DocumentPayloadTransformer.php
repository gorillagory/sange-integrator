<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DocumentPayloadTransformer
{
    /**
     * Transforms an Eloquent Model into the Payload Array required by the PDF Compiler.
     */
    public static function transform(string $documentType, Model $model): array
    {
        // 1. Global Company Data
        $globalData = [
            'company_name' => 'Bayam Travel Sdn Bhd',
            'company_email' => 'hello@bayamtravel.com',
            'company_phone' => '+609 741 8626',
            'company_address' => 'Wisma Bayam, 15050 Kota Bharu, Kelantan',
        ];

        // 2. Document-Specific Data
        $specificData = match ($documentType) {
            'invoice' => self::mapInvoice($model),
            'receipt' => self::mapReceipt($model),
            'quote' => self::mapQuote($model),
            'itinerary' => self::mapItinerary($model),
            default => [],
        };

        // Merge and return the complete payload
        return array_merge($globalData, $specificData);
    }

    /**
     * Maps a real Invoice Model to the Data Dictionary expected by the PDF
     */
    private static function mapInvoice(Model $invoice): array
    {
        return [
            // Client Info
            'client_name' => $invoice->client->name ?? 'Walk-in Customer',
            'client_address_1' => $invoice->client->address_line_1 ?? '',
            'client_address_2' => $invoice->client->address_line_2 ?? '',
            'client_address_3' => $invoice->client->city . ', ' . $invoice->client->state,
            'client_referral' => 'Ref: ' . ($invoice->salesperson->name ?? 'N/A'),

            // Invoice Info
            'invoice_number' => $invoice->invoice_number ?? 'DRAFT',
            'invoice_date' => $invoice->created_at ? $invoice->created_at->format('d M Y') : date('d M Y'),
            'invoice_month' => $invoice->created_at ? $invoice->created_at->format('F Y') : date('F Y'),
            'invoice_personnel' => $invoice->creator->name ?? 'System',
            'invoice_term' => $invoice->payment_terms ?? 'Due on Receipt',
            'contract_title' => 'TAX INVOICE',

            // 🚀 THE MAGIC: Loop through Booking Services and compile their dynamic schemas
            'items' => $invoice->services->map(function ($service) {
                return [
                    'name' => self::compileServiceDescription($service),
                    'total' => 'RM ' . number_format($service->total_price ?? 0, 2),
                ];
            })->toArray(),

            // Terms & Conditions
            'list_items' => [
                'All payments are non-refundable after 14 days.',
                'Please quote the Invoice Number on your bank transfer.',
                'Make checks payable to BAYAM TRAVEL SDN BHD.'
            ]
        ];
    }

    /**
     * Parses the HTML template from the Service Schema and injects real payload data.
     */
    private static function compileServiceDescription(Model $bookingService): string
    {
        // 1. Get the HTML template designed by the Admin in the Schema Builder
        // (Fallback to a basic string if they haven't designed one yet)
        $template = $bookingService->schema->schema_payload['document_output']
            ?? "<strong>{$bookingService->schema->display_name}</strong>";

        // 2. Get the actual JSON data filled out by the Travel Agent
        $payloadData = is_string($bookingService->payload)
            ? json_parse($bookingService->payload, true)
            : ($bookingService->payload ?? []);

        // 3. Regex to find all {{ variables }} (handles spaces like {{ key }} or {{key}})
        return preg_replace_callback('/\{\{\s*(.+?)\s*\}\}/', function ($matches) use ($payloadData) {
            $key = $matches[1];

            // If the data exists in the payload, return it.
            if (isset($payloadData[$key])) {
                $value = $payloadData[$key];

                // If it's an array (like multiple passengers), join them with commas
                if (is_array($value)) {
                    // Extract 'value' if it's a complex array object, otherwise just join the strings
                    $extracted = array_map(fn($v) => is_array($v) ? ($v['value'] ?? '') : $v, $value);
                    return implode(', ', array_filter($extracted));
                }

                return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }

            // If the key is missing from the payload, leave it blank to keep the PDF clean
            return '';
        }, $template);
    }

    // Placeholders for other document types
    private static function mapReceipt(Model $receipt): array { return []; }
    private static function mapQuote(Model $quote): array { return []; }
    private static function mapItinerary(Model $booking): array { return []; }
}
