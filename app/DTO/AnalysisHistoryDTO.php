<?php

namespace App\DTO;

use App\Enums\ActionType;
use App\Enums\AnalysisStatus;
use App\Models\AnalysisHistory;

readonly class AnalysisHistoryDTO
{
    public function __construct(
        public ?int $id = null,
        public ActionType $actionType = ActionType::INTERPRET,
        public string $provider = 'mock',
        public ?string $model = null,
        public AnalysisStatus $status = AnalysisStatus::SUCCESS,
        public ?string $responseText = null,
        public ?string $errorMessage = null,
        public ?float $durationMs = null,
        public ?string $userInstruction = null,
        public ?string $language = null,
        public ?string $createdAt = null
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'action_type' => $this->actionType->value,
            'provider' => $this->provider,
            'model' => $this->model,
            'status' => $this->status->value,
            'response_text' => $this->responseText,
            'error_message' => $this->errorMessage,
            'duration_ms' => $this->durationMs,
            'user_instruction' => $this->userInstruction,
            'language' => $this->language,
            'created_at' => $this->createdAt,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            actionType: isset($data['action_type'])
                ? ($data['action_type'] instanceof ActionType ? $data['action_type'] : ActionType::from($data['action_type']))
                : ActionType::INTERPRET,
            provider: $data['provider'] ?? 'mock',
            model: $data['model'] ?? null,
            status: isset($data['status'])
                ? ($data['status'] instanceof AnalysisStatus ? $data['status'] : AnalysisStatus::from($data['status']))
                : AnalysisStatus::SUCCESS,
            responseText: $data['response_text'] ?? null,
            errorMessage: $data['error_message'] ?? null,
            durationMs: isset($data['duration_ms']) ? (float) $data['duration_ms'] : null,
            userInstruction: $data['user_instruction'] ?? null,
            language: $data['language'] ?? null,
            createdAt: $data['created_at'] ?? null
        );
    }

    public static function fromModel(AnalysisHistory $model): self
    {
        return new self(
            id: $model->id,
            actionType: $model->action_type instanceof ActionType ? $model->action_type : ActionType::from($model->action_type),
            provider: $model->provider,
            model: $model->model,
            status: $model->status instanceof AnalysisStatus ? $model->status : AnalysisStatus::from($model->status),
            responseText: $model->response_text,
            errorMessage: $model->error_message,
            durationMs: $model->duration_ms !== null ? (float) $model->duration_ms : null,
            userInstruction: $model->user_instruction,
            language: $model->language,
            createdAt: $model->created_at?->toIso8601String()
        );
    }
}
