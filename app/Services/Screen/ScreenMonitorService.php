<?php

namespace App\Services\Screen;

use App\DTO\CaptureResultDTO;
use App\DTO\MonitorConfigDTO;
use App\DTO\ScreenRegionDTO;
use App\Enums\MonitorState;
use App\Events\MonitorPaused;
use App\Events\MonitorStarted;
use App\Events\MonitorStopped;
use App\Events\ScreenChanged;

class ScreenMonitorService
{
    private MonitorState $state = MonitorState::STOPPED;

    private ?MonitorConfigDTO $currentConfig = null;

    private ?CaptureResultDTO $lastCapture = null;

    public function __construct(
        private readonly ?ChangeDetectorInterface $changeDetector = null,
        private readonly ?ContinuousMonitorService $continuousMonitor = null
    ) {}

    public function getState(): MonitorState
    {
        return $this->state;
    }

    public function getLastCapture(): ?CaptureResultDTO
    {
        return $this->lastCapture;
    }

    public function processCapture(CaptureResultDTO $capture): bool
    {
        $detector = $this->changeDetector ?? app(ChangeDetectorInterface::class);
        $previousPath = $this->lastCapture?->filePath;

        $hasChanged = $detector->hasChanged($capture->filePath, $previousPath);

        if ($hasChanged) {
            $this->lastCapture = $capture;
            ScreenChanged::dispatch($capture);

            return true;
        }

        return false;
    }

    public function executeCycle(): ?CaptureResultDTO
    {
        $orchestrator = $this->continuousMonitor ?? app(ContinuousMonitorService::class);

        return $orchestrator->runCycle($this);
    }

    public function getConfig(): ?MonitorConfigDTO
    {
        return $this->currentConfig;
    }

    public function start(?MonitorConfigDTO $config = null): void
    {
        $this->currentConfig = $config ?? new MonitorConfigDTO;
        $this->state = MonitorState::MONITORING;

        MonitorStarted::dispatch($this->currentConfig);
    }

    public function stop(string $reason = 'User requested'): void
    {
        $this->state = MonitorState::STOPPED;

        MonitorStopped::dispatch($reason);
    }

    public function pause(string $reason = 'User requested'): void
    {
        if ($this->state === MonitorState::MONITORING) {
            $this->state = MonitorState::PAUSED;
            MonitorPaused::dispatch($reason);
        }
    }

    public function resume(): void
    {
        if ($this->state === MonitorState::PAUSED) {
            $this->state = MonitorState::MONITORING;
            if ($this->currentConfig !== null) {
                MonitorStarted::dispatch($this->currentConfig);
            }
        }
    }

    public function updateRegion(ScreenRegionDTO $region): void
    {
        if ($this->currentConfig !== null) {
            $this->currentConfig = new MonitorConfigDTO(
                intervalMs: $this->currentConfig->intervalMs,
                selectedRegion: $region,
                aiProvider: $this->currentConfig->aiProvider,
                actionType: $this->currentConfig->actionType
            );
        }
    }
}
