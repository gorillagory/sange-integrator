<?php

namespace App\Http\Controllers;

use App\Actions\ServiceRecords\CreateServiceRecordAction;
use App\Actions\ServiceRecords\UpdateServiceRecordDraftAction;
use App\Models\Client;
use App\Models\Contract;
use App\Models\DocumentTemplate;
use App\Models\SchemaVector;
use App\Models\ServiceRecord;
use App\Services\DocumentRenderContextFactory;
use App\Services\Handlers\HandlerRegistry;
use App\Services\PdfCompilerService;
use App\Services\ServiceRecordAuditService;
use App\Services\ServiceRecordPayloadValidator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Throwable;
use Spatie\Permission\PermissionRegistrar;

class ServiceRecordController extends Controller
{
    public function index(Request $request)
    {
        $company = view()->shared('currentCompany');

        $availableClientIds = Contract::query()
            ->where('company_id', $company->id)
            ->select('client_id')
            ->distinct()
            ->pluck('client_id');

        $filters = [
            'search' => trim((string) $request->input('search', '')),
            'client_id' => (string) $request->input('client_id', 'all'),
            'status' => (string) $request->input('status', 'all'),
        ];

        if ($filters['client_id'] !== 'all' && ! $availableClientIds->contains((int) $filters['client_id'])) {
            $filters['client_id'] = 'all';
        }

        $clientDirectory = $this->tenantClients($company, false);
        $matchingClientIds = $this->matchingClientIds($availableClientIds, $filters['search']);

        $baseQuery = ServiceRecord::query()
            ->where('company_id', $company->id);

        $this->applyIndexFilters($baseQuery, $filters, $matchingClientIds, false);

        $statusCounts = (clone $baseQuery)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $statusOptions = (clone $baseQuery)
            ->select('status')
            ->distinct()
            ->orderBy('status')
            ->pluck('status')
            ->filter()
            ->values();

        $metrics = [
            'records' => (clone $baseQuery)->count(),
            'total_value' => (float) ((clone $baseQuery)->sum('total_amount') ?? 0),
            'locked' => (clone $baseQuery)->where('status', 'DocumentLocked')->count(),
            'draft' => (clone $baseQuery)->where('status', 'Draft')->count(),
            'active_clients' => (clone $baseQuery)->whereNotNull('client_id')->distinct('client_id')->count('client_id'),
        ];

        $serviceRecordsQuery = ServiceRecord::query()
            ->with('client')
            ->withCount('rows')
            ->where('company_id', $company->id);

        $this->applyIndexFilters($serviceRecordsQuery, $filters, $matchingClientIds);

        $serviceRecords = $serviceRecordsQuery
            ->orderBy('created_at', 'desc')
            ->paginate(12)
            ->withQueryString();

        $clientCounts = ServiceRecord::query()
            ->where('company_id', $company->id)
            ->whereNotNull('client_id')
            ->selectRaw('client_id, COUNT(*) as record_count, COALESCE(SUM(total_amount), 0) as total_value')
            ->groupBy('client_id')
            ->get()
            ->keyBy('client_id');

        $clients = $clientDirectory->map(function (Client $client) use ($clientCounts) {
            $summary = $clientCounts->get($client->id);

            return [
                'id' => $client->id,
                'name' => $client->name,
                'logo_path' => $client->logo_path,
                'record_count' => (int) ($summary->record_count ?? 0),
                'total_value' => (float) ($summary->total_value ?? 0),
            ];
        })->values();

        return Inertia::render('ServiceRecords/Index', [
            'serviceRecords' => $serviceRecords,
            'clients' => $clients,
            'filters' => $filters,
            'metrics' => $metrics,
            'statusCounts' => [
                'all' => $metrics['records'],
                'draft' => (int) ($statusCounts['Draft'] ?? 0),
                'locked' => (int) ($statusCounts['DocumentLocked'] ?? 0),
                'other' => collect($statusCounts)
                    ->except(['Draft', 'DocumentLocked'])
                    ->sum(),
            ],
            'statusOptions' => $statusOptions,
        ]);
    }

    public function create(HandlerRegistry $handlers)
    {
        $company = view()->shared('currentCompany');
        $handler = $handlers->forCompany($company);

        return Inertia::render('ServiceRecords/Create', [
            'schemaVectors' => $this->schemaVectorsForHandler($company, $handler->industry() ?: $company->industry, $handler->key()),
            'clients' => $this->tenantClients($company),
            'serviceGroup' => $handler->toArray(),
            'mode' => 'create',
        ]);
    }

