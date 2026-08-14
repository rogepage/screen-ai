<?php

namespace App\Services\Startup;

use App\Enums\PermissionStatus;
use App\Services\Native\PermissionServiceInterface;
use App\Services\Screen\ScreenRegionService;
use App\Services\Settings\AppSettingsService;

class StartupService
{
    public function __construct(
        private readonly AppSettingsService $settingsService,
        private readonly PermissionServiceInterface $permissionService,
        private readonly ScreenRegionService $regionService
    ) {}

    public function isFirstRun(): bool
    {
        return ! $this->settingsService->getSettings()->onboardingCompleted;
    }

    public function checkStartupStatus(): array
    {
        $settings = $this->settingsService->getSettings();
        $permissionStatus = $this->permissionService->getScreenRecordingStatus();
        $region = $this->regionService->getRegion();

        return [
            'is_first_run' => ! $settings->onboardingCompleted,
            'onboarding_completed' => $settings->onboardingCompleted,
            'permission_status' => $permissionStatus->value,
            'permission_granted' => $permissionStatus === PermissionStatus::GRANTED,
            'has_configured_region' => $region !== null && $region->width > 0 && $region->height > 0,
            'ai_provider' => $settings->aiProvider,
            'ui_language' => $settings->uiLanguage,
            'response_language' => $settings->responseLanguage,
        ];
    }
}
