<?php

namespace App\Services\Native;

use App\Enums\PermissionStatus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Throwable;

class MacPermissionService implements PermissionServiceInterface
{
    private ?PermissionStatus $mockedStatus = null;

    public function setMockedStatus(?PermissionStatus $status): void
    {
        $this->mockedStatus = $status;
    }

    public function getScreenRecordingStatus(): PermissionStatus
    {
        if ($this->mockedStatus !== null) {
            return $this->mockedStatus;
        }

        if (app()->environment('testing')) {
            return PermissionStatus::GRANTED;
        }

        if (PHP_OS_FAMILY !== 'Darwin') {
            return PermissionStatus::GRANTED;
        }

        try {
            // Tenta uma captura rápida de teste silenciosa de 1x1 pixel para avaliar se o macOS permite captura
            $tempPath = storage_path('framework/testing/permission_check.png');
            $result = Process::timeout(3)->run(['screencapture', '-x', '-R0,0,1,1', $tempPath]);

            if ($result->successful() && file_exists($tempPath)) {
                @unlink($tempPath);
                return PermissionStatus::GRANTED;
            }

            return PermissionStatus::DENIED;
        } catch (Throwable $e) {
            Log::info('Não foi possível determinar a permissão de gravação de tela: ' . $e->getMessage());
            return PermissionStatus::UNKNOWN;
        }
    }

    public function openSystemSettings(): void
    {
        if (PHP_OS_FAMILY === 'Darwin') {
            try {
                Process::run(['open', 'x-apple.systempreferences:com.apple.preference.security?Privacy_ScreenCapture']);
            } catch (Throwable $e) {
                Log::warning('Falha ao abrir os Ajustes do Sistema do macOS: ' . $e->getMessage());
            }
        }
    }

    public function explainRequirement(): string
    {
        return 'O Screen AI precisa de permissão de Gravação de Tela do macOS para monitorar visualmente a região selecionada.';
    }
}
