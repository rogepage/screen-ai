<?php

namespace Tests\Feature;

use App\Services\Screen\ContinuousMonitorService;
use App\Services\Screen\ScreenMonitorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class LongRunStressTest extends TestCase
{
    use RefreshDatabase;

    public function test_long_run_stress_100_cycles_stability(): void
    {
        Queue::fake();

        /** @var ScreenMonitorService $monitor */
        $monitor = $this->app->make(ScreenMonitorService::class);
        /** @var ContinuousMonitorService $continuousService */
        $continuousService = $this->app->make(ContinuousMonitorService::class);

        $monitor->start();

        $initialMemory = memory_get_usage();

        for ($i = 1; $i <= 100; $i++) {
            $capture = $continuousService->runCycle($monitor);
            $this->assertNotNull($capture, "Falha no ciclo #{$i}");
        }

        $finalMemory = memory_get_usage();
        $memoryDeltaMb = ($finalMemory - $initialMemory) / (1024 * 1024);

        $stats = $continuousService->getStats();
        $this->assertEquals(100, $stats['cycles_count']);
        $this->assertEquals(100, $stats['captures_count']);

        // O delta de memória após 100 ciclos mockados deve ser modesto (< 15MB)
        $this->assertLessThan(15, $memoryDeltaMb);

        $monitor->stop();
    }
}
