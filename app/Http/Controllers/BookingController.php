<?php

// app/Http/Controllers/BookingController.php

namespace App\Http\Controllers;

use App\Actions\Travel\CreateBookingAction;
use App\Models\Booking;
use App\Models\Client;
use App\Models\Contract;
use App\Models\ServiceSchema;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BookingController extends Controller
{
    public function index()
    {
        $company = view()->shared('currentCompany');

        $bookings = Booking::query()
            ->with('client')
            ->where('company_id', $company->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('Bookings/Index', [
            'bookings' => $bookings,
        ]);
    }

    public function create()
    {
        $company = view()->shared('currentCompany');

        $schemas = ServiceSchema::query()
            ->where('industry', $company->industry)
            ->orderBy('display_name')
            ->get();

        $clients = Client::query()
            ->with('contracts')
            ->orderBy('name')
            ->get();

        return Inertia::render('Bookings/Create', [
            'schemas' => $schemas,
            'clients' => $clients,
        ]);
    }

    public function store(Request $request, CreateBookingAction $createBookingAction)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:control.clients,id',
            'contract_no' => 'required|string',
            'services' => 'required|array|min:1',
            'services.*.service_type' => 'required|string',
            'services.*.service_details' => 'nullable|array',
            'services.*.qty' => 'required|integer|min:1',
            'services.*.unit_fare' => 'required|numeric',
            'services.*.tax_type' => 'required|string|in:%,RM',
            'services.*.tax_value' => 'required|numeric',
            'services.*.client_price' => 'required|numeric',
            'passengers' => 'nullable|array',
            'passengers.*.full_name' => 'nullable|string|max:255',
            'passengers.*.passenger_type' => 'nullable|string|max:50',
            'passengers.*.passport_no' => 'nullable|string|max:100',
            'passengers.*.nationality' => 'nullable|string|max:100',
            'passengers.*.date_of_birth' => 'nullable|date',
            'passenger_details' => 'nullable|array',
        ]);

        $company = view()->shared('currentCompany');

        $booking = $createBookingAction->execute($validated, $company);

        return redirect()
            ->route('bookings.show', ['subdomain' => $company->subdomain, 'id' => $booking->id])
            ->with('success', 'Master Booking constructed. Ready for final assignment.');
    }

    public function show($subdomain, $id)
    {
        $company = view()->shared('currentCompany');

        $booking = Booking::query()
            ->with(['client', 'services.schema', 'passengers'])
            ->where('company_id', $company->id)
            ->findOrFail($id);

        $clients = Client::query()
            ->with('contracts')
            ->orderBy('name')
            ->get();

        return Inertia::render('Bookings/Show', [
            'booking' => $booking,
            'clients' => $clients,
        ]);
    }

    public function updateInvoice(Request $request, $subdomain, $id)
    {
        $company = view()->shared('currentCompany');

        $booking = Booking::query()
            ->where('company_id', $company->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'client_id' => 'required|exists:control.clients,id',
            'contract_no' => 'required|string',
            'passenger_details' => 'nullable|array',
        ]);

        $invoiceNo = $booking->invoice_no ?? 'INV-' . date('Ym') . '-' . str_pad($booking->id, 4, '0', STR_PAD_LEFT);

        $booking->update([
            'client_id' => $validated['client_id'],
            'contract_no' => $validated['contract_no'],
            'passenger_details' => $validated['passenger_details'] ?? [],
            'invoice_no' => $invoiceNo,
            'status' => 'Invoiced',
        ]);

        return back()->with('success', 'Invoice locked. Ready for PDF Generation.');
    }

    public function downloadInvoice($subdomain, $id)
    {
        $company = view()->shared('currentCompany');

        $booking = Booking::query()
            ->with(['client', 'services.schema', 'passengers'])
            ->where('company_id', $company->id)
            ->findOrFail($id);

        if (! $booking->invoice_no || ! $booking->client_id) {
            return back()->withErrors(['error' => 'Invoice parameters missing. Please lock the invoice first.']);
        }

        $client = Client::query()->find($booking->client_id);
        $contract = Contract::query()->where('contract_no', $booking->contract_no)->first();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice', [
            'booking' => $booking,
            'client' => $client,
            'contract' => $contract,
        ]);

        $fileName = "{$client->name} - {$booking->invoice_no}.pdf";

        return $pdf->download($fileName);
    }
}
