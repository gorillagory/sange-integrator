<?php

// app/Http/Controllers/Admin/SchemaController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceSchema;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class SchemaController extends Controller
{
    public function index($subdomain)
    {
        $company = view()->shared('currentCompany');

        $schemas = ServiceSchema::query()
            ->where('industry', $company->industry)
            ->orderBy('display_name')
            ->get();

        return Inertia::render('Admin/Schemas/Index', [
            'schemas' => $schemas,
        ]);
    }

    public function create($subdomain)
    {
        $company = view()->shared('currentCompany');

        return Inertia::render('Admin/Schemas/Builder', [
            'industry' => $company->industry,
        ]);
    }

    public function store(Request $request, $subdomain)
    {
        $company = view()->shared('currentCompany');
        $request->merge($this->canonicalizeSchemaInput($request));

        $validated = $request->validate([
            'display_name' => ['nullable', 'string', 'max:255', 'required_without:service_name'],
            'service_name' => ['nullable', 'string', 'max:255', 'required_without:display_name'],
            'service_type' => ['nullable', 'string', 'max:100'],
            'service_code' => [
                'required',
                'string',
                'max:120',
                'regex:/^[a-z0-9]+([._-][a-z0-9]+)*$/',
                Rule::unique('control.schema_vectors', 'service_code')
                    ->where(fn ($query) => $query
                        ->where('industry', $company->industry)
                        ->where('version', (int) $request->input('version', 1))),
            ],
            'version' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', Rule::in(['draft', 'active', 'deprecated', 'archived'])],
            'is_default' => ['nullable', 'boolean'],
            'industry' => ['required', 'string', 'max:100', Rule::in([$company->industry])],
            'schema_payload' => ['required', 'array'],
            'schema_payload.fields' => ['required', 'array'],
        ]);

        $payload = $validated;
        $this->enforceSingleActiveDefault($payload);

        ServiceSchema::query()->create($payload);

        return redirect()->route('admin.schemas.index', [
            'subdomain' => $subdomain,
        ])->with('success', 'Schema Vector successfully deployed to Production.');
    }

    public function edit($subdomain, $id)
    {
        $company = view()->shared('currentCompany');

        $schema = ServiceSchema::query()
            ->where('industry', $company->industry)
            ->findOrFail($id);

        return Inertia::render('Admin/Schemas/Builder', [
            'schema' => $schema,
            'industry' => $company->industry,
        ]);
    }

    public function update(Request $request, $subdomain, $id)
    {
        $company = view()->shared('currentCompany');

        $schema = ServiceSchema::query()
            ->where('industry', $company->industry)
            ->findOrFail($id);
        $request->merge($this->canonicalizeSchemaInput($request, $schema));

        $validated = $request->validate([
            'display_name' => ['nullable', 'string', 'max:255', 'required_without:service_name'],
            'service_name' => ['nullable', 'string', 'max:255', 'required_without:display_name'],
            'service_type' => ['nullable', 'string', 'max:100'],
            'service_code' => [
                'required',
                'string',
                'max:120',
                'regex:/^[a-z0-9]+([._-][a-z0-9]+)*$/',
                Rule::unique('control.schema_vectors', 'service_code')
                    ->ignore($schema->id)
                    ->where(fn ($query) => $query
                        ->where('industry', $company->industry)
                        ->where('version', (int) $request->input('version', $schema->version ?? 1))),
            ],
            'version' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', Rule::in(['draft', 'active', 'deprecated', 'archived'])],
            'is_default' => ['nullable', 'boolean'],
            'industry' => ['required', 'string', 'max:100', Rule::in([$company->industry])],
            'schema_payload' => ['required', 'array'],
            'schema_payload.fields' => ['required', 'array'],
        ]);

        $payload = $validated;
        $this->enforceSingleActiveDefault($payload, $schema->id);

        $schema->update($payload);

        return back()->with('success', 'Schema Vector successfully updated.');
    }

    public function destroy($subdomain, $id)
    {
        $company = view()->shared('currentCompany');

        $schema = ServiceSchema::query()
            ->where('industry', $company->industry)
            ->findOrFail($id);

        $schema->delete();

        return back()->with('success', 'Schema Vector successfully deleted.');
    }

    private function canonicalizeSchemaInput(Request $request, ?ServiceSchema $schema = null): array
    {
        $nameSource = $request->input('service_name')
            ?: $request->input('display_name')
            ?: $schema?->service_name
            ?: $schema?->display_name
            ?: 'Service';
        $codeSource = $request->input('service_code')
            ?: $request->input('service_type')
            ?: $schema?->service_code
            ?: $schema?->service_type
            ?: $nameSource;

        $serviceName = trim((string) $nameSource);
        $serviceCode = strtolower((string) $codeSource);
        $serviceCode = preg_replace('/[^a-z0-9._-]+/', '_', $serviceCode) ?? '';
        $serviceCode = trim(preg_replace('/_+/', '_', $serviceCode) ?? '', '_');

        if ($serviceCode === '') {
            $serviceCode = Str::snake($serviceName);
        }

        $version = (int) $request->input('version', $schema?->version ?? 1);

        return [
            'display_name' => $serviceName,
            'service_name' => $serviceName,
            'service_type' => $request->input('service_type') ?: $serviceCode,
            'service_code' => $serviceCode,
            'version' => max(1, $version),
            'status' => (string) $request->input('status', $schema?->status ?? 'active'),
            'is_default' => $request->has('is_default')
                ? $request->boolean('is_default')
                : (bool) ($schema?->is_default ?? true),
        ];
    }

    private function enforceSingleActiveDefault(array $payload, ?int $ignoreId = null): void
    {
        if (($payload['status'] ?? null) !== 'active' || ! ($payload['is_default'] ?? false)) {
            return;
        }

        $query = ServiceSchema::query()
            ->where('industry', $payload['industry'])
            ->where('service_code', $payload['service_code'])
            ->where('status', 'active')
            ->where('is_default', true);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'is_default' => 'Only one active default schema is allowed per industry + service code.',
            ]);
        }
    }
}
