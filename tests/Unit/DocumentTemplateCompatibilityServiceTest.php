<?php

namespace Tests\Unit;

use App\Services\DocumentTemplateCompatibilityService;
use PHPUnit\Framework\TestCase;

class DocumentTemplateCompatibilityServiceTest extends TestCase
{
    public function test_it_marks_valid_invoice_bindings_as_compatible(): void
    {
        $service = new DocumentTemplateCompatibilityService();

        $result = $service->analyze('invoice', [
            'data_paths' => ['invoice.number', 'client.name', 'invoice.line_items'],
            'placeholder_paths' => ['invoice.number'],
            'list_paths' => ['invoice.line_items'],
            'table_bindings' => [
                [
                    'data_key' => 'invoice.line_items',
                    'columns' => ['description', 'quantity', 'total'],
                ],
            ],
        ]);

        $this->assertSame('compatible', $result['status']);
        $this->assertSame(0, $result['issue_count']);
    }

    public function test_it_reports_unknown_paths_and_columns(): void
    {
        $service = new DocumentTemplateCompatibilityService();

        $result = $service->analyze('invoice', [
            'data_paths' => ['invoice.unknown_field'],
            'placeholder_paths' => [],
            'list_paths' => ['invoice.unknown_array'],
            'table_bindings' => [
                [
                    'data_key' => 'invoice.line_items',
                    'columns' => ['description', 'bad_column'],
                ],
            ],
        ]);

        $this->assertSame('warning', $result['status']);
        $this->assertGreaterThan(0, $result['issue_count']);
        $this->assertContains('invoice.unknown_field', $result['issues']['missing_data_paths']);
        $this->assertNotEmpty($result['issues']['table_issues']);
    }

    public function test_it_accepts_canonical_service_instance_field_paths(): void
    {
        $service = new DocumentTemplateCompatibilityService();

        $result = $service->analyze('itinerary', [
            'data_paths' => ['service_instances', 'service_instances.fields.flight_no'],
            'placeholder_paths' => ['service_instances.fields.flight_no'],
            'list_paths' => ['service_instances'],
            'table_bindings' => [],
        ]);

        $this->assertSame('compatible', $result['status']);
    }
}
