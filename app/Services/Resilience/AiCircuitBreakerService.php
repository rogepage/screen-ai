<?php

namespace App\Services\Resilience;

use App\Enums\CircuitBreakerState;

class AiCircuitBreakerService
{
    private CircuitBreakerState $state = CircuitBreakerState::CLOSED;

    private int $consecutiveFailures = 0;

    private float $openedTimestamp = 0.0;

    public function __construct(
        private readonly int $failureThreshold = 3,
        private readonly int $cooldownSeconds = 15
    ) {}

    public function isAvailable(): bool
    {
        if ($this->state === CircuitBreakerState::CLOSED) {
            return true;
        }

        if ($this->state === CircuitBreakerState::OPEN) {
            $elapsed = microtime(true) - $this->openedTimestamp;
            if ($elapsed >= $this->cooldownSeconds) {
                $this->state = CircuitBreakerState::HALF_OPEN;

                return true;
            }

            return false;
        }

        // HALF_OPEN permite uma tentativa de sonda
        return true;
    }

    public function recordSuccess(): void
    {
        $this->consecutiveFailures = 0;
        $this->state = CircuitBreakerState::CLOSED;
        $this->openedTimestamp = 0.0;
    }

    public function recordFailure(): void
    {
        $this->consecutiveFailures++;

        if ($this->consecutiveFailures >= $this->failureThreshold) {
            $this->state = CircuitBreakerState::OPEN;
            $this->openedTimestamp = microtime(true);
        }
    }

    public function getState(): CircuitBreakerState
    {
        // Se estiver em OPEN e o cooldown tiver passado, reflete HALF_OPEN
        if ($this->state === CircuitBreakerState::OPEN) {
            $elapsed = microtime(true) - $this->openedTimestamp;
            if ($elapsed >= $this->cooldownSeconds) {
                $this->state = CircuitBreakerState::HALF_OPEN;
            }
        }

        return $this->state;
    }

    public function reset(): void
    {
        $this->consecutiveFailures = 0;
        $this->state = CircuitBreakerState::CLOSED;
        $this->openedTimestamp = 0.0;
    }
}
