<?php

namespace App\Services\AI;

use App\DTO\AiPromptDTO;
use App\DTO\AiResponseDTO;
use App\DTO\ProcessedImageDTO;
use App\Enums\ActionType;
use Illuminate\Support\Facades\Log;
use Throwable;

class AnalysisService
{
    public function __construct(
        private readonly PromptBuilderInterface $promptBuilder,
        private readonly AiManagerService $aiManager,
        private readonly ?AnalysisHistoryService $historyService = null
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

        $response = $this->aiManager->analyze($promptDTO, $providerName);

        // Registro de histórico desacoplado e resiliente (Regra 10 & 20)
        try {
            $history = $this->historyService ?? app(AnalysisHistoryService::class);
            $history->recordAnalysis($promptDTO, $response);
        } catch (Throwable $e) {
            Log::error('Falha ao persistir histórico de análise no banco de dados', [
                'error' => $e->getMessage(),
            ]);
        }

        return $response;
    }
}
