<?php

namespace App\DTO;

use App\Enums\ActionType;
use App\Enums\AiProviderType;

readonly class MonitorConfigDTO
{
    public function __construct(
        public int $intervalMs = 1000,
        public ?ScreenRegionDTO $selectedRegion = null,
        public AiProviderType $aiProvider = AiProviderType::MOCK,
        public ActionType $actionType = ActionType::INTERPRET,
    ) {}

    public function toArray(): array
    {
        return [
            'interval_ms' => $this->intervalMs,
            'selected_region' => $this->selectedRegion?->toArray(),
            'ai_provider' => $this->aiProvider->value,
            'action_type' => $this->actionType->value,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            intervalMs: (int) ($data['interval_ms'] ?? 1000),
            selectedRegion: isset($data['selected_region']) ? ScreenRegionDTO::fromArray($data['selected_region']) : null,
            aiProvider: isset($data['ai_provider']) ? AiProviderType::from($data['ai_provider']) : AiProviderType::MOCK,
            actionType: isset($data['action_type']) ? ActionType::from($data['action_type']) : ActionType::INTERPRET,
        );
    }
}
