<?php

namespace App\Console\Commands;

use App\Models\Operation;
use App\Models\ServiceInstance;
use App\Services\Operations\OperationExtractionManager;
use Illuminate\Console\Command;

class ExtractOperationAnalytics extends Command
{
    protected $signature = 'operations:extract
        {operation_id? : Limit extraction to one operation}
        {--service-instance-id= : Limit extraction to one service instance}
        {--pretty : Pretty-print the JSON output}';

    protected $description = 'Dry-run normalized analytics extraction rows for operations and service instances.';

    public function handle(OperationExtractionManager $manager): int
    {
        $operations = Operation::query()
            ->when($this->argument('operation_id'), fn ($query, $operationId) => $query->whereKey($operationId))
            ->with('serviceInstances')
            ->orderBy('id')
            ->get();

        $rows = [];

        foreach ($operations as $operation) {
            $serviceInstances = $operation->serviceInstances;

            if ($this->option('service-instance-id')) {
                $serviceInstances = $serviceInstances->where('id', (int) $this->option('service-instance-id'))->values();
            }

            foreach ($serviceInstances as $serviceInstance) {
                if (! $serviceInstance instanceof ServiceInstance) {
                    continue;
                }

                $rows[] = $manager->extract($operation, $serviceInstance);
            }
        }

        $flags = JSON_UNESCAPED_SLASHES;

        if ($this->option('pretty')) {
            $flags |= JSON_PRETTY_PRINT;
        }

        $this->line(json_encode($rows, $flags) ?: '[]');

        return self::SUCCESS;
    }
}
