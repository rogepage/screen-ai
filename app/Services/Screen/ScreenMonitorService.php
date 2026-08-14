<?php

namespace App\Services\Screen;

use App\DTO\MonitorConfigDTO;
use App\Enums\MonitorState;
use App\Events\MonitorPaused;
use App\Events\MonitorStarted;
use App\Events\MonitorStopped;

class ScreenMonitorService
{
    private MonitorState $state = MonitorState::STOPPED;
    private ?MonitorConfigDTO $currentConfig = null;

    public function getState(): MonitorState
    {
        return $this->state;
    }

    public function getConfig(): ?MonitorConfigDTO
    {
        return $this->currentConfig;
    }

    public function start(?MonitorConfigDTO $config = null): void
    {
        $this->currentConfig = $config ?? new MonitorConfigDTO();
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

    public function updateRegion(\App\DTO\ScreenRegionDTO $region): void
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

