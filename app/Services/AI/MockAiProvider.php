<?php

namespace App\Services\AI;

use App\DTO\AiPromptDTO;
use App\DTO\AiResponseDTO;

class MockAiProvider implements AiProviderInterface
{
    public function __construct(
        private bool $shouldFail = false,
        private ?string $simulatedErrorMessage = null
    ) {}

    public function getName(): string
    {
        return 'mock';
    }

    public function setShouldFail(bool $fail, ?string $message = null): void
    {
        $this->shouldFail = $fail;
        $this->simulatedErrorMessage = $message;
    }

    public function process(AiPromptDTO $prompt): AiResponseDTO
    {
        if ($this->shouldFail || ($prompt->options['simulate_failure'] ?? false)) {
            return new AiResponseDTO(
                text: '',
                success: false,
                provider: $this->getName(),
                model: 'mock-v1',
                errorMessage: $this->simulatedErrorMessage ?? 'Erro simulado no provedor Mock.',
                metadata: [
                    'simulated_failure' => true,
                ]
            );
        }

        $actionValue = $prompt->actionType->value;
        $userText = $prompt->userInstruction ?? 'sem instrução adicional';

        $simulatedOutput = sprintf(
            "[Mock AI Response] Ação '%s' processada com sucesso. Instrução recebida: '%s'.",
            $actionValue,
            $userText
        );

        return new AiResponseDTO(
            text: $simulatedOutput,
            success: true,
            provider: $this->getName(),
            model: 'mock-v1',
            metadata: [
                'processed_at' => time(),
                'action_type' => $actionValue,
            ]
        );
    }
}
