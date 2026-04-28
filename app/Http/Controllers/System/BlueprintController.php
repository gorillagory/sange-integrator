<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ServiceSchema;
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
        $validated = $request->validate([
            'display_name' => 'required|string|max:255',
            'service_type' => 'required|string|max:100|unique:control.service_schemas,service_type',
            'industry' => 'required|string|max:100',
            'schema_payload' => 'required|array',
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

        $validated = $request->validate([
            'schema_payload' => 'required|array',
            'schema_payload.fields' => 'required|array',
        ]);

        $schema->update([
            'schema_payload' => $validated['schema_payload']
        ]);

        return redirect()->route('system.blueprints.index')->with('success', 'Blueprint Engine successfully updated.');
    }
}
