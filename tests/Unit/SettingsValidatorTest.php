<?php

namespace Tests\Unit;

use App\DTO\AppSettingsDTO;
use App\Services\Settings\SettingsValidator;
use Tests\TestCase;

class SettingsValidatorTest extends TestCase
{
    private SettingsValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new SettingsValidator;
    }

    public function test_valid_settings_pass_validation(): void
    {
        $settings = new AppSettingsDTO(
            intervalMs: 1000,
            debounceMs: 500,
            changeThreshold: 0.05,
            aiProvider: 'gemini',
            uiLanguage: 'pt-BR',
            responseLanguage: 'en-US',
            historyRetention: '30_days'
        );

        $this->assertTrue($this->validator->isValid($settings));
        $this->assertEmpty($this->validator->validate($settings));
    }

    public function test_invalid_settings_generate_errors(): void
    {
        $settings = new AppSettingsDTO(
            intervalMs: 50, // Menor que 100ms
            debounceMs: -10, // Negativo
            changeThreshold: 1.5, // Maior que 1.0
            aiProvider: '', // Vazio
            uiLanguage: 'invalid_lang',
            historyRetention: 'invalid_retention'
        );

        $this->assertFalse($this->validator->isValid($settings));
        $errors = $this->validator->validate($settings);

        $this->assertArrayHasKey('interval_ms', $errors);
        $this->assertArrayHasKey('debounce_ms', $errors);
        $this->assertArrayHasKey('change_threshold', $errors);
        $this->assertArrayHasKey('ai_provider', $errors);
        $this->assertArrayHasKey('ui_language', $errors);
        $this->assertArrayHasKey('history_retention', $errors);
    }
}
