<?php

namespace App\Services\AI;

use App\DTO\AiPromptDTO;
use App\DTO\AiResponseDTO;
use App\DTO\AnalysisHistoryDTO;
use App\Enums\ActionType;
use App\Enums\AnalysisStatus;
use App\Repositories\AnalysisHistoryRepositoryInterface;

class AnalysisHistoryService
{
    public function __construct(
        private readonly AnalysisHistoryRepositoryInterface $repository
    ) {}

    public function recordAnalysis(AiPromptDTO $prompt, AiResponseDTO $response): AnalysisHistoryDTO
    {
        $status = $response->success ? AnalysisStatus::SUCCESS : AnalysisStatus::FAILED;
        $durationMs = isset($response->metadata['duration_ms']) ? (float) $response->metadata['duration_ms'] : null;
        $language = $prompt->options['language'] ?? config('screen_ai.ai.response_language', 'pt-BR');

        $dto = new AnalysisHistoryDTO(
            actionType: $prompt->actionType,
            provider: $response->provider,
            model: $response->model,
            status: $status,
            responseText: $response->success ? $response->text : null,
            errorMessage: !$response->success ? $response->errorMessage : null,
            durationMs: $durationMs,
            userInstruction: $prompt->userInstruction,
            language: $language
        );

        return $this->repository->create($dto);
    }

    /**
     * @return array{
     *     items: array<AnalysisHistoryDTO>,
     *     total: int,
     *     page: int,
     *     per_page: int,
     *     last_page: int
     * }
     */
    public function listHistory(
        int $page = 1,
        int $perPage = 20,
        ?ActionType $action = null,
        ?AnalysisStatus $status = null
    ): array {
        return $this->repository->getPaginated(
            page: max(1, $page),
            perPage: min(max(1, $perPage), 100),
            action: $action,
            status: $status
        );
    }

    public function getAnalysis(int $id): ?AnalysisHistoryDTO
    {
        return $this->repository->findById($id);
    }

    public function deleteAnalysis(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function clearHistory(): bool
    {
        return $this->repository->clearAll();
    }
}
