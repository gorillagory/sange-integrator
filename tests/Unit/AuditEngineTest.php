<?php

namespace Tests\Unit;

use App\Services\AuditEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class AuditEngineTest extends TestCase
{
    public function test_audit_engine_writes_event_with_signature_and_request_meta(): void
    {
        config()->set('app.key', 'testing-key');
        config()->set('audit.enabled', true);

        $request = Request::create('http://bayam.test:8000/login?via=unit', 'POST');
        $request->headers->set('origin', 'http://bayam.test:8000');
        $request->headers->set('user-agent', 'phpunit');
        app()->instance('request', $request);
        app()->instance('currentCompany', (object) ['id' => 77]);

        Auth::shouldReceive('id')->once()->andReturn(123);
        DB::shouldReceive('connection')->once()->with('control')->andReturnSelf();
        DB::shouldReceive('table')->once()->with('audit_logs')->andReturnSelf();
        DB::shouldReceive('insert')->once()->withArgs(function (array $payload): bool {
            $this->assertSame(77, $payload['tenant_id']);
            $this->assertSame(123, $payload['user_id']);
            $this->assertSame('AUTH', $payload['category']);
            $this->assertSame('AUTH.LOGIN_SUCCESS', $payload['action']);

            $newValues = json_decode((string) $payload['new_values'], true, 512, JSON_THROW_ON_ERROR);
            $this->assertSame('ok', $newValues['status']);
            $this->assertSame('POST', $newValues['__meta']['request_method'] ?? null);
            $this->assertSame('bayam.test', $newValues['__meta']['request_host'] ?? null);

            $expectedSignature = hash_hmac('sha256', implode('|', [
                $payload['id'],
                (string) $payload['tenant_id'],
                (string) $payload['user_id'],
                $payload['category'],
                $payload['action'],
                (string) $payload['resource_type'],
                (string) $payload['resource_id'],
                (string) $payload['new_values'],
                (string) $payload['old_values'],
            ]), 'testing-key');

            $this->assertSame($expectedSignature, $payload['signature']);

            return true;
        });

        AuditEngine::log('AUTH', 'AUTH.LOGIN_SUCCESS', ['status' => 'ok']);
    }

    public function test_audit_engine_falls_back_to_app_log_when_db_insert_fails(): void
    {
        config()->set('audit.enabled', true);
        config()->set('audit.fallback_log_channel', 'stack');

        $request = Request::create('http://bayam.test:8000/login', 'POST');
        app()->instance('request', $request);

        Auth::shouldReceive('id')->once()->andReturn(null);
        DB::shouldReceive('connection')->once()->with('control')->andReturnSelf();
        DB::shouldReceive('table')->once()->with('audit_logs')->andReturnSelf();
        DB::shouldReceive('insert')->once()->andThrow(new \RuntimeException('db unavailable'));

        Log::shouldReceive('channel')->once()->with('stack')->andReturnSelf();
        Log::shouldReceive('warning')->once()->withArgs(function (string $message, array $context): bool {
            $this->assertSame('AuditEngine write failed.', $message);
            $this->assertSame('AUTH', $context['category']);
            $this->assertSame('AUTH.LOGIN_FAILED', $context['action']);
            $this->assertSame('db unavailable', $context['exception']);

            return true;
        });

        AuditEngine::log('AUTH', 'AUTH.LOGIN_FAILED', ['email' => 'x@example.com']);
        $this->assertTrue(true);
    }
}
