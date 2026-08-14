<?php

namespace App\Services\Screen;

class MockChangeDetector implements ChangeDetectorInterface
{
    public function __construct(
        private bool $shouldAlwaysDetectChange = true
    ) {}

    public function setShouldAlwaysDetectChange(bool $shouldDetect): void
    {
        $this->shouldAlwaysDetectChange = $shouldDetect;
    }

    public function hasChanged(string $currentImagePath, ?string $previousImagePath = null): bool
    {
        return $this->shouldAlwaysDetectChange;
    }
}
