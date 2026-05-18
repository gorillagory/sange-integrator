<?php

namespace Tests\Unit;

use App\Services\DocumentTemplateBindingIndexService;
use PHPUnit\Framework\TestCase;

class DocumentTemplateBindingIndexServiceTest extends TestCase
{
    public function test_it_extracts_bindings_from_text_list_table_and_nested_rows(): void
    {
        $service = new DocumentTemplateBindingIndexService();

        $index = $service->extract([
            'header' => [
                [
                    'type' => 'text',
                    'content' => 'Invoice {{ invoice.number }} for {{ client.name }}',
                ],
            ],
            'body' => [
                [
                    'type' => 'row',
                    'columns' => [
                        [
                            'blocks' => [
                                ['type' => 'list', 'data_key' => 'invoice.line_items'],
                                [
                                    'type' => 'table',
                                    'data_key' => 'invoice.line_items',
                                    'columns' => [
                                        ['key' => 'description'],
                                        ['key' => 'total'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'footer' => [],
        ]);

        $this->assertContains('invoice.number', $index['placeholder_paths']);
        $this->assertContains('client.name', $index['placeholder_paths']);
        $this->assertContains('invoice.line_items', $index['list_paths']);
        $this->assertNotEmpty($index['table_bindings']);
        $this->assertGreaterThan(0, $index['binding_count']);
    }
}

