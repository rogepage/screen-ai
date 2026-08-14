<?php

namespace App\Services\Screen;

use App\DTO\CaptureResultDTO;
use App\DTO\ScreenRegionDTO;
use App\Enums\ActionType;
use App\Enums\MonitorState;
use App\Events\AnalysisQueued;
use App\Jobs\ProcessScreenAnalysisJob;
use App\Services\Observability\MetricsCollectorService;
use App\Services\Observability\ObservabilityService;
use Illuminate\Support\Facades\Log;
use Throwable;

class ContinuousMonitorService
{
    private int $cyclesCount = 0;

    private int $capturesCount = 0;

    private int $queuedJobsCount = 0;

    public function __construct(
        private readonly ScreenCaptureInterface $captureService,
        private readonly ScreenRegionService $regionService,
        private readonly CaptureDebounceService $debounceService,
        private readonly ?ObservabilityService $observability = null,
        private readonly ?MetricsCollectorService $metrics = null
    ) {}

    /**
     * Executa um único ciclo do monitoramento contínuo.
     */
    public function runCycle(ScreenMonitorService $monitorService): ?CaptureResultDTO
    {
        if ($monitorService->getState() !== MonitorState::MONITORING) {
            return null;
        }

        $cid = $this->observability?->generateCorrelationId() ?? 'cid_'.uniqid();
        $this->cyclesCount++;
        $region = $this->regionService->getRegion() ?? new ScreenRegionDTO(x: 0, y: 0, width: 800, height: 600);

        try {
            $this->observability?->logEvent('debug', 'ContinuousMonitor', 'CAPTURE_STARTED', $cid);

            $capture = $this->captureService->captureRegion($region);
            $this->capturesCount++;

            $hasChanged = $monitorService->processCapture($capture);
            $this->metrics?->recordCapture($hasChanged);

            if ($hasChanged) {
                $this->observability?->logEvent('info', 'ContinuousMonitor', 'CHANGE_DETECTED', $cid);

                $shouldProcess = $this->debounceService->shouldProcess($capture);

                if ($shouldProcess) {
                    $config = $monitorService->getConfig();
                    $actionType = $config?->actionType ?? ActionType::INTERPRET;

                    $job = new ProcessScreenAnalysisJob(
                        capture: $capture,
                        actionType: $actionType
                    );

                    dispatch($job);
                    $this->queuedJobsCount++;

                    $this->observability?->logEvent('info', 'ContinuousMonitor', 'ANALYSIS_QUEUED', $cid, [
                        'action' => $actionType->value,
                    ]);

                    AnalysisQueued::dispatch($capture, uniqid('job_'));
                }
            } else {
                $this->observability?->logEvent('debug', 'ContinuousMonitor', 'CHANGE_IGNORED', $cid);
            }

            return $capture;
        } catch (Throwable $e) {
            $this->metrics?->recordCaptureError();
            $this->observability?->logEvent('error', 'ContinuousMonitor', 'CAPTURE_FAILED', $cid, [
                'error' => $e->getMessage(),
            ]);

            Log::error('Erro no ciclo de monitoramento contínuo: '.$e->getMessage());

            return null;
        }
    }

    public function getStats(): array
    {
        return [
            'cycles_count' => $this->cyclesCount,
            'captures_count' => $this->capturesCount,
            'queued_jobs_count' => $this->queuedJobsCount,
            'debounce_stats' => $this->debounceService->getStats(),
        ];
    }

    public function resetStats(): void
    {
        $this->cyclesCount = 0;
        $this->capturesCount = 0;
        $this->queuedJobsCount = 0;
        $this->debounceService->reset();
    }
}
