<?php

namespace App\DTO;

readonly class ScreenRegionDTO
{
    public function __construct(
        public int $x,
        public int $y,
        public int $width,
        public int $height,
        public ?int $displayId = null,
    ) {}

    public function toArray(): array
    {
        return [
            'x' => $this->x,
            'y' => $this->y,
            'width' => $this->width,
            'height' => $this->height,
            'display_id' => $this->displayId,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            x: (int) ($data['x'] ?? 0),
            y: (int) ($data['y'] ?? 0),
            width: (int) ($data['width'] ?? 0),
            height: (int) ($data['height'] ?? 0),
            displayId: isset($data['display_id']) ? (int) $data['display_id'] : null,
        );
    }
}
