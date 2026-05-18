<?php

namespace App\Console\Commands;

use App\Models\ServiceRecord;
use App\Models\ServiceRecordRow;
use App\Services\ServiceRecords\ServiceRecordExtractionManager;
use Illuminate\Console\Command;

class ExtractServiceRecordAnalytics extends Command
{
    protected $signature = 'service-records:extract
        {service_record_id? : Limit extraction to one service record}
        {--service-record-row-id= : Limit extraction to one service record row}
        {--pretty : Pretty-print the JSON output}';

    protected $description = 'Dry-run normalized analytics extraction rows for service records and rows.';

    public function handle(ServiceRecordExtractionManager $manager): int
    {
        $serviceRecords = ServiceRecord::query()
            ->when($this->argument('service_record_id'), fn ($query, $serviceRecordId) => $query->whereKey($serviceRecordId))
            ->with('rows')
            ->orderBy('id')
            ->get();

        $rows = [];

        foreach ($serviceRecords as $serviceRecord) {
            $serviceRecordRows = $serviceRecord->rows;

            if ($this->option('service-record-row-id')) {
                $serviceRecordRows = $serviceRecordRows->where('id', (int) $this->option('service-record-row-id'))->values();
            }

            foreach ($serviceRecordRows as $serviceRecordRow) {
                if (! $serviceRecordRow instanceof ServiceRecordRow) {
                    continue;
                }

                $rows[] = $manager->extract($serviceRecord, $serviceRecordRow);
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
