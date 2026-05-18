<?php

namespace App\Console\Commands;

use App\Models\Operation;
use App\Models\ServiceInstance;
use App\Services\Operations\OperationProjectionMaterializer;
use Illuminate\Console\Command;

class MaterializeOperationProjections extends Command
{
    protected $signature = 'operations:materialize-projections
        {operation_id? : Limit materialization to one operation}
        {--service-instance-id= : Limit materialization to one service instance}';

    protected $description = 'Materialize normalized operation projection rows for downstream reporting.';

    public function handle(OperationProjectionMaterializer $materializer): int
    {
        $operations = Operation::query()
            ->when($this->argument('operation_id'), fn ($query, $operationId) => $query->whereKey($operationId))
            ->with('serviceInstances')
            ->orderBy('id')
            ->get();

        $count = 0;

        foreach ($operations as $operation) {
            $serviceInstances = $operation->serviceInstances;

            if ($this->option('service-instance-id')) {
                $serviceInstances = $serviceInstances->where('id', (int) $this->option('service-instance-id'))->values();
            }

            foreach ($serviceInstances as $serviceInstance) {
                if (! $serviceInstance instanceof ServiceInstance) {
                    continue;
                }

                $materializer->materialize($operation, $serviceInstance);
                $count++;
            }
        }

        $this->info("Materialized {$count} operation projection row(s).");

        return self::SUCCESS;
    }
}
