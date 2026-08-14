<?php

namespace App\Listeners;

use App\Events\Native\AnalyzeNowShortcutPressed;
use App\Events\Native\ToggleMonitorShortcutPressed;
use App\Events\Native\ToggleOverlayShortcutPressed;
use App\Events\Native\TogglePauseShortcutPressed;
use App\Services\Native\NativeBridgeService;
use Illuminate\Support\Facades\Log;
use Throwable;

class HandleNativeShortcuts
{
    public function __construct(
        private readonly NativeBridgeService $nativeBridge
    ) {}

    public function handle(object $event): void
    {
        try {
            if ($event instanceof ToggleMonitorShortcutPressed) {
                $newState = $this->nativeBridge->toggleMonitor();
                Log::info("Atalho acionado: Toggle Monitor. Novo estado: {$newState}");
            } elseif ($event instanceof TogglePauseShortcutPressed) {
                $newState = $this->nativeBridge->togglePause();
                Log::info("Atalho acionado: Toggle Pause. Novo estado: {$newState}");
            } elseif ($event instanceof AnalyzeNowShortcutPressed) {
                $result = $this->nativeBridge->triggerAnalysisNow();
                Log::info('Atalho acionado: Analisar Agora.', ['success' => $result['success'] ?? false]);
            } elseif ($event instanceof ToggleOverlayShortcutPressed) {
                $isOpen = $this->nativeBridge->toggleOverlay();
                Log::info('Atalho acionado: Toggle Overlay. Aberto: '.($isOpen ? 'SIM' : 'NÃO'));
            }
        } catch (Throwable $e) {
            Log::error('Erro ao processar evento de atalho nativo: '.$e->getMessage());
        }
    }
}
