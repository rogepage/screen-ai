<?php

namespace Tests\Feature;

use App\Services\Screen\DesktopScreenshotWatcherService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class DesktopWatcherIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private string $testDesktopDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testDesktopDir = storage_path('framework/testing/test_desktop_'.uniqid());
        if (! File::exists($this->testDesktopDir)) {
            File::makeDirectory($this->testDesktopDir, 0755, true);
        }

        /** @var DesktopScreenshotWatcherService $watcher */
        $watcher = $this->app->make(DesktopScreenshotWatcherService::class);
        $watcher->setWatchFolder($this->testDesktopDir);
        $watcher->clearProcessedHistory();
    }

    protected function tearDown(): void
    {
        if (File::exists($this->testDesktopDir)) {
            File::deleteDirectory($this->testDesktopDir);
        }
        parent::tearDown();
    }

    public function test_get_and_update_watcher_config(): void
    {
        $response = $this->getJson('/api/monitor/watch-config');
        $response->assertStatus(200);
        $response->assertJsonStructure(['watch_folder', 'accessible']);

        $update = $this->postJson('/api/monitor/watch-config', [
            'watch_folder' => $this->testDesktopDir,
        ]);
        $update->assertStatus(200);
        $update->assertJson([
            'watch_folder' => $this->testDesktopDir,
            'accessible' => true,
        ]);
    }

    public function test_executes_watcher_cycle_and_analyzes_new_screenshot(): void
    {
        // 1. Criar uma imagem PNG válida no diretório monitorado
        $testImage = imagecreatetruecolor(100, 100);
        $bg = imagecolorallocate($testImage, 255, 0, 0);
        imagefilledrectangle($testImage, 0, 0, 100, 100, $bg);
        $filePath = $this->testDesktopDir.'/Captura de Tela 2026-08-14 às 15.00.00.png';
        imagepng($testImage, $filePath);
        imagedestroy($testImage);

        // 2. Executar o ciclo do watcher
        $response = $this->postJson('/api/monitor/watch-cycle', [
            'action' => 'explain',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'detected' => true,
        ]);
        $response->assertJsonFragment([
            'file' => 'Captura de Tela 2026-08-14 às 15.00.00.png',
            'action' => 'explain',
        ]);

        // 3. Verificar que o histórico foi registrado no banco
        $this->assertDatabaseHas('analysis_histories', [
            'action_type' => 'explain',
        ]);
    }
}
