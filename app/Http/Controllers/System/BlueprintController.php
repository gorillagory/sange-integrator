<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ServiceSchema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class BlueprintController extends Controller
{
    public function index()
    {
        $schemas = ServiceSchema::orderBy('industry')->orderBy('display_name')->get();

        return Inertia::render('System/Blueprints/Index', [
            'schemas' => $schemas
        ]);
    }

    // 👈 NEW: Show the blank Forge screen
    public function create()
    {
        return Inertia::render('System/Blueprints/Create');
    }

    // 👈 NEW: Save the brand new schema to the database
    public function store(Request $request)
    {
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
                        ->where('industry', $request->input('industry'))
                        ->where('version', (int) $request->input('version', 1))),
            ],
            'version' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', Rule::in(['draft', 'active', 'deprecated', 'archived'])],
            'is_default' => ['nullable', 'boolean'],
            'industry' => ['required', 'string', 'max:100'],
            'schema_payload' => ['required', 'array'],
            'schema_payload.fields' => ['required', 'array'],
        ]);

        ServiceSchema::create($validated);

        return redirect()->route('system.blueprints.index')->with('success', 'New Blueprint successfully forged and deployed.');
    }

    public function edit($id)
    {
        $schema = ServiceSchema::findOrFail($id);

        return Inertia::render('System/Blueprints/Edit', [
            'schema' => $schema
        ]);
    }

    public function update(Request $request, $id)
    {
        $schema = ServiceSchema::findOrFail($id);
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
                        ->where('industry', $request->input('industry', $schema->industry))
                        ->where('version', (int) $request->input('version', $schema->version ?? 1))),
            ],
            'version' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', Rule::in(['draft', 'active', 'deprecated', 'archived'])],
            'is_default' => ['nullable', 'boolean'],
            'industry' => ['required', 'string', 'max:100'],
            'schema_payload' => 'required|array',
            'schema_payload.fields' => 'required|array',
        ]);

        $schema->update($validated);

        return redirect()->route('system.blueprints.index')->with('success', 'Blueprint Engine successfully updated.');
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

        return [
            'display_name' => $serviceName,
            'service_name' => $serviceName,
            'service_type' => $request->input('service_type') ?: $serviceCode,
            'service_code' => $serviceCode,
            'version' => max(1, (int) $request->input('version', $schema?->version ?? 1)),
            'status' => (string) $request->input('status', $schema?->status ?? 'active'),
            'is_default' => $request->has('is_default')
                ? $request->boolean('is_default')
                : (bool) ($schema?->is_default ?? true),
            'industry' => (string) $request->input('industry', $schema?->industry),
        ];
    }
}
