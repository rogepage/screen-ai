<?php

namespace App\Services\Screen;

interface ChangeDetectorInterface
{
    public function hasChanged(string $currentImagePath, ?string $previousImagePath = null): bool;
}
