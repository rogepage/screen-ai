<?php

namespace Tests\Unit;

use App\Enums\PermissionStatus;
use App\Services\Native\MacPermissionService;
use App\Services\Observability\HealthCheckService;
use App\Services\Observability\MetricsCollectorService;
use App\Services\Resilience\AiCircuitBreakerService;
use Tests\TestCase;

class HealthCheckServiceTest extends TestCase
{
    public function test_health_check_reports_healthy_and_degraded(): void
    {
        $perm = new MacPermissionService;
        $perm->setMockedStatus(PermissionStatus::GRANTED);

        $cb = new AiCircuitBreakerService(failureThreshold: 2);
        $metrics = new MetricsCollectorService;

        $healthService = new HealthCheckService($perm, $cb, $metrics);

        // Estado inicial -> HEALTHY
        $report = $healthService->checkHealth();
        $this->assertEquals('healthy', $report['status']);
        $this->assertTrue($report['ai_available']);

        // Forçar Circuit Breaker OPEN -> DEGRADED
        $cb->recordFailure();
        $cb->recordFailure();
        $degradedReport = $healthService->checkHealth();
        $this->assertEquals('degraded', $degradedReport['status']);
        $this->assertFalse($degradedReport['ai_available']);
    }
}
