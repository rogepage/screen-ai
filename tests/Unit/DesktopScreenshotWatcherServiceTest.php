<?php

namespace Tests\Unit;

use App\Services\Screen\DesktopScreenshotWatcherService;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class DesktopScreenshotWatcherServiceTest extends TestCase
{
    private string $testDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testDir = storage_path('framework/testing/test_desktop_'.uniqid());
        if (! File::exists($this->testDir)) {
            File::makeDirectory($this->testDir, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        if (File::exists($this->testDir)) {
            File::deleteDirectory($this->testDir);
        }
        parent::tearDown();
    }

    public function test_scans_and_detects_new_screenshots(): void
    {
        $service = new DesktopScreenshotWatcherService(
            watchFolder: $this->testDir,
            patterns: ['Captura de Tela*.png', 'Screenshot*.png']
        );

        $this->assertTrue($service->isFolderAccessible());

        // Inicialmente nenhum print novo
        $newFiles = $service->scanNewScreenshots();
        $this->assertEmpty($newFiles);

        // Criar um print simulado do macOS
        $screenshotPath = $this->testDir.'/Captura de Tela 2026-08-14 às 14.55.00.png';
        File::put($screenshotPath, 'dummy-png-content');

        // Detectar o print
        $newFiles = $service->scanNewScreenshots();
        $this->assertCount(1, $newFiles);
        $this->assertEquals($screenshotPath, $newFiles[0]);

        // Marcar como processado
        $service->markAsProcessed($screenshotPath);
        $this->assertTrue($service->isProcessed($screenshotPath));

        // Na próxima varredura, não deve retornar novamente
        $newFilesAfter = $service->scanNewScreenshots();
        $this->assertEmpty($newFilesAfter);
    }

    public function test_ignores_non_matching_files(): void
    {
        $service = new DesktopScreenshotWatcherService(
            watchFolder: $this->testDir,
            patterns: ['Captura de Tela*.png']
        );

        $otherFile = $this->testDir.'/documento.txt';
        File::put($otherFile, 'texto');

        $newFiles = $service->scanNewScreenshots();
        $this->assertEmpty($newFiles);
    }
}
