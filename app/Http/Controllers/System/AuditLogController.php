<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    private const SEGMENT_CATEGORY_MAP = [
        'AUTH' => ['AUTH'],
        'ACCESS' => ['ACCESS'],
        'USER_ADMIN' => ['USER_ADMIN'],
        'DATA' => ['RECORD'],
    ];

    public function index(Request $request): Response
    {
        $filters = [
            'search' => trim((string) $request->string('search')),
            'segment' => strtoupper(trim((string) $request->string('segment'))),
            'category' => strtoupper(trim((string) $request->string('category'))),
            'action' => trim((string) $request->string('action')),
            'date_from' => trim((string) $request->string('date_from')),
            'date_to' => trim((string) $request->string('date_to')),
        ];

        $baseQuery = $this->buildFilteredQuery($filters);

        $logs = (clone $baseQuery)
            ->select([
                'logs.id',
                'logs.tenant_id',
                'logs.user_id',
                'logs.ip_address',
                'logs.user_agent',
                'logs.category',
                'logs.action',
                'logs.resource_type',
                'logs.resource_id',
                'logs.old_values',
                'logs.new_values',
                'logs.created_at',
                'users.name as user_name',
                'users.email as user_email',
                'companies.name as tenant_name',
                'companies.subdomain as tenant_subdomain',
            ])
            ->orderByDesc('logs.created_at')
            ->paginate(25)
            ->withQueryString()
            ->through(function ($row) {
                return [
                    'id' => $row->id,
                    'segment' => $this->segmentForCategory((string) $row->category),
                    'category' => (string) $row->category,
                    'action' => (string) $row->action,
                    'tenant' => [
                        'id' => $row->tenant_id,
                        'name' => $row->tenant_name,
                        'subdomain' => $row->tenant_subdomain,
                    ],
                    'actor' => [
                        'id' => $row->user_id,
                        'name' => $row->user_name,
                        'email' => $row->user_email,
                    ],
                    'resource' => [
                        'type' => $row->resource_type,
                        'id' => $row->resource_id,
                    ],
                    'ip_address' => $row->ip_address,
                    'user_agent' => $row->user_agent,
                    'old_values' => $this->decodeJsonPayload($row->old_values),
                    'new_values' => $this->decodeJsonPayload($row->new_values),
                    'created_at' => optional(Carbon::parse((string) $row->created_at))->toDateTimeString(),
                ];
            });

        $flatMappedCategories = collect(self::SEGMENT_CATEGORY_MAP)
            ->flatten()
            ->unique()
            ->values()
            ->all();

        $metrics = [
            'total' => (clone $baseQuery)->count(),
            'last_24h' => (clone $baseQuery)->where('logs.created_at', '>=', now()->subDay())->count(),
            'auth' => (clone $baseQuery)->where('logs.category', 'AUTH')->count(),
            'access_denied' => (clone $baseQuery)->where('logs.action', 'TENANT.ACCESS_DENIED')->count(),
        ];

        $segmentCounts = collect(self::SEGMENT_CATEGORY_MAP)->mapWithKeys(function (array $categories, string $segment) use ($baseQuery) {
            return [$segment => (clone $baseQuery)->whereIn('logs.category', $categories)->count()];
        })->toArray();

        $segmentCounts['SYSTEM'] = (clone $baseQuery)
            ->whereNotIn('logs.category', $flatMappedCategories)
            ->count();

        $topActions = (clone $baseQuery)
            ->selectRaw('logs.action, COUNT(*) as total')
            ->groupBy('logs.action')
            ->orderByDesc('total')
            ->limit(8)
            ->get()
            ->map(fn ($row) => [
                'action' => $row->action,
                'total' => (int) $row->total,
            ])
            ->values();

        $trendRows = (clone $baseQuery)
            ->where('logs.created_at', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(logs.created_at) as day, COUNT(*) as total')
            ->groupByRaw('DATE(logs.created_at)')
            ->orderBy('day')
            ->get();

        $trendByDay = collect($trendRows)
            ->mapWithKeys(fn ($row) => [Carbon::parse((string) $row->day)->toDateString() => (int) $row->total]);

        $trend = collect(range(0, 6))
            ->map(function (int $daysAgo) use ($trendByDay) {
                $day = now()->subDays(6 - $daysAgo)->toDateString();

                return [
                    'day' => $day,
                    'label' => Carbon::parse($day)->format('d M'),
                    'total' => $trendByDay->get($day, 0),
                ];
            })
            ->values();

        return Inertia::render('System/AuditLogs/Index', [
            'filters' => $filters,
            'logs' => $logs,
            'metrics' => $metrics,
            'segmentCounts' => $segmentCounts,
            'topActions' => $topActions,
            'trend' => $trend,
            'segmentOptions' => ['AUTH', 'ACCESS', 'USER_ADMIN', 'DATA', 'SYSTEM'],
            'categoryOptions' => ['AUTH', 'ACCESS', 'USER_ADMIN', 'RECORD'],
        ]);
    }

    private function buildFilteredQuery(array $filters): Builder
    {
        $search = $filters['search'];
        $segment = $filters['segment'];
        $category = $filters['category'];
        $action = $filters['action'];
        $dateFrom = $filters['date_from'];
        $dateTo = $filters['date_to'];

        $query = DB::connection('control')
            ->table('audit_logs as logs')
            ->leftJoin('users', 'users.id', '=', 'logs.user_id')
            ->leftJoin('companies', 'companies.id', '=', 'logs.tenant_id');

        if ($search !== '') {
            $query->where(function ($inner) use ($search) {
                $inner->where('logs.action', 'like', "%{$search}%")
                    ->orWhere('logs.category', 'like', "%{$search}%")
                    ->orWhere('logs.resource_type', 'like', "%{$search}%")
                    ->orWhere('logs.resource_id', 'like', "%{$search}%")
                    ->orWhere('users.name', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%")
                    ->orWhere('companies.name', 'like', "%{$search}%")
                    ->orWhere('companies.subdomain', 'like', "%{$search}%")
                    ->orWhere('logs.ip_address', 'like', "%{$search}%");
            });
        }

        if ($category !== '') {
            $query->where('logs.category', $category);
        }

        if ($action !== '') {
            $query->where('logs.action', $action);
        }

        if ($segment !== '') {
            $this->applySegmentFilter($query, $segment);
        }

        if ($dateFrom !== '') {
            try {
                $query->where('logs.created_at', '>=', Carbon::parse($dateFrom)->startOfDay());
            } catch (\Throwable) {
                // Ignore invalid dates from query string.
            }
        }

        if ($dateTo !== '') {
            try {
                $query->where('logs.created_at', '<=', Carbon::parse($dateTo)->endOfDay());
            } catch (\Throwable) {
                // Ignore invalid dates from query string.
            }
        }

        return $query;
    }

    private function applySegmentFilter(Builder $query, string $segment): void
    {
        $segment = strtoupper($segment);
        $flatMappedCategories = collect(self::SEGMENT_CATEGORY_MAP)
            ->flatten()
            ->unique()
            ->values()
            ->all();

        if ($segment === 'SYSTEM') {
            $query->whereNotIn('logs.category', $flatMappedCategories);

            return;
        }

        $categories = self::SEGMENT_CATEGORY_MAP[$segment] ?? null;

        if (! is_array($categories)) {
            return;
        }

        $query->whereIn('logs.category', $categories);
    }

    private function segmentForCategory(string $category): string
    {
        foreach (self::SEGMENT_CATEGORY_MAP as $segment => $categories) {
            if (in_array($category, $categories, true)) {
                return $segment;
            }
        }

        return 'SYSTEM';
    }

    private function decodeJsonPayload(mixed $payload): mixed
    {
        if (is_array($payload)) {
            return $payload;
        }

        if (! is_string($payload) || trim($payload) === '') {
            return [];
        }

        $decoded = json_decode($payload, true);

        return is_array($decoded) ? $decoded : [];
    }
}
