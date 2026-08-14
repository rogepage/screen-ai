<?php

namespace App\DTO;

readonly class CaptureResultDTO
{
    public function __construct(
        public string $filePath,
        public string $hash,
        public int $timestamp,
        public bool $hasChanged,
        public array $dimensions = [],
    ) {}

    public function toArray(): array
    {
        return [
            'file_path' => $this->filePath,
            'hash' => $this->hash,
            'timestamp' => $this->timestamp,
            'has_changed' => $this->hasChanged,
            'dimensions' => $this->dimensions,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            filePath: $data['file_path'] ?? '',
            hash: $data['hash'] ?? '',
            timestamp: (int) ($data['timestamp'] ?? time()),
            hasChanged: (bool) ($data['has_changed'] ?? false),
            dimensions: (array) ($data['dimensions'] ?? []),
        );
    }
}
