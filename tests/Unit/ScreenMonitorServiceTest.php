<?php

namespace Tests\Unit;

use App\DTO\MonitorConfigDTO;
use App\Enums\MonitorState;
use App\Events\MonitorPaused;
use App\Events\MonitorStarted;
use App\Events\MonitorStopped;
use App\Services\Screen\ScreenMonitorService;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ScreenMonitorServiceTest extends TestCase
{
    public function test_initial_state_is_stopped(): void
    {
        $service = new ScreenMonitorService();
        $this->assertEquals(MonitorState::STOPPED, $service->getState());
        $this->assertNull($service->getConfig());
    }

    public function test_start_transitions_state_and_dispatches_event(): void
    {
        Event::fake();

        $service = new ScreenMonitorService();
        $config = new MonitorConfigDTO(intervalMs: 1500);

        $service->start($config);

        $this->assertEquals(MonitorState::MONITORING, $service->getState());
        $this->assertEquals(1500, $service->getConfig()->intervalMs);

        Event::assertDispatched(MonitorStarted::class, function ($event) use ($config) {
            return $event->config->intervalMs === 1500;
        });
    }

    public function test_pause_and_resume_transitions_state(): void
    {
        Event::fake();

        $service = new ScreenMonitorService();
        $service->start();

        $service->pause();
        $this->assertEquals(MonitorState::PAUSED, $service->getState());
        Event::assertDispatched(MonitorPaused::class);

        $service->resume();
        $this->assertEquals(MonitorState::MONITORING, $service->getState());
    }

    public function test_stop_transitions_state_and_dispatches_event(): void
    {
        Event::fake();

        $service = new ScreenMonitorService();
        $service->start();

        $service->stop('Test reason');
        $this->assertEquals(MonitorState::STOPPED, $service->getState());

        Event::assertDispatched(MonitorStopped::class, function ($event) {
            return $event->reason === 'Test reason';
        });
    }
}
