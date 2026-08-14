<?php

namespace Tests\Unit;

use App\DTO\AiResponseDTO;
use App\DTO\ProcessedImageDTO;
use App\Enums\ActionType;
use App\Services\AI\AnalysisService;
use App\Services\AI\DefaultPromptBuilder;
use App\Services\AI\AiManagerService;
use App\Services\AI\MockAiProvider;
use Tests\TestCase;

class AnalysisServiceTest extends TestCase
{
    public function test_analysis_service_orchestrates_all_action_types(): void
    {
        $mock = new MockAiProvider();
        $aiManager = new AiManagerService($mock);
        $promptBuilder = new DefaultPromptBuilder('pt-BR');
        $service = new AnalysisService($promptBuilder, $aiManager);

        $imageDTO = new ProcessedImageDTO(
            originalPath: '/tmp/orig.png',
            processedPath: '/tmp/proc.png',
            mimeType: 'image/png',
            width: 800,
            height: 600,
            fileSize: 1024,
            format: 'png',
            processingTimeMs: 12.5
        );

        foreach (ActionType::cases() as $action) {
            $response = $service->analyze(
                image: $imageDTO,
                action: $action,
                userInstruction: 'Instrução teste',
                language: 'pt-BR',
                providerName: 'mock'
            );

            $this->assertInstanceOf(AiResponseDTO::class, $response);
            $this->assertTrue($response->success);
            $this->assertEquals('mock', $response->provider);
        }
    }
}
