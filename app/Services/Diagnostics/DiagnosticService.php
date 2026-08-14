<?php

namespace App\Services\Diagnostics;

use App\Services\Native\PermissionServiceInterface;
use App\Services\Observability\HealthCheckService;
use App\Services\Observability\MetricsCollectorService;
use App\Services\Screen\ScreenMonitorService;
use App\Services\Settings\AppSettingsService;

class DiagnosticService
{
    public function __construct(
        private readonly HealthCheckService $healthService,
        private readonly MetricsCollectorService $metricsCollector,
        private readonly PermissionServiceInterface $permissionService,
        private readonly ScreenMonitorService $monitorService,
        private readonly AppSettingsService $settingsService
    ) {}

    public function generateReport(): array
    {
        return [
            'application' => [
                'name' => 'Screen AI',
                'version' => config('screen_ai.version', '1.0.0'),
                'environment' => app()->environment(),
                'laravel_version' => app()->version(),
                'php_version' => PHP_VERSION,
                'os_family' => PHP_OS_FAMILY,
                'os_version' => php_uname('r'),
            ],
            'health' => $this->healthService->checkHealth(),
            'monitor' => [
                'state' => $this->monitorService->getState()->value,
                'config' => $this->monitorService->getConfig()?->toArray(),
            ],
            'permission' => [
                'screen_recording' => $this->permissionService->getScreenRecordingStatus()->value,
            ],
            'settings_summary' => $this->settingsService->getSettings()->toArray(),
            'metrics' => $this->metricsCollector->getMetrics(),
            'timestamp' => date('c'),
        ];
    }
}
