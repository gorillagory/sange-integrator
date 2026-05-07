<?php

// app/Actions/Travel/CreateBookingAction.php

namespace App\Actions\Travel;

use App\Models\Booking;
use App\Models\BookingService;
use App\Models\Company;
use App\Models\Passenger;
use App\Models\ServiceSchema;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateBookingAction
{
    public function execute(array $validated, Company $company): Booking
    {
        $schemas = ServiceSchema::query()
            ->where('industry', $company->industry)
            ->get()
            ->keyBy('service_type');

        return DB::connection('tenant')->transaction(function () use ($validated, $company, $schemas) {
            $referenceNo = 'BKG-' . date('Ym') . '-' . strtoupper(Str::random(5));
            $totalAmount = 0;
            $cartPayload = [];

            $booking = Booking::query()->create([
                'company_id' => $company->id,
                'reference_no' => $referenceNo,
                'client_id' => $validated['client_id'],
                'contract_no' => $validated['contract_no'],
                'cart_payload' => [],
                'passenger_details' => $validated['passenger_details'] ?? [],
                'total_amount' => 0,
                'status' => 'Draft',
            ]);

            foreach ($validated['services'] as $index => $item) {
                $qty = (int) ($item['qty'] ?? 1);
                $base = (float) ($item['unit_fare'] ?? 0);
                $taxType = $item['tax_type'] ?? 'RM';
                $taxValue = (float) ($item['tax_value'] ?? 0);

                $taxAmount = $taxType === '%'
                    ? $base * ($taxValue / 100)
                    : $taxValue;

                $clientPrice = (float) ($item['client_price'] ?? 0);
                $lineTotal = $clientPrice * $qty;
                $totalAmount += $lineTotal;

                $schema = $schemas[$item['service_type']] ?? null;
                $details = $item['service_details'] ?? [];

                BookingService::query()->create([
                    'booking_id' => $booking->id,
                    'company_id' => $company->id,
                    'service_schema_id' => $schema?->id,
                    'service_type' => $item['service_type'],
                    'service_name' => $schema?->display_name ?? 'Service',
                    'service_details' => $details,
                    'qty' => $qty,
                    'unit_fare' => $base,
                    'tax_type' => $taxType,
                    'tax_value' => $taxValue,
                    'tax_amount' => $taxAmount,
                    'client_price' => $clientPrice,
                    'line_total' => $lineTotal,
                    'sort_order' => $index,
                    'payload' => $details,
                ]);

                $cartPayload[] = [
                    'service_type' => $item['service_type'],
                    'service_name' => $schema?->display_name ?? 'Service',
                    'details' => $details,
                    'base_fare' => $base,
                    'tax_type' => $taxType,
                    'tax_value' => $taxValue,
                    'tax' => $taxAmount,
                    'price' => $clientPrice,
                    'qty' => $qty,
                    'line_total' => $lineTotal,
                ];
            }

            foreach (($validated['passengers'] ?? []) as $passenger) {
                Passenger::query()->create([
                    'booking_id' => $booking->id,
                    'company_id' => $company->id,
                    'full_name' => $passenger['full_name'] ?? null,
                    'passenger_type' => $passenger['passenger_type'] ?? null,
                    'passport_no' => $passenger['passport_no'] ?? null,
                    'nationality' => $passenger['nationality'] ?? null,
                    'date_of_birth' => Arr::get($passenger, 'date_of_birth'),
                    'meta' => Arr::except($passenger, [
                        'full_name',
                        'passenger_type',
                        'passport_no',
                        'nationality',
                        'date_of_birth',
                    ]),
                ]);
            }

            $booking->update([
                'cart_payload' => $cartPayload,
                'total_amount' => $totalAmount,
            ]);

            return $booking->fresh(['client', 'services.schema', 'passengers']);
        });
    }
}
