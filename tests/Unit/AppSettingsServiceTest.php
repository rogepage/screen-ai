<?php

namespace Tests\Unit;

use App\DTO\AppSettingsDTO;
use App\Services\Settings\AppSettingsService;
use App\Services\Settings\SettingsValidator;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AppSettingsServiceTest extends TestCase
{
    private AppSettingsService $service;

    private string $filePath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->filePath = storage_path('app/settings.json');
        if (File::exists($this->filePath)) {
            @unlink($this->filePath);
        }
        $this->service = new AppSettingsService(new SettingsValidator);
    }

    protected function tearDown(): void
    {
        if (File::exists($this->filePath)) {
            @unlink($this->filePath);
        }
        parent::tearDown();
    }

    public function test_can_load_default_settings_and_save_modifications(): void
    {
        $settings = $this->service->getSettings();
        $this->assertInstanceOf(AppSettingsDTO::class, $settings);
        $this->assertEquals(1000, $settings->intervalMs);

        // Modificar e salvar
        $updated = new AppSettingsDTO(
            intervalMs: 2500,
            debounceMs: 600,
            aiProvider: 'gemini',
            uiLanguage: 'en-US'
        );

        $errors = $this->service->saveSettings($updated);
        $this->assertEmpty($errors);

        $reloaded = $this->service->getSettings();
        $this->assertEquals(2500, $reloaded->intervalMs);
        $this->assertEquals('en-US', $reloaded->uiLanguage);
    }

    public function test_mark_onboarding_completed(): void
    {
        $this->assertFalse($this->service->getSettings()->onboardingCompleted);

        $this->service->markOnboardingCompleted();

        $this->assertTrue($this->service->getSettings()->onboardingCompleted);
    }
}
