<?php

namespace App\Repositories;

use App\DTO\AnalysisHistoryDTO;
use App\Enums\ActionType;
use App\Enums\AnalysisStatus;
use App\Models\AnalysisHistory;

class EloquentAnalysisHistoryRepository implements AnalysisHistoryRepositoryInterface
{
    public function create(AnalysisHistoryDTO $dto): AnalysisHistoryDTO
    {
        $model = AnalysisHistory::create([
            'action_type' => $dto->actionType->value,
            'provider' => $dto->provider,
            'model' => $dto->model,
            'status' => $dto->status->value,
            'response_text' => $dto->responseText,
            'error_message' => $dto->errorMessage,
            'duration_ms' => $dto->durationMs,
            'user_instruction' => $dto->userInstruction,
            'language' => $dto->language,
        ]);

        return AnalysisHistoryDTO::fromModel($model);
    }

    public function findById(int $id): ?AnalysisHistoryDTO
    {
        $model = AnalysisHistory::find($id);

        return $model ? AnalysisHistoryDTO::fromModel($model) : null;
    }

    public function getPaginated(
        int $page = 1,
        int $perPage = 20,
        ?ActionType $action = null,
        ?AnalysisStatus $status = null
    ): array {
        $query = AnalysisHistory::query()->orderBy('created_at', 'desc');

        if ($action !== null) {
            $query->where('action_type', $action->value);
        }

        if ($status !== null) {
            $query->where('status', $status->value);
        }

        $paginator = $query->paginate(perPage: $perPage, page: $page);

        $items = array_map(
            fn (AnalysisHistory $model) => AnalysisHistoryDTO::fromModel($model),
            $paginator->items()
        );

        return [
            'items' => $items,
            'total' => $paginator->total(),
            'page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'last_page' => $paginator->lastPage(),
        ];
    }

    public function delete(int $id): bool
    {
        $model = AnalysisHistory::find($id);

        return $model ? (bool) $model->delete() : false;
    }

    public function clearAll(): bool
    {
        AnalysisHistory::truncate();

        return true;
    }
}
