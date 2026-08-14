<?php

namespace Tests\Unit;

use App\Events\AnalysisQueued;
use App\Jobs\ProcessScreenAnalysisJob;
use App\Services\Screen\ContinuousMonitorService;
use App\Services\Screen\ScreenMonitorService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ContinuousMonitorServiceTest extends TestCase
{
    private ContinuousMonitorService $service;
    private ScreenMonitorService $monitor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = $this->app->make(ContinuousMonitorService::class);
        $this->monitor = $this->app->make(ScreenMonitorService::class);
    }

    public function test_continuous_monitor_does_nothing_when_stopped(): void
    {
        $this->monitor->stop();

        $capture = $this->service->runCycle($this->monitor);

        $this->assertNull($capture);
        $stats = $this->service->getStats();
        $this->assertEquals(0, $stats['cycles_count']);
    }

    public function test_continuous_monitor_executes_cycle_and_queues_job_when_monitoring(): void
    {
        Queue::fake();
        Event::fake();

        $this->monitor->start();

        $capture = $this->service->runCycle($this->monitor);

        $this->assertNotNull($capture);
        $stats = $this->service->getStats();
        $this->assertEquals(1, $stats['cycles_count']);
        $this->assertEquals(1, $stats['captures_count']);

        Queue::assertPushed(ProcessScreenAnalysisJob::class);
        Event::assertDispatched(AnalysisQueued::class);

        $this->monitor->stop();
    }
}
