<?php

namespace App\Services\Settings;

use App\DTO\AppSettingsDTO;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Throwable;

class AppSettingsService
{
    private string $storagePath;
    private ?AppSettingsDTO $cachedSettings = null;

    public function __construct(
        private readonly SettingsValidator $validator
    ) {
        $this->storagePath = storage_path('app/settings.json');
    }

    public function getSettings(): AppSettingsDTO
    {
        if ($this->cachedSettings !== null) {
            return $this->cachedSettings;
        }

        if (File::exists($this->storagePath)) {
            try {
                $content = File::get($this->storagePath);
                $data = json_decode($content, true);
                if (is_array($data)) {
                    $this->cachedSettings = AppSettingsDTO::fromArray($data);
                    return $this->cachedSettings;
                }
            } catch (Throwable $e) {
                Log::warning('Erro ao ler arquivo de configurações locais: ' . $e->getMessage());
            }
        }

        $this->cachedSettings = new AppSettingsDTO(
            uiLanguage: config('app.locale', 'pt-BR'),
            responseLanguage: config('screen_ai.ai.response_language', 'pt-BR'),
            autoStart: false,
            overlayEnabled: (bool) config('screen_ai.native.overlay_enabled', true),
            overlayPosition: config('screen_ai.native.overlay_position', 'bottom-right'),
            intervalMs: (int) config('screen_ai.monitor.default_interval_ms', 1000),
            debounceMs: (int) config('screen_ai.monitor.debounce_ms', 500),
            changeThreshold: (float) config('screen_ai.monitor.change_threshold', 0.05),
            aiProvider: config('screen_ai.ai.default_provider', 'mock'),
            aiModel: config('screen_ai.ai.default_model', 'gemini-1.5-flash'),
            aiTimeout: (int) config('screen_ai.ai.timeout', 30),
            onboardingCompleted: false
        );

        return $this->cachedSettings;
    }

    /**
     * @return array<string, string> Lista de erros de validação, vazia se salvo com sucesso
     */
    public function saveSettings(AppSettingsDTO $settings): array
    {
        $errors = $this->validator->validate($settings);
        if (!empty($errors)) {
            return $errors;
        }

        try {
            $dir = dirname($this->storagePath);
            if (!File::exists($dir)) {
                File::makeDirectory($dir, 0755, true);
            }

            File::put($this->storagePath, json_encode($settings->toArray(), JSON_PRETTY_PRINT));
            $this->cachedSettings = $settings;
            return [];
        } catch (Throwable $e) {
            Log::error('Falha ao salvar configurações no disco: ' . $e->getMessage());
            return ['storage' => 'Falha técnica ao gravar configurações no disco.'];
        }
    }

    public function markOnboardingCompleted(): void
    {
        $current = $this->getSettings();
        $updated = new AppSettingsDTO(
            uiLanguage: $current->uiLanguage,
            responseLanguage: $current->responseLanguage,
            autoStart: $current->autoStart,
            overlayEnabled: $current->overlayEnabled,
            overlayPosition: $current->overlayPosition,
            intervalMs: $current->intervalMs,
            debounceMs: $current->debounceMs,
            changeThreshold: $current->changeThreshold,
            aiProvider: $current->aiProvider,
            aiModel: $current->aiModel,
            aiTimeout: $current->aiTimeout,
            defaultAction: $current->defaultAction,
            historyRetention: $current->historyRetention,
            onboardingCompleted: true,
            selectedDisplayId: $current->selectedDisplayId
        );

        $this->saveSettings($updated);
    }

    public function resetDefaults(): AppSettingsDTO
    {
        if (File::exists($this->storagePath)) {
            @unlink($this->storagePath);
        }

        $this->cachedSettings = null;
        return $this->getSettings();
    }
}
