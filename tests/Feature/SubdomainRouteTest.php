<?php

namespace Tests\Feature;

use Tests\TestCase;

class SubdomainRouteTest extends TestCase
{
    /**
     * Test the System Core domain.
     */
    public function test_sys_core_resolves_correctly(): void
    {
        $response = $this->get('http://sys.bayam.test');
        $response->assertStatus(200);
        $response->assertSee('Welcome to Sys Core');
    }

    /**
     * Test the Bayamedic domain.
     */
    public function test_bayamedic_resolves_correctly(): void
    {
        $response = $this->get('http://bner.bayam.test');
        $response->assertStatus(200);
        $response->assertSee('Welcome to Bayamedic');
    }

    /**
     * Test the Bayam Travel domain.
     */
    public function test_bayam_travel_resolves_correctly(): void
    {
        $response = $this->get('http://bt.bayam.test');
        $response->assertStatus(200);
        $response->assertSee('Welcome to Bayam Travel');
    }

    /**
     * Test the Bayam Enterprise domain.
     */
    public function test_bayam_enterprise_resolves_correctly(): void
    {
        $response = $this->get('http://enterprise.bayam.test');
        $response->assertStatus(200);
        $response->assertSee('Welcome to Bayam Enterprise');
    }

    /**
     * Test the Bayam Tech domain.
     */
    public function test_bayam_tech_resolves_correctly(): void
    {
        $response = $this->get('http://btech.bayam.test');
        $response->assertStatus(200);
        $response->assertSee('Welcome to Bayam Tech');
    }

    /**
     * Test the Info Panel domain.
     */
    public function test_info_panel_resolves_correctly(): void
    {
        $response = $this->get('http://info.bayam.test');
        $response->assertStatus(200);
        $response->assertSee('Welcome to Info Panel');
    }
}
