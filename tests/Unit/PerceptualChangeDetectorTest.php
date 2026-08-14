<?php

namespace Tests\Unit;

use App\Services\Screen\PerceptualChangeDetector;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class PerceptualChangeDetectorTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempDir = storage_path('framework/testing/perceptual_test');

        if (!File::exists($this->tempDir)) {
            File::makeDirectory($this->tempDir, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        if (File::exists($this->tempDir)) {
            File::deleteDirectory($this->tempDir);
        }
        parent::tearDown();
    }

    private function createSolidImage(string $filename, int $w, int $h, int $r, int $g, int $b): string
    {
        $filePath = $this->tempDir . '/' . $filename;
        $img = imagecreatetruecolor($w, $h);
        $color = imagecolorallocate($img, $r, $g, $b);
        imagefill($img, 0, 0, $color);
        imagepng($img, $filePath);
        return $filePath;
    }

    private function createPartialImage(string $filename, int $w, int $h, float $changePercentage): string
    {
        $filePath = $this->tempDir . '/' . $filename;
        $img = imagecreatetruecolor($w, $h);
        $black = imagecolorallocate($img, 0, 0, 0);
        $white = imagecolorallocate($img, 255, 255, 255);
        imagefill($img, 0, 0, $black);

        $rectWidth = (int) ($w * $changePercentage);
        if ($rectWidth > 0) {
            imagefilledrectangle($img, 0, 0, $rectWidth - 1, $h - 1, $white);
        }

        imagepng($img, $filePath);
        return $filePath;
    }

    public function test_returns_true_for_first_capture_without_previous_image(): void
    {
        $detector = new PerceptualChangeDetector(0.05);
        $current = $this->createSolidImage('current1.png', 100, 100, 255, 0, 0);

        $this->assertTrue($detector->hasChanged($current, null));
        $this->assertTrue($detector->hasChanged($current, ''));
        $this->assertTrue($detector->hasChanged($current, '/non/existent/path.png'));
    }

    public function test_returns_false_for_identical_images(): void
    {
        $detector = new PerceptualChangeDetector(0.05);
        $img1 = $this->createSolidImage('img1.png', 100, 100, 128, 128, 128);
        $img2 = $this->createSolidImage('img2.png', 100, 100, 128, 128, 128);

        $this->assertFalse($detector->hasChanged($img1, $img2));
    }

    public function test_returns_true_for_significantly_different_images(): void
    {
        $detector = new PerceptualChangeDetector(0.05);
        $imgBlack = $this->createSolidImage('black.png', 100, 100, 0, 0, 0);
        $imgWhite = $this->createSolidImage('white.png', 100, 100, 255, 255, 255);

        $this->assertTrue($detector->hasChanged($imgWhite, $imgBlack));
    }

    public function test_returns_false_for_minor_difference_below_threshold(): void
    {
        // Change of ~1% of pixels with threshold 0.05
        $detector = new PerceptualChangeDetector(0.05);
        $imgBase = $this->createPartialImage('base.png', 200, 200, 0.0);
        $imgMinor = $this->createPartialImage('minor.png', 200, 200, 0.01);

        $this->assertFalse($detector->hasChanged($imgMinor, $imgBase));
    }

    public function test_returns_true_for_difference_above_threshold(): void
    {
        // Change of ~20% of pixels with threshold 0.05
        $detector = new PerceptualChangeDetector(0.05);
        $imgBase = $this->createPartialImage('base.png', 200, 200, 0.0);
        $imgMajor = $this->createPartialImage('major.png', 200, 200, 0.20);

        $this->assertTrue($detector->hasChanged($imgMajor, $imgBase));
    }

    public function test_handles_non_existent_or_invalid_file_gracefully(): void
    {
        $detector = new PerceptualChangeDetector(0.05);
        $validImg = $this->createSolidImage('valid.png', 50, 50, 0, 255, 0);

        $invalidFile = $this->tempDir . '/corrupt.png';
        File::put($invalidFile, 'not an image data');

        // Current image doesn't exist or is invalid
        $this->assertFalse($detector->hasChanged('/invalid/path.png', $validImg));
        $this->assertFalse($detector->hasChanged($invalidFile, $validImg));
    }

    public function test_supports_custom_configurable_threshold(): void
    {
        $imgBase = $this->createPartialImage('base.png', 100, 100, 0.0);
        $imgMedium = $this->createPartialImage('med.png', 100, 100, 0.10);

        // High threshold (0.25): 10% change should return false
        $strictDetector = new PerceptualChangeDetector(0.25);
        $this->assertFalse($strictDetector->hasChanged($imgMedium, $imgBase));

        // Sensitive threshold (0.02): 10% change should return true
        $sensitiveDetector = new PerceptualChangeDetector(0.02);
        $this->assertTrue($sensitiveDetector->hasChanged($imgMedium, $imgBase));
    }

    public function test_supports_different_image_dimensions(): void
    {
        $detector = new PerceptualChangeDetector(0.05);
        $imgSmall = $this->createSolidImage('small.png', 50, 50, 255, 255, 255);
        $imgLarge = $this->createSolidImage('large.png', 300, 300, 255, 255, 255);

        // Identical color content across different sizes -> false change
        $this->assertFalse($detector->hasChanged($imgLarge, $imgSmall));
    }
}
