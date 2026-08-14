<?php

namespace App\Services\Screen;

use App\DTO\CaptureResultDTO;
use App\Events\CaptureDebounced;

class CaptureDebounceService
{
    private float $lastChangeTimestamp = 0.0;

    private int $debouncedCount = 0;

    private int $droppedCount = 0;

    public function __construct(
        private readonly int $debounceMs = 500
    ) {}

    /**
     * Avalia se a captura deve avançar para o pipeline de análise após o período de estabilização.
     */
    public function shouldProcess(CaptureResultDTO $capture): bool
    {
        $now = microtime(true);
        $elapsedMs = ($now - $this->lastChangeTimestamp) * 1000;

        $effectiveDebounce = $this->debounceMs > 0
            ? $this->debounceMs
            : (int) config('screen_ai.monitor.debounce_ms', 500);

        if ($this->lastChangeTimestamp > 0 && $elapsedMs < $effectiveDebounce) {
            $this->droppedCount++;
            $this->lastChangeTimestamp = $now;

            return false;
        }

        $this->lastChangeTimestamp = $now;
        $this->debouncedCount++;

        CaptureDebounced::dispatch($capture);

        return true;
    }

    public function reset(): void
    {
        $this->lastChangeTimestamp = 0.0;
        $this->debouncedCount = 0;
        $this->droppedCount = 0;
    }

    public function getStats(): array
    {
        return [
            'debounced_count' => $this->debouncedCount,
            'dropped_count' => $this->droppedCount,
            'debounce_ms' => $this->debounceMs,
            'last_change_timestamp' => $this->lastChangeTimestamp,
        ];
    }
}
