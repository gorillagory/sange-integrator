<?php

namespace App\Http\Controllers;

use App\Models\Client;   // This is the GLOBAL Model (control DB)
use App\Models\Contract; // This is the LOCAL Model (tenant DB)
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClientController extends Controller
{
    public function index()
    {
        // 1. Find which Global Clients have active contracts inside THIS specific Tenant Vault
        $activeClientIds = Contract::select('client_id')->distinct()->pluck('client_id');

        // 2. Fetch those Global Clients and eager-load their localized Contracts
        $clients = Client::whereIn('id', $activeClientIds)
            ->with('contracts') // Laravel magically handles this cross-database load!
            ->orderBy('name', 'asc')
            ->paginate(10);

        return Inertia::render('Clients/Index', [
            'clients' => $clients // Passing 'clients' instead of 'contracts'
        ]);
    }

    public function create()
    {
        // 🌐 Fetch existing Global Clients to populate the dropdown
        $globalClients = Client::orderBy('name')->get(['id', 'name', 'registration_number']);

        return Inertia::render('Clients/Create', [
            'globalClients' => $globalClients
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // 🌐 Global Client Mode Selection
            'selection_mode' => 'required|in:existing,new',
            'client_id' => 'required_if:selection_mode,existing|nullable|exists:control.clients,id',

            // 🌐 New Global Client Data
            'name' => 'required_if:selection_mode,new|nullable|string|max:255|unique:control.clients,name',
            'registration_number' => 'nullable|string|max:100',
            'hq_contact_person' => 'required_if:selection_mode,new|nullable|string|max:255',
            'hq_contact_email' => 'required_if:selection_mode,new|nullable|email|max:255',

            // 🏢 Local Contract Data (Tenant Specific)
            'contracts' => 'required|array|min:1',
            'contracts.*.contract_no' => 'required|string|max:100|unique:contracts,contract_no',
            'contracts.*.title' => 'required|string|max:255',
            'contracts.*.billing_address' => 'required|string',
            'contracts.*.payment_terms' => 'required|string|max:50',
        ]);

        // 🛡️ STEP 1: Resolve the Global Client
        if ($validated['selection_mode'] === 'new') {
            $client = Client::create([
                'name' => $validated['name'],
                'registration_number' => $validated['registration_number'] ?? null,
                'hq_contact_person' => $validated['hq_contact_person'],
                'hq_contact_email' => $validated['hq_contact_email'],
            ]);
        } else {
            $client = Client::findOrFail($validated['client_id']);
        }

        // 🛡️ STEP 2: Attach Local Contracts in the Tenant Vault
        foreach ($validated['contracts'] as $contractData) {
            Contract::create([
                'client_id' => $client->id,
                'contract_no' => $contractData['contract_no'],
                'title' => $contractData['title'],
                'billing_address' => $contractData['billing_address'],
                'payment_terms' => $contractData['payment_terms'],
            ]);
        }

        // 🛠️ FIXED: Pass the current subdomain to the route generator
        return redirect()->route('clients.index', [
            'subdomain' => request()->route('subdomain')
        ])->with('success', 'Corporate Client and Local Contracts successfully onboarded.');
    }
}
