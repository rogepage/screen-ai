<?php

namespace Tests\Unit;

use App\DTO\ScreenRegionDTO;
use App\Enums\PermissionStatus;
use App\Services\Native\MacPermissionService;
use App\Services\Screen\ScreenRegionService;
use App\Services\Settings\AppSettingsService;
use App\Services\Settings\SettingsValidator;
use App\Services\Startup\StartupService;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class StartupServiceTest extends TestCase
{
    private StartupService $startupService;

    private AppSettingsService $settingsService;

    private MacPermissionService $permissionService;

    private ScreenRegionService $regionService;

    protected function setUp(): void
    {
        parent::setUp();
        $filePath = storage_path('app/settings.json');
        if (File::exists($filePath)) {
            @unlink($filePath);
        }

        $this->settingsService = new AppSettingsService(new SettingsValidator);
        $this->permissionService = new MacPermissionService;
        $this->regionService = new ScreenRegionService;

        $this->startupService = new StartupService(
            $this->settingsService,
            $this->permissionService,
            $this->regionService
        );
    }

    public function test_identifies_first_run_correctly(): void
    {
        $this->assertTrue($this->startupService->isFirstRun());

        $this->settingsService->markOnboardingCompleted();

        $this->assertFalse($this->startupService->isFirstRun());
    }

    public function test_check_startup_status_reports_permission_and_region(): void
    {
        $this->permissionService->setMockedStatus(PermissionStatus::GRANTED);
        $this->regionService->setRegion(new ScreenRegionDTO(x: 10, y: 10, width: 500, height: 400));

        $status = $this->startupService->checkStartupStatus();

        $this->assertTrue($status['permission_granted']);
        $this->assertTrue($status['has_configured_region']);
        $this->assertArrayHasKey('ai_provider', $status);
    }
}
