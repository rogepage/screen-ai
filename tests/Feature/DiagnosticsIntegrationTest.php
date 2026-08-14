<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiagnosticsIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_health_endpoint(): void
    {
        $response = $this->getJson('/api/health');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'message',
            'permission_status',
            'circuit_breaker_state',
            'ai_available',
            'metrics',
        ]);
    }

    public function test_export_diagnostics_endpoint(): void
    {
        $response = $this->getJson('/api/diagnostics/export');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'application' => [
                'name',
                'version',
                'laravel_version',
                'php_version',
            ],
            'health',
            'monitor',
            'permission',
            'metrics',
            'timestamp',
        ]);
    }

    public function test_cleanup_endpoint(): void
    {
        $response = $this->postJson('/api/maintenance/cleanup');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'deleted_count',
        ]);
    }
}
