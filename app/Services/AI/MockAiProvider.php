<?php

namespace App\Services\AI;

use App\DTO\AiPromptDTO;
use App\DTO\AiResponseDTO;

class MockAiProvider implements AiProviderInterface
{
    public function getName(): string
    {
        return 'mock';
    }

    public function process(AiPromptDTO $prompt): AiResponseDTO
    {
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
