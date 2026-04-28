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
        // Fetch bookings, load the client relationship, order by newest first
        $bookings = Booking::with('client')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('Bookings/Index', [
            'bookings' => $bookings
        ]);
    }
    /**
     * Show the Shape-Shifter Booking Form.
     */
    public function create()
    {
        $company = view()->shared('currentCompany');

        // 🛠️ FIXED: Removed the 'is_active' filter to match your actual database schema
        $schemas = \App\Models\ServiceSchema::where('industry', $company->industry)
            ->orderBy('display_name')
            ->get();

        // 🌐 Fetch Global Clients & Local Contracts for the Autocomplete Engine
        $clients = \App\Models\Client::with('contracts')->orderBy('name')->get();

        return Inertia::render('Bookings/Create', [
            'schemas' => $schemas,
            'clients' => $clients
        ]);
    }

    public function store(Request $request)
    {
        // 🛡️ THE FIX: Explicitly validate EVERY nested key so Laravel doesn't strip them!
        $validated = $request->validate([
            'client_id' => 'required|exists:control.clients,id',
            'contract_no' => 'required|string',
            'services' => 'required|array|min:1',
            'services.*.service_type' => 'required|string',
            'services.*.service_details' => 'nullable|array',
            'services.*.unit_fare' => 'nullable|numeric',
            'services.*.tax_type' => 'nullable|string|in:%,RM',
            'services.*.tax_value' => 'nullable|numeric',
            'services.*.markup_type' => 'nullable|string|in:%,RM',
            'services.*.markup_value' => 'nullable|numeric',
            'services.*.qty' => 'required|integer|min:1',
        ]);

        $refNo = 'BKG-' . date('Ym') . '-' . strtoupper(\Illuminate\Support\Str::random(5));
        $company = view()->shared('currentCompany');
        $schemas = \App\Models\ServiceSchema::where('industry', $company->industry)->get()->keyBy('service_type');

        $totalAmount = 0;
        $cartPayload = [];

        foreach ($validated['services'] as $item) {
            $base = floatval($item['unit_fare'] ?? 0);
            $qty = intval($item['qty'] ?? 1);

            // 🧮 SAFE HYBRID PRICING CALCULATOR
            $taxType = $item['tax_type'] ?? '%';
            $taxAmount = $taxType === '%'
                ? $base * (floatval($item['tax_value'] ?? 0) / 100)
                : floatval($item['tax_value'] ?? 0);

            $markupType = $item['markup_type'] ?? 'RM';
            $markupAmount = $markupType === '%'
                ? $base * (floatval($item['markup_value'] ?? 0) / 100)
                : floatval($item['markup_value'] ?? 0);

            $linePrice = $base + $taxAmount + $markupAmount;
            $lineTotal = $linePrice * $qty;
            $totalAmount += $lineTotal;

            // 📎 DYNAMIC ATTACHMENT HANDLER
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
                'markup_type' => $markupType,
                'markup_value' => floatval($item['markup_value'] ?? 0),
                'markup' => $markupAmount,
                'price' => $linePrice,
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

        // 🛡️ BULLETPROOF TENANT ID CAPTURE
        $bookingId = $booking->id ?? \App\Models\Booking::where('reference_no', $refNo)->value('id');

        return redirect()->route('bookings.show', ['id' => $bookingId])
            ->with('success', 'Master Booking constructed. Ready for final assignment.');
    }
// 🛠️ FIX: Added $subdomain as the first parameter to absorb the route domain
    public function show($subdomain, $id)
    {
        $booking = \App\Models\Booking::with('client')->findOrFail($id);
        $clients = \App\Models\Client::with('contracts')->orderBy('name')->get();

        return \Inertia\Inertia::render('Bookings/Show', [
            'booking' => $booking,
            'clients' => $clients
        ]);
    }

    // 🛠️ FIX: Added $subdomain right after the Request object
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

    // 🛠️ FIX: Added $subdomain as the first parameter
    public function downloadInvoice($subdomain, $id)
    {
        $booking = \App\Models\Booking::findOrFail($id);

        // 🛡️ Security Check: Ensure invoice was actually generated
        if (!$booking->invoice_no || !$booking->client_id) {
            return back()->withErrors(['error' => 'Invoice parameters missing. Please lock the invoice first.']);
        }

        // 🌐 Fetch the Global Client from the Control DB
        $client = \App\Models\Client::find($booking->client_id);

        // 🏢 Fetch the specific Local Contract
        $contract = \App\Models\Contract::where('contract_no', $booking->contract_no)->first();

        // 📄 Generate the PDF using a Blade view
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice', [
            'booking' => $booking,
            'client' => $client,
            'contract' => $contract
        ]);

        $fileName = "{$client->name} - {$booking->invoice_no}.pdf";
        return $pdf->download($fileName);
    }
}
