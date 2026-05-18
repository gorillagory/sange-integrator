<?php

namespace App\Services\ServiceRecords;

use App\Models\ServiceRecord;
use App\Models\ServiceRecordRow;
use App\Services\Handlers\HandlerRegistry;
use Illuminate\Container\Container;
use RuntimeException;

class ServiceRecordExtractionManager
{
    /**
     * @param  array<int, ServiceRecordExtractor>  $extractors
     */
    public function __construct(
        private readonly HandlerRegistry $handlers,
        private readonly array $extractors = [],
    ) {
    }

    public function extract(ServiceRecord $serviceRecord, ServiceRecordRow $serviceRecordRow): array
    {
        foreach ($this->resolvedExtractors($serviceRecord) as $extractor) {
            if ($extractor->supports($serviceRecord, $serviceRecordRow)) {
                return $extractor->extract($serviceRecord, $serviceRecordRow);
            }
        }

        throw new RuntimeException('No service record extractor is available for this row.');
    }

    /**
     * @return array<int, ServiceRecordExtractor>
     */
    private function resolvedExtractors(ServiceRecord $serviceRecord): array
    {
        if ($this->extractors !== []) {
            return $this->extractors;
        }

        $resolved = [];
        $handler = $this->handlers->forKey($serviceRecord->service_group_key);
        $extractorClass = $handler->extractorClass();

        if ($extractorClass && class_exists($extractorClass)) {
            $container = Container::getInstance();
            $instance = $container ? $container->make($extractorClass) : new $extractorClass();
            if ($instance instanceof ServiceRecordExtractor) {
                $resolved[] = $instance;
            }
        }

        $resolved[] = new GenericServiceRecordExtractor();

        return [
            ...$resolved,
        ];
    }
}
