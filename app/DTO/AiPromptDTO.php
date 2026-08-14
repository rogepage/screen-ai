<?php

namespace App\DTO;

use App\Enums\ActionType;

readonly class AiPromptDTO
{
    public function __construct(
        public ?string $imagePath = null,
        public ActionType $actionType = ActionType::INTERPRET,
        public ?string $userInstruction = null,
        public ?string $systemPrompt = null,
        public array $options = [],
    ) {}

    public function toArray(): array
    {
        return [
            'image_path' => $this->imagePath,
            'action_type' => $this->actionType->value,
            'user_instruction' => $this->userInstruction,
            'system_prompt' => $this->systemPrompt,
            'options' => $this->options,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            imagePath: $data['image_path'] ?? null,
            actionType: isset($data['action_type']) ? ActionType::from($data['action_type']) : ActionType::INTERPRET,
            userInstruction: $data['user_instruction'] ?? null,
            systemPrompt: $data['system_prompt'] ?? null,
            options: (array) ($data['options'] ?? []),
        );
    }
}
