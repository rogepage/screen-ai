<?php

namespace App\Services\Observability;

class MetricsCollectorService
{
    private int $capturesCount = 0;

    private int $changesDetectedCount = 0;

    private int $changesIgnoredCount = 0;

    private int $aiRequestsCount = 0;

    private int $aiSuccessCount = 0;

    private int $aiFailedCount = 0;

    private int $captureErrorsCount = 0;

    private int $permissionErrorsCount = 0;

    /** @var array<float> */
    private array $processingTimes = [];

    /** @var array<float> */
    private array $aiResponseTimes = [];

    public function recordCapture(bool $hasChanged): void
    {
        $this->capturesCount++;
        if ($hasChanged) {
            $this->changesDetectedCount++;
        } else {
            $this->changesIgnoredCount++;
        }
    }

    public function recordAiCall(bool $success, float $durationMs): void
    {
        $this->aiRequestsCount++;
        if ($success) {
            $this->aiSuccessCount++;
        } else {
            $this->aiFailedCount++;
        }

        $this->aiResponseTimes[] = $durationMs;
        if (count($this->aiResponseTimes) > 100) {
            array_shift($this->aiResponseTimes);
        }
    }

    public function recordProcessingDuration(float $durationMs): void
    {
        $this->processingTimes[] = $durationMs;
        if (count($this->processingTimes) > 100) {
            array_shift($this->processingTimes);
        }
    }

    public function recordCaptureError(): void
    {
        $this->captureErrorsCount++;
    }

    public function recordPermissionError(): void
    {
        $this->permissionErrorsCount++;
    }

    public function getMetrics(): array
    {
        $avgProcessing = count($this->processingTimes) > 0
            ? round(array_sum($this->processingTimes) / count($this->processingTimes), 2)
            : 0.0;

        $avgAi = count($this->aiResponseTimes) > 0
            ? round(array_sum($this->aiResponseTimes) / count($this->aiResponseTimes), 2)
            : 0.0;

        return [
            'captures_count' => $this->capturesCount,
            'changes_detected_count' => $this->changesDetectedCount,
            'changes_ignored_count' => $this->changesIgnoredCount,
            'ai_requests_count' => $this->aiRequestsCount,
            'ai_success_count' => $this->aiSuccessCount,
            'ai_failed_count' => $this->aiFailedCount,
            'avg_processing_time_ms' => $avgProcessing,
            'avg_ai_response_time_ms' => $avgAi,
            'capture_errors_count' => $this->captureErrorsCount,
            'permission_errors_count' => $this->permissionErrorsCount,
        ];
    }

    public function reset(): void
    {
        $this->capturesCount = 0;
        $this->changesDetectedCount = 0;
        $this->changesIgnoredCount = 0;
        $this->aiRequestsCount = 0;
        $this->aiSuccessCount = 0;
        $this->aiFailedCount = 0;
        $this->captureErrorsCount = 0;
        $this->permissionErrorsCount = 0;
        $this->processingTimes = [];
        $this->aiResponseTimes = [];
    }
}
