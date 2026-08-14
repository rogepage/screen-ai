<?php

namespace Tests\Feature;

use App\Events\Native\AnalyzeNowShortcutPressed;
use App\Events\Native\ToggleMonitorShortcutPressed;
use App\Events\Native\TogglePauseShortcutPressed;
use App\Services\Screen\ScreenMonitorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NativeIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_overlay_view_renders_successfully(): void
    {
        $response = $this->get('/overlay');
        $response->assertStatus(200);
        $response->assertSee('Screen AI Overlay');
    }

    public function test_native_status_endpoint_returns_json(): void
    {
        $response = $this->getJson('/api/native/status');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'monitor_state',
            'overlay_open',
            'overlay_enabled',
            'shortcuts_enabled',
            'shortcuts',
        ]);
    }

    public function test_toggle_overlay_endpoint_works(): void
    {
        $response = $this->postJson('/api/native/overlay/toggle');
        $response->assertStatus(200);
        $response->assertJsonStructure(['message', 'overlay_open']);
    }

    public function test_shortcut_events_trigger_native_handlers(): void
    {
        $monitor = $this->app->make(ScreenMonitorService::class);

        // 1. Disparar ToggleMonitorShortcutPressed
        event(new ToggleMonitorShortcutPressed);
        $this->assertEquals('monitoring', $monitor->getState()->value);

        // 2. Disparar TogglePauseShortcutPressed
        event(new TogglePauseShortcutPressed);
        $this->assertEquals('paused', $monitor->getState()->value);

        // 3. Disparar AnalyzeNowShortcutPressed
        event(new AnalyzeNowShortcutPressed);
        // Deve executar sem lançar exceções
        $this->assertTrue(true);
    }
}
