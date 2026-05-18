<?php

namespace Tests\Unit;

use App\Models\Operation;
use App\Models\ServiceInstance;
use App\Services\Handlers\HandlerRegistry;
use App\Services\Operations\OperationExtractionManager;
use PHPUnit\Framework\TestCase;

class OperationExtractionManagerTest extends TestCase
{
    public function test_it_emits_a_normalized_extraction_row(): void
    {
        $operation = new Operation([
            'company_id' => 9,
            'client_id' => 42,
            'contract_no' => 'CTR-001',
            'reference_no' => 'OPR-202605-ABC12',
            'handler_key' => 'travel.services',
            'document_no' => 'INV-2026-010',
            'status' => 'DocumentLocked',
            'total_amount' => 1560.75,
        ]);
        $operation->id = 88;

        $serviceInstance = new ServiceInstance([
            'service_schema_id' => 5,
            'service_code' => 'flight',
            'service_type' => 'flight',
            'service_name' => 'Flight',
            'schema_version' => 3,
            'service_details' => ['route' => 'KUL-NRT'],
            'service_details_extra' => ['fare_basis' => 'Y'],
            'qty' => 2,
            'unit_fare' => 500,
            'tax_value' => 30,
            'tax_amount' => 60,
            'client_price' => 780.375,
            'line_total' => 1560.75,
            'payload_snapshot' => ['captured_at' => '2026-05-15T10:00:00+08:00'],
        ]);
        $serviceInstance->id = 144;

        $registry = new HandlerRegistry([
            'travel.services' => [
                'name' => 'Travel Services',
                'industry' => 'travel',
                'status' => 'active',
                'runtime_capabilities' => [],
                'document_policy' => [
                    'document_types' => ['invoice'],
                    'canonical_roots' => ['operation', 'services', 'service_instances', 'finance', 'client', 'company'],
                ],
                'extraction_policy' => [
                    'extractor' => \App\Services\Operations\TravelServicesOperationExtractor::class,
                ],
            ],
        ], 'travel.services');

        $row = (new OperationExtractionManager($registry))->extract($operation, $serviceInstance);

        $this->assertSame('travel.services', $row['handler_key']);
        $this->assertSame(88, $row['operation_id']);
        $this->assertSame(144, $row['service_instance_id']);
        $this->assertSame('flight', $row['service_code']);
        $this->assertSame('DocumentLocked', $row['dimensions']['status']);
        $this->assertSame('INV-2026-010', $row['dimensions']['document_no']);
        $this->assertSame(2.0, $row['metrics']['qty']);
        $this->assertSame(1560.75, $row['metrics']['line_total']);
        $this->assertSame(500.0 * 2, $row['metrics']['supplier_total']);
        $this->assertSame(['route' => 'KUL-NRT'], $row['raw_payload']['service_details']);
    }
}
