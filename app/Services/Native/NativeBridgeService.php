<?php

namespace App\Services\Native;

use App\DTO\MonitorConfigDTO;
use App\DTO\ScreenRegionDTO;
use App\Enums\ActionType;
use App\Enums\MonitorState;
use App\Events\Native\AnalyzeNowShortcutPressed;
use App\Events\Native\ToggleMonitorShortcutPressed;
use App\Events\Native\ToggleOverlayShortcutPressed;
use App\Events\Native\TogglePauseShortcutPressed;
use App\Services\AI\AnalysisService;
use App\Services\Screen\ImageProcessorInterface;
use App\Services\Screen\ScreenCaptureInterface;
use App\Services\Screen\ScreenMonitorService;
use App\Services\Screen\ScreenRegionService;
use Illuminate\Support\Facades\Log;
use Native\Desktop\Facades\GlobalShortcut;
use Native\Desktop\Facades\Window;
use Throwable;

class NativeBridgeService
{
    private bool $overlayOpen = false;

    public function __construct(
        private readonly ScreenMonitorService $monitorService,
        private readonly ScreenRegionService $regionService,
        private readonly ScreenCaptureInterface $captureService,
        private readonly ImageProcessorInterface $imageProcessor,
        private readonly AnalysisService $analysisService
    ) {}

    /**
     * Registra os atalhos globais de forma resiliente contra conflitos de sistema.
     *
     * @return array<string, bool>
     */
    public function registerShortcuts(): array
    {
        $status = [];
        $enabled = config('screen_ai.native.global_shortcuts_enabled', true);

        if (! $enabled) {
            return $status;
        }

        $shortcuts = config('screen_ai.native.shortcuts', []);

        // 1. Toggle Monitor
        $status['toggle_monitor'] = $this->safelyRegisterShortcut(
            $shortcuts['toggle_monitor'] ?? 'CmdOrCtrl+Shift+S',
            ToggleMonitorShortcutPressed::class
        );

        // 2. Toggle Pause
        $status['toggle_pause'] = $this->safelyRegisterShortcut(
            $shortcuts['toggle_pause'] ?? 'CmdOrCtrl+Shift+P',
            TogglePauseShortcutPressed::class
        );

        // 3. Analyze Now
        $status['analyze_now'] = $this->safelyRegisterShortcut(
            $shortcuts['analyze_now'] ?? 'CmdOrCtrl+Shift+A',
            AnalyzeNowShortcutPressed::class
        );

        // 4. Toggle Overlay
        $status['toggle_overlay'] = $this->safelyRegisterShortcut(
            $shortcuts['toggle_overlay'] ?? 'CmdOrCtrl+Shift+O',
            ToggleOverlayShortcutPressed::class
        );

        return $status;
    }

    private function safelyRegisterShortcut(string $key, string $eventClass): bool
    {
        try {
            if (class_exists(GlobalShortcut::class)) {
                GlobalShortcut::key($key)
                    ->event($eventClass)
                    ->register();

                return true;
            }
        } catch (Throwable $e) {
            Log::warning("Falha ou conflito ao registrar atalho global [{$key}]: ".$e->getMessage());
        }

        return false;
    }

    /**
     * Alterna o estado de monitoramento (Iniciar / Parar).
     */
    public function toggleMonitor(): string
    {
        $currentState = $this->monitorService->getState();

        if ($currentState === MonitorState::MONITORING) {
            $this->monitorService->stop();

            return MonitorState::STOPPED->value;
        }

        $config = new MonitorConfigDTO(
            intervalMs: (int) config('screen_ai.monitor.default_interval_ms', 1000),
            selectedRegion: $this->regionService->getRegion(),
            actionType: ActionType::INTERPRET
        );

        $this->monitorService->start($config);

        return MonitorState::MONITORING->value;
    }

    /**
     * Alterna entre Pausado e Monitorando.
     */
    public function togglePause(): string
    {
        $currentState = $this->monitorService->getState();

        if ($currentState === MonitorState::MONITORING) {
            $this->monitorService->pause();

            return MonitorState::PAUSED->value;
        }

        return $this->toggleMonitor();
    }

    /**
     * Executa a análise imediata do frame atual.
     */
    public function triggerAnalysisNow(): array
    {
        $region = $this->regionService->getRegion() ?? new ScreenRegionDTO(x: 0, y: 0, width: 800, height: 600);
        $capture = $this->captureService->captureRegion($region);
        $this->monitorService->processCapture($capture);

        $processed = $this->imageProcessor->process($capture);
        $response = $this->analysisService->analyze(
            image: $processed,
            action: ActionType::INTERPRET,
            language: config('screen_ai.ai.response_language', 'pt-BR')
        );

        return [
            'success' => $response->success,
            'text' => $response->text,
            'provider' => $response->provider,
            'model' => $response->model,
            'timestamp' => time(),
        ];
    }

    /**
     * Alterna a visibilidade da janela de Overlay.
     */
    public function toggleOverlay(): bool
    {
        $this->overlayOpen = ! $this->overlayOpen;

        try {
            if (class_exists(Window::class)) {
                if ($this->overlayOpen) {
                    Window::open('overlay')
                        ->route('overlay')
                        ->width(360)
                        ->height(180)
                        ->alwaysOnTop()
                        ->showDevTools(false);
                } else {
                    Window::close('overlay');
                }
            }
        } catch (Throwable $e) {
            Log::info('Operação de janela Overlay interceptada ou em ambiente não-desktop: '.$e->getMessage());
        }

        return $this->overlayOpen;
    }

    public function isOverlayOpen(): bool
    {
        return $this->overlayOpen;
    }

    /**
     * Verifica se a área de captura configurada colide com a janela de overlay (Anti-Loop).
     *
     * @param  array{x: int, y: int, width: int, height: int}  $overlayBounds
     */
    public function checkRegionOverlayCollision(ScreenRegionDTO $region, array $overlayBounds): bool
    {
        $rLeft = $region->x;
        $rRight = $region->x + $region->width;
        $rTop = $region->y;
        $rBottom = $region->y + $region->height;

        $oLeft = $overlayBounds['x'];
        $oRight = $overlayBounds['x'] + $overlayBounds['width'];
        $oTop = $overlayBounds['y'];
        $oBottom = $overlayBounds['y'] + $overlayBounds['height'];

        // Não há colisão se um retângulo estiver completamente fora do outro
        if ($rRight <= $oLeft || $rLeft >= $oRight || $rBottom <= $oTop || $rTop >= $oBottom) {
            return false;
        }

        return true;
    }

    /**
     * Retorna o resumo do status da camada nativa.
     */
    public function getNativeStatus(): array
    {
        return [
            'monitor_state' => $this->monitorService->getState()->value,
            'overlay_open' => $this->overlayOpen,
            'overlay_enabled' => (bool) config('screen_ai.native.overlay_enabled', true),
            'shortcuts_enabled' => (bool) config('screen_ai.native.global_shortcuts_enabled', true),
            'shortcuts' => config('screen_ai.native.shortcuts', []),
        ];
    }
}
