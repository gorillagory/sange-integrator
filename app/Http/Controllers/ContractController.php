<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:control.clients,id',
            'contract_no' => 'required|string|max:100|unique:tenant.contracts,contract_no',
            'title' => 'required|string|max:255',
            'billing_address' => 'required|string',
            'payment_terms' => 'required|string|max:50',
        ]);

        Contract::create($validated);

        // Inertia 'back()' seamlessly closes the modal and refreshes the data grid!
        return back()->with('success', 'Contract successfully added to Vault.');
    }

    public function update(Request $request, $id)
    {
        $contract = Contract::findOrFail($id);

        $validated = $request->validate([
            'contract_no' => 'required|string|max:100|unique:tenant.contracts,contract_no,' . $contract->id,
            'title' => 'required|string|max:255',
            'billing_address' => 'required|string',
            'payment_terms' => 'required|string|max:50',
        ]);

        $contract->update($validated);

        return back()->with('success', 'Contract parameters updated.');
    }
}
