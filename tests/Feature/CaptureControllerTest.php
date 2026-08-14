<?php

namespace Tests\Feature;

use Tests\TestCase;

class CaptureControllerTest extends TestCase
{
    public function test_can_trigger_capture_via_api(): void
    {
        // Define primeiro uma região
        $this->postJson('/api/monitor/region', [
            'x' => 10,
            'y' => 10,
            'width' => 400,
            'height' => 300,
        ]);

        $response = $this->postJson('/api/monitor/capture');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'capture' => [
                'file_path',
                'hash',
                'timestamp',
                'has_changed',
                'dimensions',
            ],
        ]);
    }
}
