<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceSchema;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SchemaController extends Controller
{
    /**
     * List all Schema Vectors
     */
    public function index($subdomain)
    {
        // We fetch from the 'control' database (handled by the Model connection)
        $schemas = ServiceSchema::orderBy('display_name')->get();

        return Inertia::render('Admin/Schemas/Index', [
            'schemas' => $schemas
        ]);
    }

    /**
     * Show the Schema Builder UI for a New Vector
     */
    public function create($subdomain)
    {
        return Inertia::render('Admin/Schemas/Builder');
    }

    /**
     * Deploy a new Schema Vector to Production
     */
    public function store(Request $request, $subdomain)
    {
        $validated = $request->validate([
            'display_name' => 'required|string|max:255',
            'service_type' => 'required|string|max:100|unique:control.service_schemas,service_type',
            'industry' => 'required|string|max:100',
            'schema_payload' => 'required|array',
        ]);

        ServiceSchema::create($validated);

        // Redirect back to the index list after creation
        return redirect('/admin/schemas')->with('success', 'Schema Vector successfully deployed to Production.');
    }

    /**
     * Show the Schema Builder UI for an Existing Vector
     */
    public function edit($subdomain, $id)
    {
        $schema = ServiceSchema::findOrFail($id);

        return Inertia::render('Admin/Schemas/Builder', [
            'schema' => $schema
        ]);
    }

    /**
     * Update an existing Schema Vector
     */
    public function update(Request $request, $subdomain, $id)
    {
        $schema = ServiceSchema::findOrFail($id);

        $validated = $request->validate([
            'display_name' => 'required|string|max:255',
            'service_type' => 'required|string|max:100|unique:control.service_schemas,service_type,' . $schema->id,
            'industry' => 'required|string|max:100',
            'schema_payload' => 'required|array',
        ]);

        $schema->update($validated);

        // Stay on the builder page after updating
        return back()->with('success', 'Schema Vector successfully updated.');
    }

    /**
     * Delete an existing Schema Vector
     */
    public function destroy($subdomain, $id)
    {
        $schema = ServiceSchema::findOrFail($id);
        $schema->delete();

        return back()->with('success', 'Schema Vector successfully deleted.');
    }
}