    public function store(
        Request $request,
        CreateServiceRecordAction $createServiceRecordAction,
        ServiceRecordPayloadValidator $payloadValidator,
        HandlerRegistry $handlers,
        ServiceRecordAuditService $auditService,
    ) {
        $company = view()->shared('currentCompany');
        $request->merge([
            'rows' => $request->input('rows', $request->input('services', [])),
            'service_group_key' => $request->input('service_group_key', $request->input('handler_key')),
        ]);

        $validated = $this->validateDraftPayload($request, $company, $handlers);

        $validated['service_group_key'] = $handlers->resolve($validated['service_group_key'] ?? null, $company)->key();
        $validated['rows'] = $payloadValidator->validateAndNormalize($validated['rows'], $company, $validated['service_group_key']);

        $serviceRecord = $createServiceRecordAction->execute($validated, $company);
        $auditService->logCreated($serviceRecord);

        return redirect()
            ->route('service-records.show', ['subdomain' => $company->subdomain, 'id' => $serviceRecord->id])
            ->with('success', 'Service record captured. Review document routing when ready.');
    }

    public function edit($subdomain, $id, HandlerRegistry $handlers)
    {
        $company = view()->shared('currentCompany');

        $serviceRecord = ServiceRecord::query()
            ->with(['client', 'clientRemarkPreset', 'rows.schemaVector'])
            ->where('company_id', $company->id)
            ->findOrFail($id);

        if ($serviceRecord->status !== 'Draft') {
            return redirect()
                ->route('service-records.show', ['subdomain' => $company->subdomain, 'id' => $serviceRecord->id])
                ->with('warning', 'Return the document to Draft before editing its captured payload.');
        }

        $handler = $handlers->resolve($serviceRecord->service_group_key, $company);

        return Inertia::render('ServiceRecords/Create', [
            'schemaVectors' => $this->schemaVectorsForHandler($company, $handler->industry() ?: $company->industry, $handler->key()),
            'clients' => $this->tenantClients($company),
            'serviceGroup' => $handler->toArray(),
            'serviceRecord' => $serviceRecord,
            'mode' => 'edit',
        ]);
    }

    public function update(
        Request $request,
        $subdomain,
        $id,
        UpdateServiceRecordDraftAction $updateServiceRecordDraftAction,
        ServiceRecordPayloadValidator $payloadValidator,
        HandlerRegistry $handlers,
        ServiceRecordAuditService $auditService,
    ) {
        $company = view()->shared('currentCompany');
        $request->merge([
            'rows' => $request->input('rows', $request->input('services', [])),
            'service_group_key' => $request->input('service_group_key', $request->input('handler_key')),
        ]);

        $serviceRecord = ServiceRecord::query()
            ->with(['client', 'clientRemarkPreset', 'rows.schemaVector'])
            ->where('company_id', $company->id)
            ->findOrFail($id);

        if ($serviceRecord->status !== 'Draft') {
            return redirect()
                ->route('service-records.show', ['subdomain' => $company->subdomain, 'id' => $serviceRecord->id])
                ->with('warning', 'Locked documents must be returned to Draft before editing.');
        }

        $before = clone $serviceRecord;
        $before->setRelation('client', $serviceRecord->client);
        $before->setRelation('clientRemarkPreset', $serviceRecord->clientRemarkPreset);
        $before->setRelation('rows', $serviceRecord->rows);

        $validated = $this->validateDraftPayload($request, $company, $handlers);
        $validated['service_group_key'] = $handlers->resolve($validated['service_group_key'] ?? null, $company)->key();
        $validated['rows'] = $payloadValidator->validateAndNormalize($validated['rows'], $company, $validated['service_group_key']);

        $serviceRecord = $updateServiceRecordDraftAction->execute($serviceRecord, $validated, $company);
        $auditService->logUpdated($before, $serviceRecord);

        return redirect()
            ->route('service-records.show', ['subdomain' => $company->subdomain, 'id' => $serviceRecord->id])
            ->with('success', 'Service record draft updated.');
    }

    public function show($subdomain, $id, Request $request, ServiceRecordAuditService $auditService)
    {
        $company = view()->shared('currentCompany');

        $serviceRecord = ServiceRecord::query()
            ->with(['client', 'clientRemarkPreset', 'rows.schemaVector'])
            ->where('company_id', $company->id)
            ->findOrFail($id);

        $clients = $this->tenantClients($company);

        return Inertia::render('ServiceRecords/Show', [
            'serviceRecord' => $serviceRecord,
            'clients' => $clients,
            'timeline' => $auditService->timelineFor($serviceRecord, $company),
            'capabilities' => $this->serviceRecordCapabilities($request, $company->id),
            'statusAuthority' => $this->statusAuthorityMatrix(),
        ]);
    }

