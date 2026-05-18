<?php

namespace Tests\Unit;

use App\Models\Client;
use App\Models\Company;
use App\Models\Contract;
use App\Models\Operation;
use App\Models\ServiceInstance;
use App\Services\DocumentRenderContextFactory;
use App\Services\Handlers\HandlerRegistry;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class DocumentRenderContextFactoryTest extends TestCase
{
    public function test_it_builds_canonical_service_record_payload_without_booking_aliases(): void
    {
        $registry = new HandlerRegistry([
            'travel.services' => [
                'name' => 'Travel Services',
                'industry' => 'travel',
                'status' => 'active',
                'runtime_capabilities' => [],
                'document_policy' => [
                    'document_types' => ['invoice'],
                    'canonical_roots' => ['service_record', 'service_rows', 'schema_vectors', 'finance', 'client', 'company'],
                ],
                'extraction_policy' => [],
            ],
        ], 'travel.services');

        $company = new Company([
            'name' => 'Bayam Travel',
            'phones' => ['+60 11-1111 1111'],
            'address' => ['Kuala Lumpur'],
        ]);

        $client = new Client([
            'name' => 'Acme Corp',
            'hq_contact_email' => 'billing@acme.test',
        ]);

        $contract = new Contract([
            'billing_address' => ['Cyberjaya'],
        ]);

        $serviceInstance = new ServiceInstance([
            'service_code' => 'airport_transfer',
            'service_name' => 'Airport Transfer',
            'service_details' => ['pickup_point' => 'KLIA'],
            'service_details_extra' => ['vehicle' => 'MPV'],
            'qty' => 1,
            'unit_fare' => 100,
            'tax_amount' => 6,
            'client_price' => 106,
            'line_total' => 106,
            'payload_snapshot' => ['captured_at' => '2026-05-15T10:00:00+08:00'],
        ]);
        $serviceInstance->id = 17;

        $operation = new Operation([
            'reference_no' => 'OPR-202605-XYZ99',
            'handler_key' => 'travel.services',
            'document_no' => 'INV-2026-009',
            'status' => 'DocumentLocked',
            'total_amount' => 106,
        ]);
        $operation->id = 12;
        $operation->setRawAttributes([
            ...$operation->getAttributes(),
            'created_at' => CarbonImmutable::parse('2026-05-15 10:00:00', 'Asia/Kuala_Lumpur')->format('Y-m-d H:i:s'),
            'updated_at' => CarbonImmutable::parse('2026-05-15 11:00:00', 'Asia/Kuala_Lumpur')->format('Y-m-d H:i:s'),
        ], true);
        $operation->setRelation('company', $company);
        $operation->setRelation('client', $client);
        $operation->setRelation('services', new Collection([$serviceInstance]));

        $payload = (new DocumentRenderContextFactory($registry))->makeInvoiceFromOperation($operation, $company, $contract);

        $this->assertSame('OPR-202605-XYZ99', $payload['service_record']['reference_no']);
        $this->assertSame('travel.services', $payload['service_record']['service_group_key']);
        $this->assertCount(1, $payload['service_rows']);
        $this->assertSame('Airport Transfer', $payload['service_rows'][0]['service']['title']);
        $this->assertSame('airport_transfer', $payload['schema_vectors'][0]['service_code']);
        $this->assertSame('OPR-202605-XYZ99', $payload['operation']['reference_no']);
        $this->assertSame('travel.services', $payload['operation']['handler_key']);
        $this->assertSame('INV-2026-009', $payload['operation']['document_no']);
        $this->assertSame('INV-2026-009', $payload['invoice']['number']);
        $this->assertCount(1, $payload['services']);
        $this->assertSame('Travel Services', $payload['handler']['name']);
        $this->assertArrayNotHasKey('booking', $payload);
    }
}
