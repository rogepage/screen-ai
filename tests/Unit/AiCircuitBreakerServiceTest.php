<?php

namespace Tests\Unit;

use App\Enums\CircuitBreakerState;
use App\Services\Resilience\AiCircuitBreakerService;
use Tests\TestCase;

class AiCircuitBreakerServiceTest extends TestCase
{
    public function test_circuit_breaker_transitions_to_open_after_failure_threshold(): void
    {
        $cb = new AiCircuitBreakerService(failureThreshold: 2, cooldownSeconds: 5);

        $this->assertEquals(CircuitBreakerState::CLOSED, $cb->getState());
        $this->assertTrue($cb->isAvailable());

        // 1ª Falha -> ainda CLOSED
        $cb->recordFailure();
        $this->assertEquals(CircuitBreakerState::CLOSED, $cb->getState());
        $this->assertTrue($cb->isAvailable());

        // 2ª Falha -> atinge threshold de 2 -> OPEN
        $cb->recordFailure();
        $this->assertEquals(CircuitBreakerState::OPEN, $cb->getState());
        $this->assertFalse($cb->isAvailable());

        // Sucesso restaura para CLOSED
        $cb->recordSuccess();
        $this->assertEquals(CircuitBreakerState::CLOSED, $cb->getState());
        $this->assertTrue($cb->isAvailable());
    }
}
