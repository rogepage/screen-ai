<?php

namespace App\DTO;

readonly class ProcessedImageDTO
{
    public function __construct(
        public string $originalPath,
        public string $processedPath,
        public string $mimeType,
        public int $width,
        public int $height,
        public int $fileSize,
        public string $format,
        public float $processingTimeMs,
        public array $metadata = []
    ) {}

    public function toArray(): array
    {
        return [
            'original_path' => $this->originalPath,
            'processed_path' => $this->processedPath,
            'mime_type' => $this->mimeType,
            'width' => $this->width,
            'height' => $this->height,
            'file_size' => $this->fileSize,
            'format' => $this->format,
            'processing_time_ms' => $this->processingTimeMs,
            'metadata' => $this->metadata,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            originalPath: $data['original_path'] ?? '',
            processedPath: $data['processed_path'] ?? '',
            mimeType: $data['mime_type'] ?? 'image/png',
            width: (int) ($data['width'] ?? 0),
            height: (int) ($data['height'] ?? 0),
            fileSize: (int) ($data['file_size'] ?? 0),
            format: $data['format'] ?? 'png',
            processingTimeMs: (float) ($data['processing_time_ms'] ?? 0.0),
            metadata: (array) ($data['metadata'] ?? [])
        );
    }
}
