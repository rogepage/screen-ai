<?php

namespace Tests\Feature;

use Tests\TestCase;

class RegionControllerTest extends TestCase
{
    public function test_can_get_initial_empty_region(): void
    {
        $response = $this->getJson('/api/monitor/region');
        $response->assertStatus(200);
        $response->assertJson(['region' => null]);
    }

    public function test_can_set_region_via_api(): void
    {
        $response = $this->postJson('/api/monitor/region', [
            'x' => 120,
            'y' => 80,
            'width' => 1024,
            'height' => 768,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('region.x', 120);
        $response->assertJsonPath('region.width', 1024);

        $getResponse = $this->getJson('/api/monitor/region');
        $getResponse->assertJsonPath('region.width', 1024);
    }

    public function test_fails_on_invalid_region_payload(): void
    {
        $response = $this->postJson('/api/monitor/region', [
            'x' => -5,
            'y' => 0,
            'width' => 0,
            'height' => 100,
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['error']);
    }

    public function test_can_clear_region_via_api(): void
    {
        $this->postJson('/api/monitor/region', [
            'x' => 10,
            'y' => 10,
            'width' => 500,
            'height' => 500,
        ]);

        $clearResponse = $this->deleteJson('/api/monitor/region');
        $clearResponse->assertStatus(200);

        $getResponse = $this->getJson('/api/monitor/region');
        $getResponse->assertJson(['region' => null]);
    }

    public function test_selection_view_loads_successfully(): void
    {
        $response = $this->get('/selection');
        $response->assertStatus(200);
        $response->assertSee('Seleção de Área da Tela');
    }
}
