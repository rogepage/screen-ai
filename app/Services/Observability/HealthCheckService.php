<?php

namespace App\Services\Observability;

use App\Enums\CircuitBreakerState;
use App\Enums\HealthStatus;
use App\Enums\PermissionStatus;
use App\Services\Native\PermissionServiceInterface;
use App\Services\Resilience\AiCircuitBreakerService;

class HealthCheckService
{
    public function __construct(
        private readonly PermissionServiceInterface $permissionService,
        private readonly AiCircuitBreakerService $circuitBreaker,
        private readonly MetricsCollectorService $metricsCollector
    ) {}

    public function checkHealth(): array
    {
        $permStatus = $this->permissionService->getScreenRecordingStatus();
        $cbState = $this->circuitBreaker->getState();
        $aiAvailable = $this->circuitBreaker->isAvailable();

        $status = HealthStatus::HEALTHY;
        $statusMessage = 'Operacional';

        if ($permStatus === PermissionStatus::DENIED) {
            $status = HealthStatus::ERROR;
            $statusMessage = 'Permissão de Gravação de Tela necessária';
        } elseif ($cbState === CircuitBreakerState::OPEN || ! $aiAvailable) {
            $status = HealthStatus::DEGRADED;
            $statusMessage = 'IA temporariamente indisponível (Circuit Breaker Ativo)';
        }

        return [
            'status' => $status->value,
            'message' => $statusMessage,
            'permission_status' => $permStatus->value,
            'circuit_breaker_state' => $cbState->value,
            'ai_available' => $aiAvailable,
            'metrics' => $this->metricsCollector->getMetrics(),
        ];
    }
}