    public function updateDocument(
        Request $request,
        $subdomain,
        $id,
        ServiceRecordAuditService $auditService,
    )
    {
        $company = view()->shared('currentCompany');

        $serviceRecord = ServiceRecord::query()
            ->with(['client', 'clientRemarkPreset', 'rows.schemaVector'])
            ->where('company_id', $company->id)
            ->findOrFail($id);

        $action = (string) $request->input('action', 'lock');
        $before = clone $serviceRecord;
        $before->setRelation('client', $serviceRecord->client);
        $before->setRelation('clientRemarkPreset', $serviceRecord->clientRemarkPreset);
        $before->setRelation('rows', $serviceRecord->rows);

        if ($action === 'unlock') {
            $serviceRecord->update([
                'status' => 'Draft',
            ]);

            $serviceRecord = $serviceRecord->fresh(['client', 'clientRemarkPreset', 'rows.schemaVector']);
            $auditService->logStatusChanged($before, $serviceRecord, 'unlock');

            return back()->with('success', 'Document returned to Draft so it can be edited again.');
        }

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

        $serviceRecord = $serviceRecord->fresh(['client', 'clientRemarkPreset', 'rows.schemaVector']);
        $auditService->logStatusChanged($before, $serviceRecord, 'lock');

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

    private function tenantClients(mixed $company, bool $includeContext = true)
    {
        $clientIds = Contract::query()
            ->where('company_id', $company->id)
            ->select('client_id')
            ->distinct()
            ->pluck('client_id');

        $query = Client::query()
            ->whereIn('id', $clientIds)
            ->orderBy('name');

        if ($includeContext) {
            $query->with([
                'contracts' => fn ($query) => $query
                    ->where('company_id', $company->id)
                    ->orderBy('contract_no'),
                'remarkPresets' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderBy('title'),
            ]);
        }

        return $query->get();
    }

    private function validateDraftPayload(Request $request, mixed $company, HandlerRegistry $handlers): array
    {
        return $request->validate([
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
            'client_remark_preset_id' => [
                'nullable',
                'integer',
                Rule::exists('control.client_remark_presets', 'id')->where(function ($query) use ($request) {
                    $query->where('client_id', $request->input('client_id'));
                }),
            ],
            'remarks' => 'nullable|string|max:5000',
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
    }

    private function applyIndexFilters(mixed $query, array $filters, mixed $matchingClientIds, bool $applyStatus = true): void
    {
        if ($filters['client_id'] !== 'all') {
            $query->where('client_id', (int) $filters['client_id']);
        }

        if ($filters['search'] !== '') {
            $query->where(function ($builder) use ($filters, $matchingClientIds) {
                $builder
                    ->where('reference_no', 'ilike', "%{$filters['search']}%")
                    ->orWhere('invoice_no', 'ilike', "%{$filters['search']}%")
                    ->orWhere('contract_no', 'ilike', "%{$filters['search']}%")
                    ->orWhere('remarks', 'ilike', "%{$filters['search']}%")
                    ->orWhere('status', 'ilike', "%{$filters['search']}%");

                if ($matchingClientIds->isNotEmpty()) {
                    $builder->orWhereIn('client_id', $matchingClientIds);
                }
            });
        }

        if ($applyStatus && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }
    }

    private function matchingClientIds(mixed $availableClientIds, string $search)
    {
        if ($search === '' || $availableClientIds->isEmpty()) {
            return collect();
        }

        return Client::query()
            ->whereIn('id', $availableClientIds)
            ->where('name', 'ilike', "%{$search}%")
            ->pluck('id');
    }

    private function serviceRecordCapabilities(Request $request, int $companyId): array
    {
        return [
            'can_edit_draft' => $this->userHasAnyTenantRole($request, $companyId, ['agency_admin', 'booking_manager', 'travel_agent']),
            'can_manage_document_status' => $this->userHasAnyTenantRole($request, $companyId, ['agency_admin', 'document_manager']),
        ];
    }

    private function userHasAnyTenantRole(Request $request, int $companyId, array $roles): bool
    {
        $user = $request->user();

        if (! $user) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        app(PermissionRegistrar::class)->setPermissionsTeamId($companyId);

        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    private function statusAuthorityMatrix(): array
    {
        return [
            [
                'status' => 'Draft',
                'label' => 'Draft Working State',
                'roles' => ['Super Admin', 'Agency Admin', 'Booking Manager', 'Travel Agent'],
                'description' => 'These roles can create and revise draft service records before document routing is locked.',
            ],
            [
                'status' => 'DocumentLocked',
                'label' => 'Document Routing Locked',
                'roles' => ['Super Admin', 'Agency Admin', 'Document Manager'],
                'description' => 'These roles can lock routing for output and return the document to Draft if a revision is required.',
            ],
        ];
    }

    private function schemaVectorsForHandler(mixed $company, ?string $industry, string $handlerKey)
    {
        return SchemaVector::query()
            ->where('industry', $industry ?: $company->industry)
            ->where(function ($query) use ($handlerKey) {
                $query->where('service_group_key', $handlerKey)
                    ->orWhereNull('service_group_key')
                    ->orWhere('service_group_key', '');
            })
            ->orderBy('display_name')
            ->orderBy('version')
            ->get();
    }
}
