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
        $schemaPayload = $this->canonicalizeSchemaPayload($request->input('schema_payload'), $request);
        $request->merge([
            'schema_payload' => $schemaPayload,
        ]);

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
            'schema_payload.document_output' => ['nullable', 'string'],
            'schema_payload.pricing_units' => ['nullable', 'array'],
            'schema_payload.pricing_units.*' => ['string', 'max:100'],
        ]);

        $validated['schema_payload'] = $schemaPayload;
        $schema = ServiceSchema::create($validated);

        return redirect()
            ->route('system.blueprints.edit', $schema->id)
            ->with('success', 'Blueprint saved successfully.');
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
        $schemaPayload = $this->canonicalizeSchemaPayload($request->input('schema_payload'), $request);
        $request->merge([
            'schema_payload' => $schemaPayload,
        ]);

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
            'schema_payload.document_output' => ['nullable', 'string'],
            'schema_payload.pricing_units' => ['nullable', 'array'],
            'schema_payload.pricing_units.*' => ['string', 'max:100'],
        ]);

        $validated['schema_payload'] = $schemaPayload;
        $schema->update($validated);

        return back()->with('success', 'Blueprint saved successfully.');
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

        $saveMode = (string) $request->input('save_mode', '');
        $status = match ($saveMode) {
            'publish' => 'active',
            default => (string) $request->input('status', $schema?->status ?? 'active'),
        };

        return [
            'display_name' => $serviceName,
            'service_name' => $serviceName,
            'service_type' => $request->input('service_type') ?: $serviceCode,
            'service_code' => $serviceCode,
            'version' => max(1, (int) $request->input('version', $schema?->version ?? 1)),
            'status' => $status,
            'is_default' => $request->has('is_default')
                ? $request->boolean('is_default')
                : (bool) ($schema?->is_default ?? true),
            'industry' => (string) $request->input('industry', $schema?->industry),
        ];
    }

    private function canonicalizeSchemaPayload(mixed $payload, ?Request $request = null): array
    {
        if (is_string($payload)) {
            $decoded = json_decode($payload, true);
            $payload = is_array($decoded) ? $decoded : [];
        }

        $payload = is_array($payload) ? $payload : [];
        $fields = is_array($payload['fields'] ?? null) ? $payload['fields'] : [];
        $pricingUnits = is_array($payload['pricing_units'] ?? null)
            ? $payload['pricing_units']
            : (is_array($request?->input('pricing_units')) ? $request->input('pricing_units') : []);
        $normalizedFields = [];

        foreach (array_values(array_filter($fields, 'is_array')) as $index => $field) {
            $textTransform = (string) ($field['text_transform'] ?? 'none');
            $normalizedField = [
                'key' => trim((string) ($field['key'] ?? '')),
                'type' => trim((string) ($field['type'] ?? 'string')),
                'label' => trim((string) ($field['label'] ?? '')),
                'ui_component' => trim((string) ($field['ui_component'] ?? 'text_input')),
                'grid_span' => max(1, min(2, (int) ($field['grid_span'] ?? 1))),
                'rules' => array_values(array_filter(array_map(
                    static fn ($rule) => is_scalar($rule) ? trim((string) $rule) : '',
                    is_array($field['rules'] ?? null) ? $field['rules'] : []
                ))),
                'is_array' => (bool) ($field['is_array'] ?? false),
                'order' => (int) ($field['order'] ?? $index),
                'text_transform' => in_array($textTransform, ['none', 'uppercase', 'lowercase', 'capitalize'], true)
                    ? $textTransform
                    : 'none',
            ];

            $placeholder = trim((string) ($field['placeholder'] ?? ''));
            if ($placeholder !== '') {
                $normalizedField['placeholder'] = $placeholder;
            }

            $dataSource = $this->canonicalizeFieldDataSource($field['data_source'] ?? null, $field['api_endpoint'] ?? null, $field['cascade_parent'] ?? null);
            if ($dataSource !== []) {
                $normalizedField['data_source'] = $dataSource;
            }

            $fileOptions = $this->canonicalizeFileOptions($field['file_options'] ?? null, $field);
            if ($fileOptions !== []) {
                $normalizedField['file_options'] = $fileOptions;
            }

            $normalizedFields[] = $normalizedField;
        }

        return [
            'fields' => $normalizedFields,
            'document_output' => trim((string) ($payload['document_output'] ?? $request?->input('document_output', ''))),
            'pricing_units' => array_values(array_unique(array_filter(array_map(
                static fn ($unit) => strtolower(trim((string) $unit)),
                $pricingUnits
            )))),
        ];
    }

    private function canonicalizeFieldDataSource(mixed $dataSource, mixed $apiEndpoint, mixed $cascadeParent): array
    {
        $dataSource = is_array($dataSource) ? $dataSource : [];
        $endpoint = trim((string) ($dataSource['endpoint'] ?? $apiEndpoint ?? ''));
        $cascadeFrom = trim((string) ($dataSource['cascade_from'] ?? $cascadeParent ?? ''));

        return array_filter([
            'endpoint' => $endpoint,
            'cascade_from' => $cascadeFrom,
        ], static fn ($value) => $value !== '');
    }

    private function canonicalizeFileOptions(mixed $fileOptions, array $field): array
    {
        $fileOptions = is_array($fileOptions) ? $fileOptions : [];
        $maxSize = (int) ($fileOptions['max_size_mb'] ?? $field['file_max_size'] ?? 0);
        $maxCount = (int) ($fileOptions['max_count'] ?? $field['file_max_count'] ?? 0);
        $allowedTypes = trim((string) ($fileOptions['allowed_types'] ?? $field['file_types'] ?? ''));

        if (($field['type'] ?? null) !== 'file' && $fileOptions === []) {
            return [];
        }

        return [
            'max_size_mb' => $maxSize > 0 ? $maxSize : 5,
            'max_count' => $maxCount > 0 ? $maxCount : 1,
            'allowed_types' => $allowedTypes !== '' ? $allowedTypes : '*',
            'enable_preview' => (bool) ($fileOptions['enable_preview'] ?? $field['file_preview'] ?? false),
        ];
    }
}
