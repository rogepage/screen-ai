<?php

namespace Tests\Feature;

use App\DTO\AiPromptDTO;
use App\DTO\AiResponseDTO;
use App\DTO\CaptureResultDTO;
use App\DTO\ProcessedImageDTO;
use App\Enums\ActionType;
use App\Events\AnalysisCompleted;
use App\Events\AnalysisStarted;
use App\Services\AI\AiManagerService;
use App\Services\Screen\ImageProcessorInterface;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AiManagerIntegrationTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempDir = storage_path('framework/testing/ai_integration_test');

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

    public function test_full_pipeline_from_processed_image_to_ai_response_dto(): void
    {
        Event::fake([AnalysisStarted::class, AnalysisCompleted::class]);

        // 1. Criar uma imagem capturada
        $srcPath = $this->tempDir . '/captured.png';
        $img = imagecreatetruecolor(640, 480);
        $red = imagecolorallocate($img, 200, 0, 0);
        imagefill($img, 0, 0, $red);
        imagepng($img, $srcPath);

        $capture = new CaptureResultDTO(
            filePath: $srcPath,
            hash: md5_file($srcPath),
            timestamp: time(),
            hasChanged: true,
            dimensions: ['x' => 0, 'y' => 0, 'width' => 640, 'height' => 480]
        );

        // 2. Processar a imagem com o ImageProcessor (Fase 6)
        $processor = $this->app->make(ImageProcessorInterface::class);
        $processedImageDTO = $processor->process($capture);

        $this->assertInstanceOf(ProcessedImageDTO::class, $processedImageDTO);

        // 3. Montar o AiPromptDTO utilizando a imagem processada
        $prompt = new AiPromptDTO(
            imagePath: $processedImageDTO->processedPath,
            actionType: ActionType::INTERPRET,
            userInstruction: 'Explique o conteúdo visual da tela'
        );

        // 4. Executar o AiManagerService (Fase 7)
        $aiManager = $this->app->make(AiManagerService::class);
        $response = $aiManager->analyze($prompt, 'mock');

        // 5. Validações do resultado final
        $this->assertInstanceOf(AiResponseDTO::class, $response);
        $this->assertTrue($response->success);
        $this->assertEquals('mock', $response->provider);
        $this->assertNotEmpty($response->text);

        Event::assertDispatched(AnalysisStarted::class);
        Event::assertDispatched(AnalysisCompleted::class);

        // Limpeza do temporário
        if (File::exists($processedImageDTO->processedPath)) {
            File::delete($processedImageDTO->processedPath);
        }
    }
}
