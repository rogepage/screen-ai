<?php

namespace Tests\Unit;

use App\DTO\CaptureResultDTO;
use App\DTO\ProcessedImageDTO;
use App\Exceptions\ImageProcessingException;
use App\Services\Screen\DefaultImageProcessor;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class DefaultImageProcessorTest extends TestCase
{
    private string $tempDir;

    private string $storageDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempDir = storage_path('framework/testing/processor_test_src');
        $this->storageDir = storage_path('framework/testing/processor_test_out');

        if (! File::exists($this->tempDir)) {
            File::makeDirectory($this->tempDir, 0755, true);
        }
        if (! File::exists($this->storageDir)) {
            File::makeDirectory($this->storageDir, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        if (File::exists($this->tempDir)) {
            File::deleteDirectory($this->tempDir);
        }
        if (File::exists($this->storageDir)) {
            File::deleteDirectory($this->storageDir);
        }
        parent::tearDown();
    }

    private function createSamplePng(string $filename, int $w, int $h): string
    {
        $path = $this->tempDir.'/'.$filename;
        $img = imagecreatetruecolor($w, $h);
        $blue = imagecolorallocate($img, 0, 100, 200);
        imagefill($img, 0, 0, $blue);
        imagepng($img, $path);

        return $path;
    }

    public function test_process_valid_image_returns_processed_image_dto(): void
    {
        $srcPath = $this->createSamplePng('sample.png', 800, 600);
        $capture = new CaptureResultDTO(
            filePath: $srcPath,
            hash: 'hash123',
            timestamp: time(),
            hasChanged: true,
            dimensions: ['x' => 0, 'y' => 0, 'width' => 800, 'height' => 600]
        );

        $processor = new DefaultImageProcessor(
            maxWidth: 1920,
            maxHeight: 1080,
            maxFileSize: 5242880,
            defaultFormat: 'png',
            storageDir: $this->storageDir
        );

        $result = $processor->process($capture);

        $this->assertInstanceOf(ProcessedImageDTO::class, $result);
        $this->assertEquals($srcPath, $result->originalPath);
        $this->assertFileExists($result->processedPath);
        $this->assertEquals(800, $result->width);
        $this->assertEquals(600, $result->height);
        $this->assertEquals('image/png', $result->mimeType);
        $this->assertGreaterThan(0, $result->fileSize);
        $this->assertGreaterThanOrEqual(0, $result->processingTimeMs);
        $this->assertFalse($result->metadata['was_resized']);
    }

    public function test_throws_exception_for_non_existent_file(): void
    {
        $capture = new CaptureResultDTO(
            filePath: '/non/existent/image.png',
            hash: 'h',
            timestamp: time(),
            hasChanged: true
        );

        $processor = new DefaultImageProcessor(storageDir: $this->storageDir);

        $this->expectException(ImageProcessingException::class);
        $this->expectExceptionMessage('Arquivo de captura não encontrado.');

        $processor->process($capture);
    }

    public function test_throws_exception_for_corrupted_or_invalid_image_file(): void
    {
        $corruptPath = $this->tempDir.'/corrupt.png';
        File::put($corruptPath, 'not a real png image data');

        $capture = new CaptureResultDTO(
            filePath: $corruptPath,
            hash: 'h',
            timestamp: time(),
            hasChanged: true
        );

        $processor = new DefaultImageProcessor(storageDir: $this->storageDir);

        $this->expectException(ImageProcessingException::class);
        $this->expectExceptionMessage('Arquivo de imagem inválido ou corrompido.');

        $processor->process($capture);
    }

    public function test_image_exceeding_max_bounds_is_resized_proportionally(): void
    {
        // 4000x2000 image with max bounds 2000x2000 -> should scale to 2000x1000
        $srcPath = $this->createSamplePng('large.png', 4000, 2000);
        $capture = new CaptureResultDTO(
            filePath: $srcPath,
            hash: 'hash_large',
            timestamp: time(),
            hasChanged: true
        );

        $processor = new DefaultImageProcessor(
            maxWidth: 2000,
            maxHeight: 2000,
            storageDir: $this->storageDir
        );

        $result = $processor->process($capture);

        $this->assertEquals(2000, $result->width);
        $this->assertEquals(1000, $result->height);
        $this->assertTrue($result->metadata['was_resized']);
        $this->assertEquals(4000, $result->metadata['original_dimensions']['width']);
        $this->assertEquals(2000, $result->metadata['original_dimensions']['height']);
    }

    public function test_original_file_remains_unmodified(): void
    {
        $srcPath = $this->createSamplePng('original.png', 3000, 3000);
        $originalHashBefore = md5_file($srcPath);
        $originalSizeBefore = filesize($srcPath);

        $capture = new CaptureResultDTO(filePath: $srcPath, hash: 'h', timestamp: time(), hasChanged: true);
        $processor = new DefaultImageProcessor(maxWidth: 1000, maxHeight: 1000, storageDir: $this->storageDir);

        $result = $processor->process($capture);

        $this->assertFileExists($srcPath);
        $this->assertEquals($originalHashBefore, md5_file($srcPath));
        $this->assertEquals($originalSizeBefore, filesize($srcPath));
        $this->assertNotEquals($srcPath, $result->processedPath);
    }

    public function test_exceeds_max_file_size_throws_exception(): void
    {
        $srcPath = $this->createSamplePng('sample_size.png', 500, 500);
        $capture = new CaptureResultDTO(filePath: $srcPath, hash: 'h', timestamp: time(), hasChanged: true);

        // Define a tiny max file size (e.g. 10 bytes) that is impossible to satisfy
        $processor = new DefaultImageProcessor(
            maxFileSize: 10,
            storageDir: $this->storageDir
        );

        $this->expectException(ImageProcessingException::class);
        $this->expectExceptionMessage('A imagem processada excede o limite máximo permitido de 10 bytes.');

        $processor->process($capture);
    }

    public function test_cleanup_deletes_old_processed_files(): void
    {
        $processor = new DefaultImageProcessor(storageDir: $this->storageDir);

        $oldFile = $this->storageDir.'/proc_old.png';
        File::put($oldFile, 'dummy content');
        touch($oldFile, time() - 3600); // 1 hour ago

        $newFile = $this->storageDir.'/proc_new.png';
        File::put($newFile, 'dummy content');

        $processor->cleanupTemporaryImages(300); // 5 min TTL

        $this->assertFileDoesNotExist($oldFile);
        $this->assertFileExists($newFile);
    }
}
