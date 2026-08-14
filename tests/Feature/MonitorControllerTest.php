<?php

namespace Tests\Feature;

use Tests\TestCase;

class MonitorControllerTest extends TestCase
{
    public function test_home_page_loads_successfully(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Screen AI — Painel de Controle (Fase 2)');
    }

    public function test_can_get_monitor_state(): void
    {
        $response = $this->getJson('/api/monitor/state');
        $response->assertStatus(200);
        $response->assertJsonStructure(['state', 'config']);
    }

    public function test_can_start_monitor(): void
    {
        $response = $this->postJson('/api/monitor/start', [
            'action_type' => 'translate',
            'interval_ms' => 2000,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['state' => 'monitoring']);
    }

    public function test_can_pause_and_stop_monitor(): void
    {
        $this->postJson('/api/monitor/start');

        $pauseResponse = $this->postJson('/api/monitor/pause');
        $pauseResponse->assertStatus(200);
        $pauseResponse->assertJson(['state' => 'paused']);

        $stopResponse = $this->postJson('/api/monitor/stop');
        $stopResponse->assertStatus(200);
        $stopResponse->assertJson(['state' => 'stopped']);
    }

    public function test_can_simulate_ai_analysis(): void
    {
        $response = $this->postJson('/api/ai/analyze', [
            'action_type' => 'explain',
            'user_instruction' => 'Explique o erro exibido',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('response.provider', 'mock');
        $response->assertJsonPath('response.success', true);
    }
}
