<?php

namespace App\Services\Screen;

use App\DTO\CaptureResultDTO;
use App\DTO\ScreenRegionDTO;

class MockScreenCaptureService implements ScreenCaptureInterface
{
    public function captureRegion(ScreenRegionDTO $region): CaptureResultDTO
    {
        return new CaptureResultDTO(
            filePath: '/tmp/mock_capture.png',
            hash: md5('mock_frame_' . time()),
            timestamp: time(),
            hasChanged: true,
            dimensions: [
                'x' => $region->x,
                'y' => $region->y,
                'width' => $region->width,
                'height' => $region->height,
            ]
        );
    }

    public function cleanupTemporaryCaptures(): void
    {
        // Mock cleanup
    }
}
