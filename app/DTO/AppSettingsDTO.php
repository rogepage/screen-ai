<?php

namespace App\DTO;

use App\Enums\ActionType;

readonly class AppSettingsDTO
{
    public function __construct(
        public string $uiLanguage = 'pt-BR',
        public string $responseLanguage = 'pt-BR',
        public bool $autoStart = false,
        public bool $overlayEnabled = true,
        public string $overlayPosition = 'bottom-right',
        public int $intervalMs = 1000,
        public int $debounceMs = 500,
        public float $changeThreshold = 0.05,
        public string $aiProvider = 'mock',
        public string $aiModel = 'gemini-1.5-flash',
        public int $aiTimeout = 30,
        public ActionType $defaultAction = ActionType::INTERPRET,
        public string $historyRetention = 'always',
        public bool $onboardingCompleted = false,
        public ?int $selectedDisplayId = 1,
    ) {}

    public function toArray(): array
    {
        return [
            'ui_language' => $this->uiLanguage,
            'response_language' => $this->responseLanguage,
            'auto_start' => $this->autoStart,
            'overlay_enabled' => $this->overlayEnabled,
            'overlay_position' => $this->overlayPosition,
            'interval_ms' => $this->intervalMs,
            'debounce_ms' => $this->debounceMs,
            'change_threshold' => $this->changeThreshold,
            'ai_provider' => $this->aiProvider,
            'ai_model' => $this->aiModel,
            'ai_timeout' => $this->aiTimeout,
            'default_action' => $this->defaultAction->value,
            'history_retention' => $this->historyRetention,
            'onboarding_completed' => $this->onboardingCompleted,
            'selected_display_id' => $this->selectedDisplayId,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            uiLanguage: $data['ui_language'] ?? 'pt-BR',
            responseLanguage: $data['response_language'] ?? 'pt-BR',
            autoStart: (bool) ($data['auto_start'] ?? false),
            overlayEnabled: (bool) ($data['overlay_enabled'] ?? true),
            overlayPosition: $data['overlay_position'] ?? 'bottom-right',
            intervalMs: (int) ($data['interval_ms'] ?? 1000),
            debounceMs: (int) ($data['debounce_ms'] ?? 500),
            changeThreshold: (float) ($data['change_threshold'] ?? 0.05),
            aiProvider: $data['ai_provider'] ?? 'mock',
            aiModel: $data['ai_model'] ?? 'gemini-1.5-flash',
            aiTimeout: (int) ($data['ai_timeout'] ?? 30),
            defaultAction: isset($data['default_action'])
                ? ($data['default_action'] instanceof ActionType ? $data['default_action'] : (ActionType::tryFrom($data['default_action']) ?? ActionType::INTERPRET))
                : ActionType::INTERPRET,
            historyRetention: $data['history_retention'] ?? 'always',
            onboardingCompleted: (bool) ($data['onboarding_completed'] ?? false),
            selectedDisplayId: isset($data['selected_display_id']) ? (int) $data['selected_display_id'] : 1
        );
    }
}
