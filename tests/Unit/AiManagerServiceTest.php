<?php

namespace Tests\Unit;

use App\DTO\AiPromptDTO;
use App\Enums\ActionType;
use App\Events\AnalysisCompleted;
use App\Events\AnalysisStarted;
use App\Services\AI\AiManagerService;
use App\Services\AI\MockAiProvider;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AiManagerServiceTest extends TestCase
{
    public function test_resolves_mock_provider_and_processes_prompt(): void
    {
        Event::fake();

        $mockProvider = new MockAiProvider();
        $manager = new AiManagerService($mockProvider);

        $prompt = new AiPromptDTO(
            actionType: ActionType::INTERPRET,
            userInstruction: 'Instrução de Teste'
        );

        $response = $manager->analyze($prompt);

        $this->assertTrue($response->success);
        $this->assertEquals('mock', $response->provider);
        $this->assertStringContainsString('Instrução de Teste', $response->text);

        Event::assertDispatched(AnalysisStarted::class);
        Event::assertDispatched(AnalysisCompleted::class);
    }
}
