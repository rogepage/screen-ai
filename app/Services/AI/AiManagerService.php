<?php

namespace App\Services\AI;

use App\DTO\AiPromptDTO;
use App\DTO\AiResponseDTO;
use App\Events\AnalysisCompleted;
use App\Events\AnalysisFailed;
use App\Events\AnalysisStarted;
use App\Services\Observability\MetricsCollectorService;
use App\Services\Observability\ObservabilityService;
use App\Services\Resilience\AiCircuitBreakerService;
use InvalidArgumentException;
use Throwable;

class AiManagerService
{
    /**
     * @var array<string, AiProviderInterface>
     */
    private array $providers = [];

    public function __construct(
        ?AiProviderInterface $defaultProvider = null,
        private readonly ?AiCircuitBreakerService $circuitBreaker = null,
        private readonly ?MetricsCollectorService $metricsCollector = null,
        private readonly ?ObservabilityService $observabilityService = null
    ) {
        if ($defaultProvider !== null) {
            $this->registerProvider($defaultProvider);
        }
    }

    public function registerProvider(AiProviderInterface $provider): void
    {
        $this->providers[$provider->getName()] = $provider;
    }

    public function getProvider(?string $name = null): AiProviderInterface
    {
        $providerName = $name ?? config('screen_ai.ai.default_provider', 'mock');

        if (! isset($this->providers[$providerName])) {
            if (isset($this->providers['mock'])) {
                return $this->providers['mock'];
            }

            throw new InvalidArgumentException("Provedor de IA '{$providerName}' não registrado.");
        }

        return $this->providers[$providerName];
    }

    public function analyze(AiPromptDTO $prompt, ?string $providerName = null, ?string $correlationId = null): AiResponseDTO
    {
        $cid = $correlationId ?? $this->observabilityService?->generateCorrelationId() ?? 'cid_'.uniqid();

        $this->observabilityService?->logEvent('info', 'AiManager', 'AI_REQUEST_STARTED', $cid, [
            'provider' => $providerName ?? 'default',
            'action_type' => $prompt->actionType->value,
        ]);

        if ($this->circuitBreaker !== null && ! $this->circuitBreaker->isAvailable()) {
            $this->observabilityService?->logEvent('warning', 'AiManager', 'CIRCUIT_BREAKER_BLOCKED', $cid);
            $failedResponse = new AiResponseDTO(
                success: false,
                provider: $providerName ?? 'circuit_breaker',
                model: 'none',
                rawResponse: [],
                errorMessage: 'Provedor de IA temporariamente indisponível (Circuit Breaker aberto).'
            );
            AnalysisFailed::dispatch($failedResponse->errorMessage);

            return $failedResponse;
        }

        AnalysisStarted::dispatch($prompt);
        $startTime = microtime(true);

        try {
            $provider = $this->getProvider($providerName);
            $response = $provider->process($prompt);

            $durationMs = round((microtime(true) - $startTime) * 1000, 2);

            if ($response->success) {
                $this->circuitBreaker?->recordSuccess();
                $this->metricsCollector?->recordAiCall(true, $durationMs);

                $this->observabilityService?->logEvent('info', 'AiManager', 'AI_REQUEST_COMPLETED', $cid, [
                    'provider' => $response->provider,
                    'duration_ms' => $durationMs,
                ]);

                AnalysisCompleted::dispatch($response);
            } else {
                $this->circuitBreaker?->recordFailure();
                $this->metricsCollector?->recordAiCall(false, $durationMs);

                $this->observabilityService?->logEvent('error', 'AiManager', 'AI_REQUEST_FAILED', $cid, [
                    'provider' => $response->provider,
                    'error' => $response->errorMessage,
                    'duration_ms' => $durationMs,
                ]);

                AnalysisFailed::dispatch($response->errorMessage ?? 'Falha na análise da IA');
            }

            return $response;
        } catch (Throwable $e) {
            $durationMs = round((microtime(true) - $startTime) * 1000, 2);
            $this->circuitBreaker?->recordFailure();
            $this->metricsCollector?->recordAiCall(false, $durationMs);

            $this->observabilityService?->logEvent('error', 'AiManager', 'AI_REQUEST_FAILED', $cid, [
                'error' => $e->getMessage(),
                'duration_ms' => $durationMs,
            ]);

            AnalysisFailed::dispatch($e->getMessage(), $e);
            throw $e;
        }
    }
}
