<?php

namespace Tests\Unit;

use App\Enums\PermissionStatus;
use App\Services\Native\MacPermissionService;
use Tests\TestCase;

class PermissionServiceTest extends TestCase
{
    public function test_can_mock_and_verify_permission_status(): void
    {
        $service = new MacPermissionService;

        // 1. Mock Granted
        $service->setMockedStatus(PermissionStatus::GRANTED);
        $this->assertEquals(PermissionStatus::GRANTED, $service->getScreenRecordingStatus());

        // 2. Mock Denied
        $service->setMockedStatus(PermissionStatus::DENIED);
        $this->assertEquals(PermissionStatus::DENIED, $service->getScreenRecordingStatus());

        // 3. Explicação amigável do requisito
        $explanation = $service->explainRequirement();
        $this->assertNotEmpty($explanation);
        $this->assertStringContainsString('Gravação de Tela', $explanation);
    }
}
