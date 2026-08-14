<?php

namespace App\Services\Screen;

use App\DTO\CaptureResultDTO;
use App\DTO\ScreenRegionDTO;
use Illuminate\Support\Facades\File;

class MockScreenCaptureService implements ScreenCaptureInterface
{
    public function captureRegion(ScreenRegionDTO $region): CaptureResultDTO
    {
        $storageDir = storage_path('framework/testing/mock_captures');
        if (!File::exists($storageDir)) {
            File::makeDirectory($storageDir, 0755, true);
        }

        $filePath = $storageDir . '/mock_capture.png';
        if (!File::exists($filePath)) {
            $img = imagecreatetruecolor(100, 100);
            $blue = imagecolorallocate($img, 0, 100, 200);
            imagefill($img, 0, 0, $blue);
            imagepng($img, $filePath);
        }

        return new CaptureResultDTO(
            filePath: $filePath,
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
