<?php

namespace Tests\Unit;

use App\DTO\AiPromptDTO;
use App\DTO\AiResponseDTO;
use App\Enums\ActionType;
use App\Enums\AnalysisStatus;
use App\Repositories\EloquentAnalysisHistoryRepository;
use App\Services\AI\AnalysisHistoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalysisHistoryServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_records_successful_analysis(): void
    {
        $repo = new EloquentAnalysisHistoryRepository();
        $service = new AnalysisHistoryService($repo);

        $prompt = new AiPromptDTO(
            actionType: ActionType::INTERPRET,
            userInstruction: 'Instrução do usuário'
        );

        $response = new AiResponseDTO(
            text: 'Resposta válida da IA',
            success: true,
            provider: 'mock',
            model: 'mock-v1',
            metadata: ['duration_ms' => 120.0]
        );

        $history = $service->recordAnalysis($prompt, $response);

        $this->assertEquals(AnalysisStatus::SUCCESS, $history->status);
        $this->assertEquals('Resposta válida da IA', $history->responseText);
        $this->assertEquals(120.0, $history->durationMs);
        $this->assertEquals('Instrução do usuário', $history->userInstruction);
    }

    public function test_records_failed_analysis(): void
    {
        $repo = new EloquentAnalysisHistoryRepository();
        $service = new AnalysisHistoryService($repo);

        $prompt = new AiPromptDTO(actionType: ActionType::ANSWER);
        $response = new AiResponseDTO(
            text: '',
            success: false,
            provider: 'gemini',
            model: 'gemini-1.5-flash',
            errorMessage: 'Timeout na API',
            metadata: ['duration_ms' => 30000.0]
        );

        $history = $service->recordAnalysis($prompt, $response);

        $this->assertEquals(AnalysisStatus::FAILED, $history->status);
        $this->assertEquals('Timeout na API', $history->errorMessage);
        $this->assertNull($history->responseText);
    }
}
