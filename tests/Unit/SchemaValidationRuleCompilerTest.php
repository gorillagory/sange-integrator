<?php

namespace Tests\Unit;

use App\Services\SchemaValidationRuleCompiler;
use PHPUnit\Framework\TestCase;

class SchemaValidationRuleCompilerTest extends TestCase
{
    public function test_it_compiles_scalar_and_array_field_rules(): void
    {
        $compiler = new SchemaValidationRuleCompiler();

        $compiled = $compiler->compile([
            [
                'key' => 'flight_number',
                'label' => 'Flight Number',
                'type' => 'string',
                'rules' => ['required'],
                'is_array' => false,
            ],
            [
                'key' => 'passenger_email',
                'label' => 'Passenger Email',
                'type' => 'email',
                'rules' => ['required', 'email'],
                'is_array' => true,
            ],
        ]);

        $this->assertArrayHasKey('flight_number', $compiled['rules']);
        $this->assertContains('required', $compiled['rules']['flight_number']);
        $this->assertContains('string', $compiled['rules']['flight_number']);

        $this->assertArrayHasKey('passenger_email', $compiled['rules']);
        $this->assertArrayHasKey('passenger_email.*', $compiled['rules']);
        $this->assertContains('array', $compiled['rules']['passenger_email']);
        $this->assertContains('email', $compiled['rules']['passenger_email.*']);
    }

    public function test_it_tracks_known_keys_and_attributes(): void
    {
        $compiler = new SchemaValidationRuleCompiler();

        $compiled = $compiler->compile([
            ['key' => 'service_date', 'label' => 'Service Date', 'type' => 'date', 'rules' => [], 'is_array' => false],
            ['key' => '', 'label' => 'Ignored', 'type' => 'string', 'rules' => [], 'is_array' => false],
        ]);

        $this->assertSame(['service_date'], $compiled['known_keys']);
        $this->assertSame('Service Date', $compiled['attributes']['service_date']);
        $this->assertArrayNotHasKey('', $compiled['rules']);
    }
}

