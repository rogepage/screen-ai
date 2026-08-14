<?php

namespace Tests\Feature;

use App\DTO\CaptureResultDTO;
use App\DTO\ProcessedImageDTO;
use App\Services\Screen\ImageProcessorInterface;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ImageProcessorIntegrationTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempDir = storage_path('framework/testing/integration_image_test');

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

    public function test_pipeline_from_capture_result_to_processed_image_dto(): void
    {
        // 1. Simular uma imagem capturada pela Fase 4/5
        $srcPath = $this->tempDir . '/captured_frame.png';
        $img = imagecreatetruecolor(1280, 720);
        $green = imagecolorallocate($img, 0, 200, 0);
        imagefill($img, 0, 0, $green);
        imagepng($img, $srcPath);

        $captureResult = new CaptureResultDTO(
            filePath: $srcPath,
            hash: md5_file($srcPath),
            timestamp: time(),
            hasChanged: true,
            dimensions: ['x' => 0, 'y' => 0, 'width' => 1280, 'height' => 720]
        );

        // 2. Resolver ImageProcessorInterface pelo Container do Laravel
        $processor = $this->app->make(ImageProcessorInterface::class);

        // 3. Processar a captura
        $processedDTO = $processor->process($captureResult);

        // 4. Asserções de conformidade para a futura camada de IA (Fase 7)
        $this->assertInstanceOf(ProcessedImageDTO::class, $processedDTO);
        $this->assertFileExists($processedDTO->processedPath);
        $this->assertEquals(1280, $processedDTO->width);
        $this->assertEquals(720, $processedDTO->height);
        $this->assertNotEmpty($processedDTO->mimeType);
        $this->assertGreaterThan(0, $processedDTO->fileSize);
        $this->assertGreaterThanOrEqual(0, $processedDTO->processingTimeMs);

        // Limpeza após o teste
        if (File::exists($processedDTO->processedPath)) {
            File::delete($processedDTO->processedPath);
        }
    }
}
