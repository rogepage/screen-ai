<?php

namespace Tests\Unit;

use App\DTO\AiResponseDTO;
use App\DTO\ProcessedImageDTO;
use App\Enums\ActionType;
use App\Repositories\AnalysisHistoryRepositoryInterface;
use App\Services\AI\AiManagerService;
use App\Services\AI\AnalysisHistoryService;
use App\Services\AI\AnalysisService;
use App\Services\AI\DefaultPromptBuilder;
use App\Services\AI\MockAiProvider;
use Exception;
use Tests\TestCase;

class AnalysisHistoryResilienceTest extends TestCase
{
    public function test_analysis_service_delivers_ai_response_even_if_database_fails(): void
    {
        // 1. Criar um Mock do Repository que lança exceção simulando queda/bloqueio do banco
        $failingRepository = $this->createMock(AnalysisHistoryRepositoryInterface::class);
        $failingRepository->method('create')
            ->willThrowException(new Exception('Simulated Database Connection Loss or Disk Write Error'));

        $historyService = new AnalysisHistoryService($failingRepository);

        // 2. Provedor de IA com resposta de sucesso
        $mockAiProvider = new MockAiProvider;
        $aiManager = new AiManagerService($mockAiProvider);
        $promptBuilder = new DefaultPromptBuilder('pt-BR');

        $analysisService = new AnalysisService($promptBuilder, $aiManager, $historyService);

        $imageDTO = new ProcessedImageDTO(
            originalPath: '/tmp/test_orig.png',
            processedPath: '/tmp/test_proc.png',
            mimeType: 'image/png',
            width: 800,
            height: 600,
            fileSize: 2048,
            format: 'png',
            processingTimeMs: 15.0
        );

        // 3. Executar a análise
        $response = $analysisService->analyze($imageDTO, ActionType::INTERPRET);

        // 4. A resposta da IA deve continuar sendo entregue normalmente ao usuário
        $this->assertInstanceOf(AiResponseDTO::class, $response);
        $this->assertTrue($response->success);
        $this->assertEquals('mock', $response->provider);
        $this->assertStringContainsString('Ação \'interpret\' processada com sucesso', $response->text);
    }
}
