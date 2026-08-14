<?php

namespace Tests\Feature;

use App\DTO\CaptureResultDTO;
use App\Enums\ActionType;
use App\Services\AI\AnalysisService;
use App\Services\Screen\ImageProcessorInterface;
use App\Services\Screen\ScreenMonitorService;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AnalysisOrchestrationTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempDir = storage_path('framework/testing/analysis_orchestration_test');

        if (! File::exists($this->tempDir)) {
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

    public function test_complete_orchestration_pipeline_via_analysis_service(): void
    {
        // 1. Criar um frame capturado
        $srcPath = $this->tempDir.'/frame.png';
        $img = imagecreatetruecolor(400, 300);
        $yellow = imagecolorallocate($img, 255, 255, 0);
        imagefill($img, 0, 0, $yellow);
        imagepng($img, $srcPath);

        $capture = new CaptureResultDTO(
            filePath: $srcPath,
            hash: md5_file($srcPath),
            timestamp: time(),
            hasChanged: true,
            dimensions: ['x' => 0, 'y' => 0, 'width' => 400, 'height' => 300]
        );

        // 2. Processar a imagem
        $processor = $this->app->make(ImageProcessorInterface::class);
        $processedImage = $processor->process($capture);

        // 3. Executar o AnalysisService
        $analysisService = $this->app->make(AnalysisService::class);
        $response = $analysisService->analyze(
            image: $processedImage,
            action: ActionType::TRANSLATE,
            userInstruction: null,
            language: 'pt-BR',
            providerName: 'mock'
        );

        // 4. Validações
        $this->assertTrue($response->success);
        $this->assertEquals('mock', $response->provider);
        $this->assertNotEmpty($response->text);

        // Limpeza
        if (File::exists($processedImage->processedPath)) {
            File::delete($processedImage->processedPath);
        }
    }

    public function test_analyze_endpoint_orchestrates_with_last_captured_frame(): void
    {
        // Registrar uma captura no ScreenMonitorService
        $srcPath = $this->tempDir.'/last_frame.png';
        $img = imagecreatetruecolor(400, 300);
        $cyan = imagecolorallocate($img, 0, 255, 255);
        imagefill($img, 0, 0, $cyan);
        imagepng($img, $srcPath);

        $capture = new CaptureResultDTO(
            filePath: $srcPath,
            hash: md5_file($srcPath),
            timestamp: time(),
            hasChanged: true
        );

        $monitor = $this->app->make(ScreenMonitorService::class);
        $monitor->processCapture($capture);

        // Executar o endpoint HTTP /api/ai/analyze
        $response = $this->postJson('/api/ai/analyze', [
            'action_type' => 'explain',
            'user_instruction' => 'Explique a cor desta imagem.',
            'language' => 'pt-BR',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'action_type',
            'response' => [
                'text',
                'success',
                'provider',
                'model',
            ],
        ]);
        $this->assertEquals('explain', $response->json('action_type'));
    }
}
