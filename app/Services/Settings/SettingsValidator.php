<?php

namespace App\Services\Settings;

use App\DTO\AppSettingsDTO;

class SettingsValidator
{
    /**
     * @return array<string, string>
     */
    public function validate(AppSettingsDTO $settings): array
    {
        $errors = [];

        if ($settings->intervalMs <= 0) {
            $errors['interval_ms'] = 'O intervalo de monitoramento deve ser maior que zero.';
        } elseif ($settings->intervalMs < 100) {
            $errors['interval_ms'] = 'O intervalo mínimo de monitoramento suportado é 100ms.';
        }

        if ($settings->debounceMs < 0) {
            $errors['debounce_ms'] = 'O tempo de debounce não pode ser negativo.';
        }

        if ($settings->changeThreshold < 0.0 || $settings->changeThreshold > 1.0) {
            $errors['change_threshold'] = 'A sensibilidade de mudança deve estar entre 0.0 e 1.0.';
        }

        if ($settings->aiTimeout <= 0) {
            $errors['ai_timeout'] = 'O timeout da IA deve ser maior que zero segundos.';
        }

        if (empty(trim($settings->aiProvider))) {
            $errors['ai_provider'] = 'O provedor de IA deve ser especificado.';
        }

        $validLanguages = ['pt-BR', 'pt', 'en-US', 'en', 'es-ES', 'es', 'fr-FR', 'fr'];
        if (!in_array($settings->uiLanguage, $validLanguages, true)) {
            $errors['ui_language'] = 'Idioma de interface não suportado.';
        }

        if (!in_array($settings->responseLanguage, $validLanguages, true)) {
            $errors['response_language'] = 'Idioma de resposta da IA não suportado.';
        }

        $validRetentions = ['7_days', '30_days', 'always'];
        if (!in_array($settings->historyRetention, $validRetentions, true)) {
            $errors['history_retention'] = 'Política de retenção de histórico inválida.';
        }

        return $errors;
    }

    public function isValid(AppSettingsDTO $settings): bool
    {
        return empty($this->validate($settings));
    }
}
