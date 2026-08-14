<?php

namespace App\Services\AI;

use App\DTO\AiPromptDTO;
use App\DTO\AiResponseDTO;
use App\Events\AnalysisCompleted;
use App\Events\AnalysisFailed;
use App\Events\AnalysisStarted;
use InvalidArgumentException;
use Throwable;

class AiManagerService
{
    /**
     * @var array<string, AiProviderInterface>
     */
    private array $providers = [];

    public function __construct(
        ?AiProviderInterface $defaultProvider = null
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

        if (!isset($this->providers[$providerName])) {
            if (isset($this->providers['mock'])) {
                return $this->providers['mock'];
            }

            throw new InvalidArgumentException("Provedor de IA '{$providerName}' não registrado.");
        }

        return $this->providers[$providerName];
    }

    public function analyze(AiPromptDTO $prompt, ?string $providerName = null): AiResponseDTO
    {
        AnalysisStarted::dispatch($prompt);

        try {
            $provider = $this->getProvider($providerName);
            $response = $provider->process($prompt);

            if ($response->success) {
                AnalysisCompleted::dispatch($response);
            } else {
                AnalysisFailed::dispatch($response->errorMessage ?? 'Falha na análise da IA');
            }

            return $response;
        } catch (Throwable $e) {
            AnalysisFailed::dispatch($e->getMessage(), $e);
            throw $e;
        }
    }
}
