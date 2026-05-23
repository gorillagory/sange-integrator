<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;

class ViteDevServerHostRewriteTest extends TestCase
{
    private string $hotFile;

    private bool $hadOriginalHotFile = false;

    private ?string $originalHotContents = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hotFile = public_path('hot');
        $this->hadOriginalHotFile = is_file($this->hotFile);
        $this->originalHotContents = $this->hadOriginalHotFile
            ? file_get_contents($this->hotFile) ?: ''
            : null;
    }

    protected function tearDown(): void
    {
        if ($this->hadOriginalHotFile) {
            file_put_contents($this->hotFile, $this->originalHotContents ?? '');
        } elseif (is_file($this->hotFile)) {
            unlink($this->hotFile);
        }

        parent::tearDown();
    }

    public function test_login_page_uses_the_request_host_for_vite_assets_when_hot_mode_is_enabled(): void
    {
        file_put_contents($this->hotFile, 'http://localhost:5174');

        $response = $this->get('http://sange-node.tailnet.ts.net/login');

        $response->assertOk();
        $response->assertSee('http://sange-node.tailnet.ts.net:5174/@vite/client', false);
        $response->assertSee('http://sange-node.tailnet.ts.net:5174/resources/css/app.css', false);
        $response->assertSee('http://sange-node.tailnet.ts.net:5174/resources/js/app.js', false);
        $response->assertDontSee('http://localhost:5174/@vite/client', false);
    }
}
