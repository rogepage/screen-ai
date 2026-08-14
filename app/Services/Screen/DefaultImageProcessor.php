<?php

namespace App\Services\Screen;

use App\DTO\CaptureResultDTO;
use App\DTO\ProcessedImageDTO;
use App\Exceptions\ImageProcessingException;
use Illuminate\Support\Facades\File;

class DefaultImageProcessor implements ImageProcessorInterface
{
    private int $maxWidth;

    private int $maxHeight;

    private int $maxFileSize;

    private string $defaultFormat;

    private string $storageDir;

    public function __construct(
        ?int $maxWidth = null,
        ?int $maxHeight = null,
        ?int $maxFileSize = null,
        ?string $defaultFormat = null,
        ?string $storageDir = null
    ) {
        $this->maxWidth = $maxWidth ?? (int) config('screen_ai.image.max_width', 2048);
        $this->maxHeight = $maxHeight ?? (int) config('screen_ai.image.max_height', 2048);
        $this->maxFileSize = $maxFileSize ?? (int) config('screen_ai.image.max_file_size', 5242880);
        $this->defaultFormat = strtolower($defaultFormat ?? config('screen_ai.image.default_format', 'png'));
        $this->storageDir = $storageDir ?? config('screen_ai.image.storage_path', storage_path('app/processed_captures'));

        if (! File::exists($this->storageDir)) {
            File::makeDirectory($this->storageDir, 0755, true);
        }
    }

    public function process(CaptureResultDTO $capture): ProcessedImageDTO
    {
        $startTime = microtime(true);

        if (! File::exists($capture->filePath) || ! is_readable($capture->filePath)) {
            throw ImageProcessingException::fileNotFound();
        }

        $content = @file_get_contents($capture->filePath);
        if ($content === false) {
            throw ImageProcessingException::invalidImageFile();
        }

        $srcImg = @imagecreatefromstring($content);
        if ($srcImg === false) {
            throw ImageProcessingException::invalidImageFile();
        }

        $origW = imagesx($srcImg);
        $origH = imagesy($srcImg);

        if ($origW <= 0 || $origH <= 0) {
            throw ImageProcessingException::invalidImageFile();
        }

        $wasResized = false;
        $finalW = $origW;
        $finalH = $origH;

        if ($origW > $this->maxWidth || $origH > $this->maxHeight) {
            $scale = min($this->maxWidth / $origW, $this->maxHeight / $origH);
            $finalW = (int) max(1, round($origW * $scale));
            $finalH = (int) max(1, round($origH * $scale));

            $resizedImg = imagecreatetruecolor($finalW, $finalH);
            if ($resizedImg !== false) {
                // Preservar transparência para PNG
                imagealphablending($resizedImg, false);
                imagesavealpha($resizedImg, true);

                imagecopyresampled($resizedImg, $srcImg, 0, 0, 0, 0, $finalW, $finalH, $origW, $origH);
                $finalImg = $resizedImg;
                $wasResized = true;
            } else {
                $finalImg = $srcImg;
            }
        } else {
            $finalImg = $srcImg;
        }

        $fileName = sprintf('proc_%d_%s.%s', time(), uniqid(), $this->defaultFormat);
        $processedPath = $this->storageDir.'/'.$fileName;

        $this->saveGdImage($finalImg, $processedPath, $this->defaultFormat);

        // Verificar limite máximo de tamanho de arquivo
        $fileSize = File::exists($processedPath) ? File::size($processedPath) : 0;

        if ($fileSize > $this->maxFileSize) {
            // Tentar otimizar convertendo para JPEG com compressão
            $jpegPath = sprintf('%s/proc_%d_%s.jpg', $this->storageDir, time(), uniqid());
            $this->saveGdImage($finalImg, $jpegPath, 'jpeg', 75);

            if (File::exists($jpegPath) && File::size($jpegPath) <= $this->maxFileSize) {
                File::delete($processedPath);
                $processedPath = $jpegPath;
                $fileSize = File::size($processedPath);
            } else {
                if (File::exists($jpegPath)) {
                    File::delete($jpegPath);
                }
                if (File::exists($processedPath)) {
                    File::delete($processedPath);
                }
                throw ImageProcessingException::exceedsMaxFileSize($this->maxFileSize);
            }
        }

        $mimeType = match (pathinfo($processedPath, PATHINFO_EXTENSION)) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            default => 'image/png',
        };

        $processingTimeMs = round((microtime(true) - $startTime) * 1000.0, 2);

        return new ProcessedImageDTO(
            originalPath: $capture->filePath,
            processedPath: $processedPath,
            mimeType: $mimeType,
            width: $finalW,
            height: $finalH,
            fileSize: $fileSize,
            format: pathinfo($processedPath, PATHINFO_EXTENSION),
            processingTimeMs: $processingTimeMs,
            metadata: [
                'was_resized' => $wasResized,
                'original_dimensions' => [
                    'width' => $origW,
                    'height' => $origH,
                ],
                'original_file_size' => File::size($capture->filePath),
            ]
        );
    }

    public function cleanupTemporaryImages(int $ttlSeconds = 300): void
    {
        if (! File::exists($this->storageDir)) {
            return;
        }

        $files = File::files($this->storageDir);
        $now = time();

        foreach ($files as $file) {
            if ($now - $file->getMTime() > $ttlSeconds) {
                File::delete($file->getPathname());
            }
        }
    }

    private function saveGdImage(mixed $image, string $outputPath, string $format, int $quality = 90): void
    {
        if (in_array(strtolower($format), ['jpg', 'jpeg'], true)) {
            imagejpeg($image, $outputPath, $quality);
        } else {
            imagepng($image, $outputPath, 6);
        }
    }
}
