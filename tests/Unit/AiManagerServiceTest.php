<?php

namespace Tests\Unit;

use App\DTO\AiPromptDTO;
use App\Enums\ActionType;
use App\Events\AnalysisCompleted;
use App\Events\AnalysisFailed;
use App\Events\AnalysisStarted;
use App\Services\AI\AiManagerService;
use App\Services\AI\MockAiProvider;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AiManagerServiceTest extends TestCase
{
    public function test_resolves_default_mock_provider(): void
    {
        $mock = new MockAiProvider;
        $manager = new AiManagerService($mock);

        $provider = $manager->getProvider('mock');
        $this->assertEquals('mock', $provider->getName());
    }

    public function test_analyze_dispatches_started_and_completed_events(): void
    {
        Event::fake([AnalysisStarted::class, AnalysisCompleted::class, AnalysisFailed::class]);

        $mock = new MockAiProvider;
        $manager = new AiManagerService($mock);

        $prompt = new AiPromptDTO(actionType: ActionType::INTERPRET, userInstruction: 'Teste de análise');
        $response = $manager->analyze($prompt, 'mock');

        $this->assertTrue($response->success);
        $this->assertEquals('mock', $response->provider);

        Event::assertDispatched(AnalysisStarted::class);
        Event::assertDispatched(AnalysisCompleted::class);
        Event::assertNotDispatched(AnalysisFailed::class);
    }

    public function test_analyze_dispatches_failed_event_when_provider_fails(): void
    {
        Event::fake([AnalysisStarted::class, AnalysisCompleted::class, AnalysisFailed::class]);

        $mock = new MockAiProvider(shouldFail: true, simulatedErrorMessage: 'Simulação de erro na IA');
        $manager = new AiManagerService($mock);

        $prompt = new AiPromptDTO(actionType: ActionType::ANSWER);
        $response = $manager->analyze($prompt, 'mock');

        $this->assertFalse($response->success);
        $this->assertEquals('Simulação de erro na IA', $response->errorMessage);

        Event::assertDispatched(AnalysisStarted::class);
        Event::assertDispatched(AnalysisFailed::class);
        Event::assertNotDispatched(AnalysisCompleted::class);
    }
}
