<?php

namespace Tests\Unit;

use App\DTO\ScreenRegionDTO;
use App\Services\Screen\MacScreenCaptureService;
use App\Services\Screen\MockScreenCaptureService;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class MacScreenCaptureServiceTest extends TestCase
{
    private string $testDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testDir = storage_path('framework/testing/captures_test');
    }

    protected function tearDown(): void
    {
        if (File::exists($this->testDir)) {
            File::deleteDirectory($this->testDir);
        }
        parent::tearDown();
    }

    public function test_mock_screen_capture_service(): void
    {
        $mock = new MockScreenCaptureService();
        $region = new ScreenRegionDTO(x: 10, y: 10, width: 100, height: 100);

        $result = $mock->captureRegion($region);

        $this->assertNotEmpty($result->filePath);
        $this->assertNotEmpty($result->hash);
        $this->assertTrue($result->hasChanged);
    }

    public function test_mac_screen_capture_creates_file_and_dto(): void
    {
        $service = new MacScreenCaptureService($this->testDir);
        $region = new ScreenRegionDTO(x: 0, y: 0, width: 200, height: 200);

        $result = $service->captureRegion($region);

        $this->assertFileExists($result->filePath);
        $this->assertNotEmpty($result->hash);
        $this->assertEquals(200, $result->dimensions['width']);
    }

    public function test_cleanup_deletes_old_files(): void
    {
        $service = new MacScreenCaptureService($this->testDir);
        $oldFile = $this->testDir . '/old_capture.png';
        File::put($oldFile, 'old content');

        // Modifica a data de modificação para 1 hora atrás (3600 segundos)
        touch($oldFile, time() - 3600);

        $service->cleanupTemporaryCaptures();

        $this->assertFileDoesNotExist($oldFile);
    }
}
