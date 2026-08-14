<?php

namespace Tests\Unit;

use App\Services\Observability\ObservabilityService;
use Tests\TestCase;

class ObservabilityServiceTest extends TestCase
{
    public function test_generates_valid_correlation_id(): void
    {
        $service = new ObservabilityService;
        $cid1 = $service->generateCorrelationId();
        $cid2 = $service->generateCorrelationId();

        $this->assertStringStartsWith('cid_', $cid1);
        $this->assertStringStartsWith('cid_', $cid2);
        $this->assertNotEquals($cid1, $cid2);
    }

    public function test_sanitizes_sensitive_keys_in_context(): void
    {
        $service = new ObservabilityService;

        $rawContext = [
            'api_key' => 'secret_123',
            'user_token' => 'jwt_456',
            'password' => 'pass_789',
            'safe_field' => 'safe_value',
            'nested' => [
                'auth_secret' => 'nested_secret',
                'regular_item' => 'normal',
            ],
        ];

        $sanitized = $service->sanitizeContext($rawContext);

        $this->assertEquals('********', $sanitized['api_key']);
        $this->assertEquals('********', $sanitized['user_token']);
        $this->assertEquals('********', $sanitized['password']);
        $this->assertEquals('safe_value', $sanitized['safe_field']);
        $this->assertEquals('********', $sanitized['nested']['auth_secret']);
        $this->assertEquals('normal', $sanitized['nested']['regular_item']);
    }

    public function test_log_event_executes_without_exception(): void
    {
        $service = new ObservabilityService;
        $service->logEvent('info', 'ScreenMonitor', 'MONITOR_STARTED', 'cid_123', ['safe' => 'val']);
        $service->logEvent('error', 'AiManager', 'AI_REQUEST_FAILED', 'cid_123', ['err' => 'timeout']);

        $this->assertTrue(true);
    }
}
