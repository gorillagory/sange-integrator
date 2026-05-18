<?php

namespace Tests\Unit;

use App\Models\Company;
use App\Services\Handlers\HandlerRegistry;
use PHPUnit\Framework\TestCase;

class HandlerRegistryTest extends TestCase
{
    public function test_it_resolves_handlers_by_company_industry_and_default(): void
    {
        $registry = new HandlerRegistry([
            'travel.services' => [
                'name' => 'Travel Services',
                'industry' => 'travel',
                'status' => 'active',
                'runtime_capabilities' => [],
                'document_policy' => ['document_types' => ['invoice'], 'canonical_roots' => ['operation']],
                'extraction_policy' => [],
            ],
            'medical.services' => [
                'name' => 'Medical Services',
                'industry' => 'medical',
                'status' => 'draft',
                'runtime_capabilities' => [],
                'document_policy' => ['document_types' => ['invoice'], 'canonical_roots' => ['operation']],
                'extraction_policy' => [],
            ],
        ], 'travel.services');

        $company = new Company([
            'industry' => 'medical',
        ]);

        $this->assertSame('medical.services', $registry->forCompany($company)->key());
        $this->assertSame('travel.services', $registry->forKey('unknown')->key());
        $this->assertSame(['travel.services', 'medical.services'], $registry->keys());
    }
}
