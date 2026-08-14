<?php

namespace App\Services\Screen;

use App\DTO\CaptureResultDTO;
use App\DTO\ProcessedImageDTO;

interface ImageProcessorInterface
{
    public function process(CaptureResultDTO $capture): ProcessedImageDTO;

    public function cleanupTemporaryImages(int $ttlSeconds = 300): void;
}
