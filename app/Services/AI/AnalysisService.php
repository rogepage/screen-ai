<?php

namespace App\Services\AI;

use App\DTO\AiPromptDTO;
use App\DTO\AiResponseDTO;
use App\DTO\ProcessedImageDTO;
use App\Enums\ActionType;

class AnalysisService
{
    public function __construct(
        private readonly PromptBuilderInterface $promptBuilder,
        private readonly AiManagerService $aiManager
    ) {}

    public function analyze(
        ProcessedImageDTO $image,
        ActionType $action,
        ?string $userInstruction = null,
        ?string $language = null,
        ?string $providerName = null
    ): AiResponseDTO {
        $promptDTO = $this->promptBuilder->buildAiPromptDTO(
            imagePath: $image->processedPath,
            action: $action,
            userInstruction: $userInstruction,
            language: $language
        );

        return $this->aiManager->analyze($promptDTO, $providerName);
    }
}
