<?php

namespace App\Console\Commands;

use App\Models\ServiceRecord;
use App\Models\ServiceRecordRow;
use App\Services\ServiceRecords\ServiceRecordProjectionMaterializer;
use Illuminate\Console\Command;

class MaterializeServiceRecordProjections extends Command
{
    protected $signature = 'service-records:materialize-projections
        {service_record_id? : Limit materialization to one service record}
        {--service-record-row-id= : Limit materialization to one service record row}';

    protected $description = 'Materialize normalized service record projection rows for downstream reporting.';

    public function handle(ServiceRecordProjectionMaterializer $materializer): int
    {
        $serviceRecords = ServiceRecord::query()
            ->when($this->argument('service_record_id'), fn ($query, $serviceRecordId) => $query->whereKey($serviceRecordId))
            ->with('rows')
            ->orderBy('id')
            ->get();

        $count = 0;

        foreach ($serviceRecords as $serviceRecord) {
            $serviceRecordRows = $serviceRecord->rows;

            if ($this->option('service-record-row-id')) {
                $serviceRecordRows = $serviceRecordRows->where('id', (int) $this->option('service-record-row-id'))->values();
            }

            foreach ($serviceRecordRows as $serviceRecordRow) {
                if (! $serviceRecordRow instanceof ServiceRecordRow) {
                    continue;
                }

                $materializer->materialize($serviceRecord, $serviceRecordRow);
                $count++;
            }
        }

        $this->info("Materialized {$count} service record projection row(s).");

        return self::SUCCESS;
    }
}
