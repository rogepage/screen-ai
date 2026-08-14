<?php

namespace Tests\Unit;

use App\DTO\ScreenRegionDTO;
use App\Enums\MonitorState;
use App\Services\Native\NativeBridgeService;
use Tests\TestCase;

class NativeBridgeServiceTest extends TestCase
{
    private NativeBridgeService $bridge;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bridge = $this->app->make(NativeBridgeService::class);
    }

    public function test_toggle_monitor_switches_state_correctly(): void
    {
        // 1. Alternar quando parado -> deve iniciar (MONITORING)
        $newState = $this->bridge->toggleMonitor();
        $this->assertEquals(MonitorState::MONITORING->value, $newState);

        // 2. Alternar quando monitorando -> deve parar (STOPPED)
        $newState = $this->bridge->toggleMonitor();
        $this->assertEquals(MonitorState::STOPPED->value, $newState);
    }

    public function test_toggle_pause_switches_state_correctly(): void
    {
        // Iniciar
        $this->bridge->toggleMonitor();

        // Pausar
        $newState = $this->bridge->togglePause();
        $this->assertEquals(MonitorState::PAUSED->value, $newState);

        // Limpar estado
        $this->bridge->toggleMonitor();
    }

    public function test_trigger_analysis_now_executes_successfully(): void
    {
        $result = $this->bridge->triggerAnalysisNow();

        $this->assertTrue($result['success']);
        $this->assertEquals('mock', $result['provider']);
        $this->assertNotEmpty($result['text']);
    }

    public function test_check_region_overlay_collision_detects_overlap_accurately(): void
    {
        $region = new ScreenRegionDTO(x: 100, y: 100, width: 400, height: 300);

        // Caso 1: Overlay colidindo com a região (dentro de x:100..500, y:100..400)
        $collidingOverlay = ['x' => 200, 'y' => 200, 'width' => 300, 'height' => 150];
        $this->assertTrue($this->bridge->checkRegionOverlayCollision($region, $collidingOverlay));

        // Caso 2: Overlay completamente fora (à direita)
        $outsideOverlay = ['x' => 600, 'y' => 100, 'width' => 300, 'height' => 150];
        $this->assertFalse($this->bridge->checkRegionOverlayCollision($region, $outsideOverlay));

        // Caso 3: Overlay completamente fora (abaixo)
        $belowOverlay = ['x' => 100, 'y' => 500, 'width' => 300, 'height' => 150];
        $this->assertFalse($this->bridge->checkRegionOverlayCollision($region, $belowOverlay));
    }

    public function test_get_native_status_returns_complete_structure(): void
    {
        $status = $this->bridge->getNativeStatus();

        $this->assertArrayHasKey('monitor_state', $status);
        $this->assertArrayHasKey('overlay_open', $status);
        $this->assertArrayHasKey('overlay_enabled', $status);
        $this->assertArrayHasKey('shortcuts_enabled', $status);
        $this->assertArrayHasKey('shortcuts', $status);
    }
}
