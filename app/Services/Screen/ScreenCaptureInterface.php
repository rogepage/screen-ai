<?php

namespace App\Services\Screen;

use App\DTO\CaptureResultDTO;
use App\DTO\ScreenRegionDTO;

interface ScreenCaptureInterface
{
    public function captureRegion(ScreenRegionDTO $region): CaptureResultDTO;

    public function cleanupTemporaryCaptures(): void;
}
