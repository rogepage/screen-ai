<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingAndSettingsIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_views_render_successfully(): void
    {
        $onboardingRes = $this->get('/onboarding');
        $onboardingRes->assertStatus(200);
        $onboardingRes->assertSee('Screen AI');

        $settingsRes = $this->get('/settings');
        $settingsRes->assertStatus(200);
        $settingsRes->assertSee('Configurações — Screen AI');
    }

    public function test_settings_api_endpoints(): void
    {
        // 1. Obter configurações
        $getRes = $this->getJson('/api/settings');
        $getRes->assertStatus(200);
        $getRes->assertJsonStructure(['ui_language', 'interval_ms', 'ai_provider']);

        // 2. Salvar configurações válidas
        $postRes = $this->postJson('/api/settings', [
            'interval_ms' => 1500,
            'debounce_ms' => 400,
            'change_threshold' => 0.08,
            'ai_provider' => 'mock',
            'ai_model' => 'gemini-1.5-flash',
            'ui_language' => 'pt-BR',
            'response_language' => 'pt-BR',
            'history_retention' => '30_days',
        ]);
        $postRes->assertStatus(200);
        $postRes->assertJsonPath('settings.interval_ms', 1500);

        // 3. Salvar configurações inválidas deve retornar 422
        $invalidRes = $this->postJson('/api/settings', [
            'interval_ms' => 10,
        ]);
        $invalidRes->assertStatus(422);
        $invalidRes->assertJsonStructure(['errors']);
    }

    public function test_test_ai_connection_and_capture_preview_endpoints(): void
    {
        // Testar conexão com IA
        $aiRes = $this->postJson('/api/settings/test-ai', [
            'ai_provider' => 'mock',
            'ai_model' => 'gemini-1.5-flash',
        ]);
        $aiRes->assertStatus(200);
        $aiRes->assertJsonPath('success', true);

        // Testar preview de captura
        $captureRes = $this->postJson('/api/settings/test-capture');
        $captureRes->assertStatus(200);
        $captureRes->assertJsonPath('success', true);
        $captureRes->assertJsonStructure(['preview' => ['width', 'height', 'format']]);
    }

    public function test_complete_onboarding_endpoint(): void
    {
        $statusBefore = $this->getJson('/api/startup/status');
        $statusBefore->assertStatus(200);

        $completeRes = $this->postJson('/api/startup/onboarding/complete');
        $completeRes->assertStatus(200);

        $statusAfter = $this->getJson('/api/startup/status');
        $statusAfter->assertStatus(200);
        $statusAfter->assertJsonPath('onboarding_completed', true);
        $statusAfter->assertJsonPath('is_first_run', false);
    }
}
