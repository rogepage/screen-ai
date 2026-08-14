<?php

namespace App\Repositories;

use App\DTO\AnalysisHistoryDTO;
use App\Enums\ActionType;
use App\Enums\AnalysisStatus;

interface AnalysisHistoryRepositoryInterface
{
    public function create(AnalysisHistoryDTO $dto): AnalysisHistoryDTO;

    public function findById(int $id): ?AnalysisHistoryDTO;

    /**
     * @return array{
     *     items: array<AnalysisHistoryDTO>,
     *     total: int,
     *     page: int,
     *     per_page: int,
     *     last_page: int
     * }
     */
    public function getPaginated(
        int $page = 1,
        int $perPage = 20,
        ?ActionType $action = null,
        ?AnalysisStatus $status = null
    ): array;

    public function delete(int $id): bool;

    public function clearAll(): bool;
}
