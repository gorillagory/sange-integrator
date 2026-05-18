<?php

namespace App\Services\ServiceRecords;

use App\Models\ServiceRecord;
use App\Models\ServiceRecordRow;

interface ServiceRecordExtractor
{
    public function supports(ServiceRecord $serviceRecord, ServiceRecordRow $serviceRecordRow): bool;

    public function extract(ServiceRecord $serviceRecord, ServiceRecordRow $serviceRecordRow): array;
}
