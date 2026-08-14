<?php

namespace App\DTO;

readonly class AiResponseDTO
{
    public function __construct(
        public string $text,
        public bool $success = true,
        public string $provider = 'mock',
        public ?string $model = null,
        public ?string $errorMessage = null,
        public array $metadata = [],
    ) {}

    public function toArray(): array
    {
        return [
            'text' => $this->text,
            'success' => $this->success,
            'provider' => $this->provider,
            'model' => $this->model,
            'error_message' => $this->errorMessage,
            'metadata' => $this->metadata,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            text: $data['text'] ?? '',
            success: (bool) ($data['success'] ?? true),
            provider: $data['provider'] ?? 'mock',
            model: $data['model'] ?? null,
            errorMessage: $data['error_message'] ?? null,
            metadata: (array) ($data['metadata'] ?? []),
        );
    }
}
