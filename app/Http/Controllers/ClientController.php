<?php

// app/Http/Controllers/ClientController.php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientRemarkPreset;
use App\Models\Contract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClientController extends Controller
{
    public function index()
    {
        $company = view()->shared('currentCompany');

        $activeClientIds = Contract::query()
            ->where('company_id', $company->id)
            ->select('client_id')
            ->distinct()
            ->pluck('client_id');

        $clients = Client::query()
            ->whereIn('id', $activeClientIds)
            ->with(['contracts' => function ($query) use ($company) {
                $query->where('company_id', $company->id)
                    ->orderBy('contract_no');
            }])
            ->orderBy('name', 'asc')
            ->paginate(10);

        return Inertia::render('Clients/Index', [
            'clients' => $clients,
        ]);
    }

    public function create()
    {
        $globalClients = Client::query()
            ->orderBy('name')
            ->get(['id', 'name', 'registration_number']);

        return Inertia::render('Clients/Create', [
            'globalClients' => $globalClients,
        ]);
    }

    public function store(Request $request)
    {
        $company = view()->shared('currentCompany');

        $validated = $request->validate([
            'selection_mode' => 'required|in:existing,new',
            'client_id' => 'required_if:selection_mode,existing|nullable|exists:control.clients,id',

            'name' => 'required_if:selection_mode,new|nullable|string|max:255|unique:control.clients,name',
            'registration_number' => 'nullable|string|max:100',
            'hq_contact_person' => 'required_if:selection_mode,new|nullable|string|max:255',
            'hq_contact_email' => 'required_if:selection_mode,new|nullable|email|max:255',

            'contracts' => 'required|array|min:1',
            'contracts.*.contract_no' => 'required|string|max:100|unique:tenant.contracts,contract_no',
            'contracts.*.title' => 'required|string|max:255',
            'contracts.*.billing_address' => 'required|string',
            'contracts.*.payment_terms' => 'required|string|max:50',
        ]);

        if ($validated['selection_mode'] === 'new') {
            $client = Client::query()->create([
                'name' => $validated['name'],
                'registration_number' => $validated['registration_number'] ?? null,
                'hq_contact_person' => $validated['hq_contact_person'],
                'hq_contact_email' => $validated['hq_contact_email'],
            ]);
        } else {
            $client = Client::query()->findOrFail($validated['client_id']);
        }

        foreach ($validated['contracts'] as $contractData) {
            Contract::query()->create([
                'company_id' => $company->id,
                'client_id' => $client->id,
                'contract_no' => $contractData['contract_no'],
                'title' => $contractData['title'],
                'billing_address' => $contractData['billing_address'],
                'payment_terms' => $contractData['payment_terms'],
            ]);
        }

        return redirect()->route('clients.index', [
            'subdomain' => request()->route('subdomain'),
        ])->with('success', 'Corporate Client and Local Contracts successfully onboarded.');
    }

    public function storeRemarkPreset(Request $request, Client $client): JsonResponse
    {
        $this->assertClientAvailableToTenant($client);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'content' => ['required', 'string', 'max:5000'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $preset = $client->remarkPresets()->create([
            'title' => trim($validated['title']),
            'content' => trim($validated['content']),
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'preset' => $this->presentRemarkPreset($preset->fresh()),
        ]);
    }

    public function updateRemarkPreset(Request $request, Client $client, ClientRemarkPreset $preset): JsonResponse
    {
        $this->assertClientAvailableToTenant($client);

        abort_unless((int) $preset->client_id === (int) $client->id, 404);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'content' => ['required', 'string', 'max:5000'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $preset->update([
            'title' => trim($validated['title']),
            'content' => trim($validated['content']),
            'is_active' => $validated['is_active'] ?? $preset->is_active,
        ]);

        return response()->json([
            'preset' => $this->presentRemarkPreset($preset->fresh()),
        ]);
    }

    public function destroyRemarkPreset(Client $client, ClientRemarkPreset $preset): JsonResponse
    {
        $this->assertClientAvailableToTenant($client);

        abort_unless((int) $preset->client_id === (int) $client->id, 404);

        $preset->delete();

        return response()->json([
            'deleted' => true,
            'preset_id' => $preset->id,
        ]);
    }

    private function assertClientAvailableToTenant(Client $client): void
    {
        $company = view()->shared('currentCompany');

        abort_unless(
            Contract::query()
                ->where('company_id', $company->id)
                ->where('client_id', $client->id)
                ->exists(),
            404
        );
    }

    private function presentRemarkPreset(ClientRemarkPreset $preset): array
    {
        return [
            'id' => $preset->id,
            'client_id' => $preset->client_id,
            'title' => $preset->title,
            'content' => $preset->content,
            'is_active' => (bool) $preset->is_active,
            'updated_at' => optional($preset->updated_at)?->toDateTimeString(),
        ];
    }
}
