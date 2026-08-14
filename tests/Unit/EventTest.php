<?php

namespace Tests\Unit;

use App\DTO\AiPromptDTO;
use App\DTO\AiResponseDTO;
use App\DTO\CaptureResultDTO;
use App\DTO\MonitorConfigDTO;
use App\Events\AnalysisCompleted;
use App\Events\AnalysisFailed;
use App\Events\AnalysisStarted;
use App\Events\MonitorPaused;
use App\Events\MonitorStarted;
use App\Events\MonitorStopped;
use App\Events\ScreenChanged;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class EventTest extends TestCase
{
    public function test_events_can_be_dispatched_and_faked(): void
    {
        Event::fake();

        $config = new MonitorConfigDTO;
        MonitorStarted::dispatch($config);
        Event::assertDispatched(MonitorStarted::class);

        MonitorPaused::dispatch('User pause');
        Event::assertDispatched(MonitorPaused::class);

        MonitorStopped::dispatch('User stop');
        Event::assertDispatched(MonitorStopped::class);

        $capture = new CaptureResultDTO('/tmp/test.png', 'hash', time(), true);
        ScreenChanged::dispatch($capture);
        Event::assertDispatched(ScreenChanged::class);

        $prompt = new AiPromptDTO;
        AnalysisStarted::dispatch($prompt);
        Event::assertDispatched(AnalysisStarted::class);

        $response = new AiResponseDTO('Ok');
        AnalysisCompleted::dispatch($response);
        Event::assertDispatched(AnalysisCompleted::class);

        AnalysisFailed::dispatch('Erro de teste');
        Event::assertDispatched(AnalysisFailed::class);
    }
}
