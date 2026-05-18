<?php

namespace App\Services\ServiceRecords;

use App\Models\ServiceRecord;
use App\Models\ServiceRecordProjection;
use App\Models\ServiceRecordRow;
use Carbon\CarbonImmutable;

class ServiceRecordProjectionMaterializer
{
    public function __construct(
        private readonly ServiceRecordExtractionManager $manager,
    ) {
    }

    public function materialize(ServiceRecord $serviceRecord, ServiceRecordRow $serviceRecordRow): ServiceRecordProjection
    {
        $row = $this->manager->extract($serviceRecord, $serviceRecordRow);

        return ServiceRecordProjection::query()->updateOrCreate(
            [
                'service_record_row_id' => $row['service_record_row_id'],
            ],
            [
                'service_group_key' => $row['service_group_key'],
                'service_record_id' => $row['service_record_id'],
                'schema_vector_id' => $row['schema_vector_id'],
                'service_code' => $row['service_code'],
                'schema_version' => $row['schema_version'],
                'captured_at' => CarbonImmutable::parse($row['captured_at']),
                'dimensions' => $row['dimensions'],
                'metrics' => $row['metrics'],
                'raw_payload' => $row['raw_payload'],
            ]
        );
    }
}
