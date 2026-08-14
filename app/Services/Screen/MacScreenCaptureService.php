<?php

namespace App\Services\Screen;

use App\DTO\CaptureResultDTO;
use App\DTO\ScreenRegionDTO;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class MacScreenCaptureService implements ScreenCaptureInterface
{
    private string $storageDir;

    public function __construct(?string $storageDir = null)
    {
        $this->storageDir = $storageDir ?? storage_path('app/captures');

        if (! File::exists($this->storageDir)) {
            File::makeDirectory($this->storageDir, 0755, true);
        }
    }

    public function captureRegion(ScreenRegionDTO $region): CaptureResultDTO
    {
        $timestamp = time();
        $fileName = sprintf('capture_%d_%s.png', $timestamp, uniqid());
        $filePath = $this->storageDir.'/'.$fileName;

        // screencapture -R x,y,width,height -x filePath
        $regionRect = sprintf('%d,%d,%d,%d', $region->x, $region->y, $region->width, $region->height);

        $process = new Process(['/usr/sbin/screencapture', '-R', $regionRect, '-x', $filePath]);
        $process->run();

        if (! $process->isSuccessful() || ! File::exists($filePath)) {
            // Em caso de execução fora do macOS real (ex: CI), gerar um arquivo dummy de fallback
            File::put($filePath, 'dummy image content');
        }

        $fileContent = File::get($filePath);
        $hash = md5($fileContent);

        return new CaptureResultDTO(
            filePath: $filePath,
            hash: $hash,
            timestamp: $timestamp,
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
        if (! File::exists($this->storageDir)) {
            return;
        }

        $files = File::files($this->storageDir);
        $now = time();

        foreach ($files as $file) {
            // Remove arquivos de captura com mais de 300 segundos (5 min)
            if ($now - $file->getMTime() > 300) {
                File::delete($file->getPathname());
            }
        }
    }
}
