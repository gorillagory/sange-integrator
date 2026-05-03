<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\ServiceSchema;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BookingController extends Controller
{
    public function index()
    {
        $bookings = Booking::with('client')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('Bookings/Index', [
            'bookings' => $bookings
        ]);
    }

    public function create()
    {
        $company = view()->shared('currentCompany');

        $schemas = \App\Models\ServiceSchema::where('industry', $company->industry)
            ->orderBy('display_name')
            ->get();

        $clients = \App\Models\Client::with('contracts')->orderBy('name')->get();

        return Inertia::render('Bookings/Create', [
            'schemas' => $schemas,
            'clients' => $clients
        ]);
    }

    public function store(Request $request)
    {
        // 🛡️ Explicitly define rules for the new Agency Pricing Flow
        $validated = $request->validate([
            'client_id' => 'required|exists:control.clients,id',
            'contract_no' => 'required|string',
            'services' => 'required|array|min:1',
            'services.*.service_type' => 'required|string',
            'services.*.service_details' => 'nullable|array', // Allows nested dynamic schema data
            'services.*.qty' => 'required|integer|min:1',
            'services.*.unit_fare' => 'required|numeric', // Supplier Cost
            'services.*.tax_type' => 'required|string|in:%,RM',
            'services.*.tax_value' => 'required|numeric',
            'services.*.client_price' => 'required|numeric', // Total Charged to Client (per unit)
        ]);

        $refNo = 'BKG-' . date('Ym') . '-' . strtoupper(\Illuminate\Support\Str::random(5));
        $company = view()->shared('currentCompany');
        $schemas = \App\Models\ServiceSchema::where('industry', $company->industry)->get()->keyBy('service_type');

        $totalAmount = 0;
        $cartPayload = [];

        foreach ($validated['services'] as $item) {
            $qty = intval($item['qty'] ?? 1);
            $base = floatval($item['unit_fare'] ?? 0);

            // Tax Engine
            $taxType = $item['tax_type'] ?? 'RM';
            $taxAmount = $taxType === '%'
                ? $base * (floatval($item['tax_value'] ?? 0) / 100)
                : floatval($item['tax_value'] ?? 0);

            // 🧮 Auto-Calculate Profit Margin
            $clientPrice = floatval($item['client_price'] ?? 0);
            $markupAmount = $clientPrice - $base - $taxAmount; // Profit = Client Price - Cost - Tax

            $lineTotal = $clientPrice * $qty;
            $totalAmount += $lineTotal;

            // 📎 Process Dynamic Details & Files
            $processedDetails = [];
            if (!empty($item['service_details'])) {
                foreach ($item['service_details'] as $key => $value) {
                    if ($value instanceof \Illuminate\Http\UploadedFile) {
                        $path = $value->store('booking-attachments', 'public');
                        $processedDetails[$key] = '/storage/' . $path;
                    } else {
                        $processedDetails[$key] = $value;
                    }
                }
            }

            $cartPayload[] = [
                'service_type' => $item['service_type'],
                'service_name' => $schemas[$item['service_type']]->display_name ?? 'Service',
                'details' => $processedDetails,
                'base_fare' => $base,
                'tax_type' => $taxType,
                'tax_value' => floatval($item['tax_value'] ?? 0),
                'tax' => $taxAmount,
                'markup_type' => 'RM', // Hardcoded since we derive it from final client price
                'markup_value' => $markupAmount,
                'markup' => $markupAmount,
                'price' => $clientPrice,
                'qty' => $qty
            ];
        }

        $booking = \App\Models\Booking::create([
            'reference_no' => $refNo,
            'client_id' => $validated['client_id'],
            'contract_no' => $validated['contract_no'],
            'cart_payload' => $cartPayload,
            'total_amount' => $totalAmount,
            'status' => 'Draft'
        ]);

        $bookingId = $booking->id ?? \App\Models\Booking::where('reference_no', $refNo)->value('id');

        return redirect()->route('bookings.show', ['id' => $bookingId])
            ->with('success', 'Master Booking constructed. Ready for final assignment.');
    }

    public function show($subdomain, $id)
    {
        $booking = \App\Models\Booking::with('client')->findOrFail($id);
        $clients = \App\Models\Client::with('contracts')->orderBy('name')->get();

        return \Inertia\Inertia::render('Bookings/Show', [
            'booking' => $booking,
            'clients' => $clients
        ]);
    }

    public function updateInvoice(Request $request, $subdomain, $id)
    {
        $booking = \App\Models\Booking::findOrFail($id);

        $validated = $request->validate([
            'client_id' => 'required|exists:control.clients,id',
            'contract_no' => 'required|string',
            'passenger_details' => 'nullable|array'
        ]);

        $invoiceNo = $booking->invoice_no ?? 'INV-' . date('Ym') . '-' . str_pad($booking->id, 4, '0', STR_PAD_LEFT);

        $booking->update([
            'client_id' => $validated['client_id'],
            'contract_no' => $validated['contract_no'],
            'passenger_details' => $validated['passenger_details'] ?? [],
            'invoice_no' => $invoiceNo,
            'status' => 'Invoiced'
        ]);

        return back()->with('success', 'Invoice locked. Ready for PDF Generation.');
    }

    public function downloadInvoice($subdomain, $id)
    {
        $booking = \App\Models\Booking::findOrFail($id);

        if (!$booking->invoice_no || !$booking->client_id) {
            return back()->withErrors(['error' => 'Invoice parameters missing. Please lock the invoice first.']);
        }

        $client = \App\Models\Client::find($booking->client_id);
        $contract = \App\Models\Contract::where('contract_no', $booking->contract_no)->first();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice', [
            'booking' => $booking,
            'client' => $client,
            'contract' => $contract
        ]);

        $fileName = "{$client->name} - {$booking->invoice_no}.pdf";
        return $pdf->download($fileName);
    }
}
