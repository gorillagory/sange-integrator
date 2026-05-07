<?php

// app/Http/Controllers/ContractController.php

namespace App\Http\Controllers;

use App\Models\Contract;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    public function store(Request $request)
    {
        $company = view()->shared('currentCompany');

        $validated = $request->validate([
            'client_id' => 'required|exists:control.clients,id',
            'contract_no' => 'required|string|max:100|unique:tenant.contracts,contract_no',
            'title' => 'required|string|max:255',
            'billing_address' => 'required|string',
            'payment_terms' => 'required|string|max:50',
        ]);

        Contract::query()->create([
            'company_id' => $company->id,
            'client_id' => $validated['client_id'],
            'contract_no' => $validated['contract_no'],
            'title' => $validated['title'],
            'billing_address' => $validated['billing_address'],
            'payment_terms' => $validated['payment_terms'],
        ]);

        return back()->with('success', 'Contract successfully added to Vault.');
    }

    public function update(Request $request, $id)
    {
        $company = view()->shared('currentCompany');

        $contract = Contract::query()
            ->where('company_id', $company->id)
            ->findOrFail($id);

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
