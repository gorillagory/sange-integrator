<?php

namespace App\Http\Controllers;

use App\Actions\ServiceRecords\CreateServiceRecordAction;
use App\Models\Client;
use App\Models\Contract;
use App\Models\DocumentTemplate;
use App\Models\SchemaVector;
use App\Models\ServiceRecord;
use App\Services\DocumentRenderContextFactory;
use App\Services\Handlers\HandlerRegistry;
use App\Services\PdfCompilerService;
use App\Services\ServiceRecordPayloadValidator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Throwable;

class ServiceRecordController extends Controller
{
    public function index()
    {
        $company = view()->shared('currentCompany');

        $serviceRecords = ServiceRecord::query()
            ->with('client')
            ->where('company_id', $company->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('ServiceRecords/Index', [
            'serviceRecords' => $serviceRecords,
        ]);
    }

    public function create(HandlerRegistry $handlers)
    {
        $company = view()->shared('currentCompany');
        $handler = $handlers->forCompany($company);

        $schemaVectors = SchemaVector::query()
            ->where('industry', $handler->industry() ?: $company->industry)
            ->orderBy('display_name')
            ->orderBy('version')
            ->get();

        $clients = Client::query()
            ->with('contracts')
            ->orderBy('name')
            ->get();

        return Inertia::render('ServiceRecords/Create', [
            'schemaVectors' => $schemaVectors,
            'clients' => $clients,
            'serviceGroup' => $handler->toArray(),
        ]);
    }

    public function store(
        Request $request,
        CreateServiceRecordAction $createServiceRecordAction,
        ServiceRecordPayloadValidator $payloadValidator,
        HandlerRegistry $handlers,
    ) {
        $company = view()->shared('currentCompany');
        $request->merge([
            'rows' => $request->input('rows', $request->input('services', [])),
            'service_group_key' => $request->input('service_group_key', $request->input('handler_key')),
        ]);

        $validated = $request->validate([
            'service_group_key' => ['nullable', 'string', Rule::in($handlers->keys())],
            'client_id' => 'required|exists:control.clients,id',
            'contract_no' => [
                'required',
                'string',
                Rule::exists('tenant.contracts', 'contract_no')->where(function ($query) use ($company, $request) {
                    $query
                        ->where('company_id', $company->id)
                        ->where('client_id', $request->input('client_id'));
                }),
            ],
            'rows' => 'required|array|min:1',
            'rows.*.schema_vector_id' => 'nullable|integer|exists:control.schema_vectors,id',
            'rows.*.service_code' => 'required|string',
            'rows.*.service_details' => 'nullable|array',
            'rows.*.service_details_extra' => 'nullable|array',
            'rows.*.qty' => 'required|integer|min:1',
            'rows.*.unit_name' => 'nullable|string|max:100',
            'rows.*.base_cost' => 'required|numeric|min:0',
            'rows.*.supplier_cost' => 'nullable|numeric|min:0',
            'rows.*.discount_type' => 'required|string|in:%,RM',
            'rows.*.discount_value' => 'required|numeric|min:0',
            'rows.*.tax_type' => 'required|string|in:%,RM',
            'rows.*.tax_value' => 'required|numeric|min:0',
            'rows.*.sell_price' => 'required|numeric|min:0',
        ]);

        $validated['service_group_key'] = $handlers->resolve($validated['service_group_key'] ?? null, $company)->key();
        $validated['rows'] = $payloadValidator->validateAndNormalize($validated['rows'], $company, $validated['service_group_key']);

        $serviceRecord = $createServiceRecordAction->execute($validated, $company);

        return redirect()
            ->route('service-records.show', ['subdomain' => $company->subdomain, 'id' => $serviceRecord->id])
            ->with('success', 'Service record captured. Review document routing when ready.');
    }

    public function show($subdomain, $id)
    {
        $company = view()->shared('currentCompany');

        $serviceRecord = ServiceRecord::query()
            ->with(['client', 'rows.schemaVector'])
            ->where('company_id', $company->id)
            ->findOrFail($id);

        $clients = Client::query()
            ->with('contracts')
            ->orderBy('name')
            ->get();

        return Inertia::render('ServiceRecords/Show', [
            'serviceRecord' => $serviceRecord,
            'clients' => $clients,
        ]);
    }

    public function updateDocument(Request $request, $subdomain, $id)
    {
        $company = view()->shared('currentCompany');

        $serviceRecord = ServiceRecord::query()
            ->where('company_id', $company->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'client_id' => 'required|exists:control.clients,id',
            'contract_no' => [
                'required',
                'string',
                Rule::exists('tenant.contracts', 'contract_no')->where(function ($query) use ($company, $request) {
                    $query
                        ->where('company_id', $company->id)
                        ->where('client_id', $request->input('client_id'));
                }),
            ],
        ]);

        $documentNo = $serviceRecord->document_no ?? 'DOC-' . date('Ym') . '-' . str_pad((string) $serviceRecord->id, 4, '0', STR_PAD_LEFT);

        $serviceRecord->update([
            'client_id' => $validated['client_id'],
            'contract_no' => $validated['contract_no'],
            'document_no' => $documentNo,
            'status' => 'DocumentLocked',
        ]);

        return back()->with('success', 'Document routing locked. Output is ready.');
    }

    public function downloadDocument(
        $subdomain,
        $id,
        DocumentRenderContextFactory $renderContextFactory,
        PdfCompilerService $compiler,
    ) {
        $company = view()->shared('currentCompany');

        $serviceRecord = ServiceRecord::query()
            ->with(['client', 'company.mainGroupCompany', 'rows.schemaVector'])
            ->where('company_id', $company->id)
            ->findOrFail($id);

        if (! $serviceRecord->document_no || ! $serviceRecord->client_id) {
            return back()->withErrors(['error' => 'Document parameters are missing. Please lock routing first.']);
        }

        $client = Client::query()->find($serviceRecord->client_id);
        $contract = Contract::query()
            ->where('company_id', $company->id)
            ->where('client_id', $serviceRecord->client_id)
            ->where('contract_no', $serviceRecord->contract_no)
            ->first();

        if (! $client || ! $contract) {
            return back()->withErrors([
                'error' => 'The locked document references a missing or invalid client/contract pairing.',
            ]);
        }

        $fileName = "{$client->name} - {$serviceRecord->document_no}.pdf";

        $template = DocumentTemplate::query()
            ->where('document_type', 'invoice')
            ->orderByDesc('updated_at')
            ->first();

        if (! $template) {
            return back()->withErrors([
                'error' => 'No invoice document template found. Please create an invoice template in Admin Documents.',
            ]);
        }

        try {
            $payload = $renderContextFactory->makeInvoiceFromServiceRecord($serviceRecord, $serviceRecord->company, $contract);
            $html = $compiler->compileToHtml($template, $payload);
            $page = $template->layout_vector['page'] ?? [];
            $size = strtolower((string) ($page['size'] ?? 'a4'));
            $orientation = (string) ($page['orientation'] ?? 'portrait');

            $pdf = Pdf::loadHTML($html);
            $pdf->setPaper($size, $orientation);

            return $pdf->download($fileName);
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors([
                'error' => 'Document generation failed from the template pipeline. Please review template bindings and try again.',
            ]);
        }
    }
}
