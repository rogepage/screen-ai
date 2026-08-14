<?php

namespace App\Services\Screen;

class PerceptualChangeDetector implements ChangeDetectorInterface
{
    private float $threshold;

    private int $sampleSize;

    public function __construct(?float $threshold = null, int $sampleSize = 32)
    {
        $this->threshold = $threshold ?? (float) config('screen_ai.monitor.change_threshold', 0.05);
        $this->sampleSize = $sampleSize > 0 ? $sampleSize : 32;
    }

    public function hasChanged(string $currentImagePath, ?string $previousImagePath = null): bool
    {
        // Se não houver captura anterior ou o arquivo não existir, considera como mudança válida (primeira captura)
        if ($previousImagePath === null || trim($previousImagePath) === '' || ! file_exists($previousImagePath)) {
            return true;
        }

        // Se o arquivo atual não existir, não é possível comparar
        if (! file_exists($currentImagePath)) {
            return false;
        }

        $imgCurrent = $this->loadGdImage($currentImagePath);
        $imgPrev = $this->loadGdImage($previousImagePath);

        if ($imgCurrent === null || $imgPrev === null) {
            return false;
        }

        $sampleCurrent = $this->resampleImage($imgCurrent);
        $samplePrev = $this->resampleImage($imgPrev);

        if ($sampleCurrent === null || $samplePrev === null) {
            return false;
        }

        $differenceScore = $this->calculateDifferenceScore($sampleCurrent, $samplePrev);

        return $differenceScore >= $this->threshold;
    }

    /**
     * Carrega uma imagem GD de forma segura sem lançar exceções não tratadas.
     */
    private function loadGdImage(string $path): mixed
    {
        $content = @file_get_contents($path);
        if ($content === false) {
            return null;
        }

        $image = @imagecreatefromstring($content);

        return $image !== false ? $image : null;
    }

    /**
     * Redimensiona a imagem para a grade de subamostragem normalizada.
     */
    private function resampleImage(mixed $sourceImage): mixed
    {
        $srcW = imagesx($sourceImage);
        $srcH = imagesy($sourceImage);

        if ($srcW <= 0 || $srcH <= 0) {
            return null;
        }

        $sample = imagecreatetruecolor($this->sampleSize, $this->sampleSize);
        if ($sample === false) {
            return null;
        }

        imagecopyresampled(
            $sample,
            $sourceImage,
            0, 0, 0, 0,
            $this->sampleSize, $this->sampleSize,
            $srcW, $srcH
        );

        return $sample;
    }

    /**
     * Calcula a diferença percentual normalizada (0.0 a 1.0) entre duas imagens amostradas.
     */
    private function calculateDifferenceScore(mixed $sampleCurrent, mixed $samplePrev): float
    {
        $totalPixels = $this->sampleSize * $this->sampleSize;
        $accumulatedDifference = 0.0;

        for ($x = 0; $x < $this->sampleSize; $x++) {
            for ($y = 0; $y < $this->sampleSize; $y++) {
                $rgb1 = imagecolorat($sampleCurrent, $x, $y);
                $rgb2 = imagecolorat($samplePrev, $x, $y);

                $r1 = ($rgb1 >> 16) & 0xFF;
                $g1 = ($rgb1 >> 8) & 0xFF;
                $b1 = $rgb1 & 0xFF;

                $r2 = ($rgb2 >> 16) & 0xFF;
                $g2 = ($rgb2 >> 8) & 0xFF;
                $b2 = $rgb2 & 0xFF;

                // Luminosidade normalizada (Fórmula NTSC: 0.299R + 0.587G + 0.114B)
                $lum1 = (0.299 * $r1 + 0.587 * $g1 + 0.114 * $b1) / 255.0;
                $lum2 = (0.299 * $r2 + 0.587 * $g2 + 0.114 * $b2) / 255.0;

                $accumulatedDifference += abs($lum1 - $lum2);
            }
        }

        return $accumulatedDifference / $totalPixels;
    }
}
