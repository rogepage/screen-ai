<?php

namespace Tests\Feature;

use App\Services\Screen\ScreenMonitorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContinuousMonitoringPipelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_pipeline_stats_endpoint_returns_json(): void
    {
        $response = $this->getJson('/api/monitor/pipeline');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'state',
            'stats' => [
                'cycles_count',
                'captures_count',
                'queued_jobs_count',
                'debounce_stats',
            ],
        ]);
    }

    public function test_execute_cycle_endpoint_runs_continuous_flow(): void
    {
        /** @var ScreenMonitorService $monitor */
        $monitor = $this->app->make(ScreenMonitorService::class);
        $monitor->start();

        $response = $this->postJson('/api/monitor/cycle');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'capture',
            'stats',
        ]);

        $monitor->stop();
    }
}
