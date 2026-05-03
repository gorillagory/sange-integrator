<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentTemplate;
use App\Services\DocumentVariableService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\PdfCompilerService;
use Barryvdh\DomPDF\Facade\Pdf;

class DocumentTemplateController extends Controller
{
    public function index()
    {
        // Add $subdomain parameter to method signature if your routes require it
        return Inertia::render('Admin/Documents/Index', [
            'templates' => DocumentTemplate::orderBy('name')->get()
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Documents/Builder', [
            // Pass all dictionaries to Vue so we don't need to make AJAX calls when changing types
            'dictionaries' => $this->getAllDictionaries()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:document_templates,code',
            'document_type' => 'required|string',
            'layout_vector' => 'required|array'
        ]);

        DocumentTemplate::create($validated);

        return redirect()->route('admin.documents.index')->with('success', 'Template Created Successfully');
    }

    public function edit($subdomain, $id)
    {
        return Inertia::render('Admin/Documents/Builder', [
            'template' => DocumentTemplate::findOrFail($id),
            'dictionaries' => $this->getAllDictionaries()
        ]);
    }

    public function update(Request $request, $subdomain, $id)
    {
        $template = DocumentTemplate::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:document_templates,code,' . $id,
            'document_type' => 'required|string',
            'layout_vector' => 'required|array'
        ]);

        $template->update($validated);

        // Use back() so the Vue page doesn't reload and lose the user's zoom/preview state
        return back()->with('success', 'Vector Synced Successfully');
    }

    public function destroy($subdomain, $id)
    {
        DocumentTemplate::findOrFail($id)->delete();
        return back()->with('success', 'Template Shattered');
    }

    /**
     * Helper to bundle all dictionaries at once for the Vue frontend
     */
    private function getAllDictionaries(): array
    {
        return [
            'invoice' => DocumentVariableService::getDictionary('invoice'),
            'receipt' => DocumentVariableService::getDictionary('receipt'),
            'quote' => DocumentVariableService::getDictionary('quote'),
            'itinerary' => DocumentVariableService::getDictionary('itinerary'),
        ];
    }

    /**
     * Injects mock data into the template and streams a physical PDF to the browser.
     */
    public function preview($subdomain, $id, PdfCompilerService $compiler)
    {
        $template = DocumentTemplate::findOrFail($id);

        // 1. Generate Mock Data tailored to the exact keys in your JSON Vector
        $mockData = [
            'client_name' => 'Acme Corporation Sdn Bhd',
            'client_address_1' => 'Suite 101, Innovation Tower',
            'client_address_2' => 'Jalan Technology, Cyberjaya',
            'client_address_3' => '63000 Selangor, Malaysia',
            'client_referral' => 'Ref: John Doe (Sales)',

            'invoice_number' => 'INV-' . date('Y') . '-00912',
            'invoice_date' => date('d M Y'),
            'invoice_month' => date('F Y'),
            'invoice_personnel' => 'Sarah Jane',
            'invoice_term' => 'Net 30 Days',
            'contract_title' => 'MASTER SERVICES AGREEMENT',

            // This maps exactly to your "items" Data Table loop!
            'items' => [
                ['name' => 'Roundtrip Flight (KUL - NRT)', 'total' => 'RM 2,500.00'],
                ['name' => 'Hotel Accommodation (3 Nights)', 'total' => 'RM 1,200.00'],
                ['name' => 'Private Airport Transfer', 'total' => 'RM 150.00'],
                ['name' => 'Tourism Tax & Fees', 'total' => 'RM 50.00'],
            ],

            // This maps to your Bulleted List loop!
            'list_items' => [
                'All payments are non-refundable after 14 days.',
                'Please quote the Invoice Number on your bank transfer.',
                'Make checks payable to BAYAM TRAVEL SDN BHD.'
            ]
        ];

        // 2. Pass the JSON Vector and the Mock Data to our Compiler
        $html = $compiler->compileToHtml($template, $mockData);

        // 3. Generate the physical PDF
        $pdf = Pdf::loadHTML($html);

        // Optional: Set paper size based on template settings
        $size = $template->layout_vector['page']['size'] ?? 'A4';
        $orientation = $template->layout_vector['page']['orientation'] ?? 'portrait';
        $pdf->setPaper(strtolower($size), $orientation);

        // 4. Stream directly to the browser in a new tab
        return $pdf->stream($template->code . '_preview.pdf', ['Attachment' => false]);
    }
}
