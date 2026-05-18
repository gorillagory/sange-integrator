<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Operation;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index()
    {
        $company = view()->shared('currentCompany');

        $operations = Operation::query()
            ->with('client')
            ->where('company_id', $company->id)
            ->latest()
            ->get();

        $draftOperations = $operations->where('status', 'Draft');
        $lockedOperations = $operations->where('status', 'DocumentLocked');

        $recentOperations = $operations
            ->take(8)
            ->values()
            ->map(function (Operation $operation) {
                return [
                    'id' => $operation->id,
                    'reference_no' => $operation->reference_no,
                    'document_no' => $operation->document_no,
                    'status' => $operation->status,
                    'client_name' => $operation->client?->name,
                    'total_amount' => (float) ($operation->total_amount ?? 0),
                    'created_at' => optional($operation->created_at)?->toIso8601String(),
                ];
            });

        $monthlyRevenue = $lockedOperations
            ->groupBy(fn (Operation $operation) => optional($operation->updated_at ?: $operation->created_at)?->format('Y-m'))
            ->map(function ($group, $month) {
                return [
                    'month' => $month,
                    'count' => $group->count(),
                    'amount' => round((float) $group->sum('total_amount'), 2),
                ];
            })
            ->values()
            ->take(6)
            ->sortBy('month')
            ->values();

        $activeContracts = Contract::query()
            ->where('company_id', $company->id)
            ->count();

        return Inertia::render('Reports/Index', [
            'stats' => [
                'total_operations' => $operations->count(),
                'draft_operations' => $draftOperations->count(),
                'locked_operations' => $lockedOperations->count(),
                'active_contracts' => $activeContracts,
                'draft_pipeline_value' => round((float) $draftOperations->sum('total_amount'), 2),
                'recognized_revenue' => round((float) $lockedOperations->sum('total_amount'), 2),
            ],
            'recentOperations' => $recentOperations,
            'monthlyRevenue' => $monthlyRevenue,
        ]);
    }
}
