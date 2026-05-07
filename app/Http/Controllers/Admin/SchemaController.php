<?php

// app/Http/Controllers/Admin/SchemaController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceSchema;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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

        $validated = $request->validate([
            'display_name' => ['required', 'string', 'max:255'],
            'service_type' => [
                'required',
                'string',
                'max:100',
                Rule::unique('control.service_schemas', 'service_type')
                    ->where(fn ($query) => $query->where('industry', $company->industry)),
            ],
            'industry' => ['required', 'string', 'max:100', Rule::in([$company->industry])],
            'schema_payload' => ['required', 'array'],
            'schema_payload.fields' => ['required', 'array'],
        ]);

        ServiceSchema::query()->create($validated);

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

        $validated = $request->validate([
            'display_name' => ['required', 'string', 'max:255'],
            'service_type' => [
                'required',
                'string',
                'max:100',
                Rule::unique('control.service_schemas', 'service_type')
                    ->ignore($schema->id)
                    ->where(fn ($query) => $query->where('industry', $company->industry)),
            ],
            'industry' => ['required', 'string', 'max:100', Rule::in([$company->industry])],
            'schema_payload' => ['required', 'array'],
            'schema_payload.fields' => ['required', 'array'],
        ]);

        $schema->update($validated);

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
}
