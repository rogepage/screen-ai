<?php

namespace Tests\Unit;

use App\DTO\AiPromptDTO;
use App\Enums\ActionType;
use App\Services\AI\GeminiAiProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GeminiAiProviderTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempDir = storage_path('framework/testing/gemini_test');

        if (! File::exists($this->tempDir)) {
            File::makeDirectory($this->tempDir, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        if (File::exists($this->tempDir)) {
            File::deleteDirectory($this->tempDir);
        }
        parent::tearDown();
    }

    public function test_returns_error_if_api_key_is_empty(): void
    {
        $provider = new GeminiAiProvider(apiKey: '');
        $prompt = new AiPromptDTO(actionType: ActionType::INTERPRET);

        $response = $provider->process($prompt);

        $this->assertFalse($response->success);
        $this->assertEquals('gemini', $response->provider);
        $this->assertStringContainsString('Chave de API do Gemini não configurada', $response->errorMessage);
    }

    public function test_sends_payload_and_parses_successful_response(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'Esta é uma análise detalhada da tela com o código Laravel.'],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $imgPath = $this->tempDir.'/test.png';
        File::put($imgPath, 'dummy image bytes');

        $provider = new GeminiAiProvider(apiKey: 'dummy_key', model: 'gemini-1.5-flash');
        $prompt = new AiPromptDTO(
            imagePath: $imgPath,
            actionType: ActionType::INTERPRET
        );

        $response = $provider->process($prompt);

        $this->assertTrue($response->success);
        $this->assertEquals('gemini', $response->provider);
        $this->assertEquals('gemini-1.5-flash', $response->model);
        $this->assertEquals('Esta é uma análise detalhada da tela com o código Laravel.', $response->text);

        Http::assertSent(function ($request) {
            $data = $request->data();

            return isset($data['contents'][0]['parts'][0]['text']) &&
                   str_contains($request->url(), 'generativelanguage.googleapis.com') &&
                   ! str_contains($request->body(), 'dummy_key_in_body');
        });
    }

    public function test_handles_auth_error_gracefully(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'error' => ['message' => 'API key not valid.'],
            ], 401),
        ]);

        $provider = new GeminiAiProvider(apiKey: 'invalid_key');
        $prompt = new AiPromptDTO(actionType: ActionType::EXPLAIN);

        $response = $provider->process($prompt);

        $this->assertFalse($response->success);
        $this->assertStringContainsString('Falha de autenticação com o provedor Gemini', $response->errorMessage);
    }

    public function test_handles_rate_limit_error(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'error' => ['message' => 'Quota exceeded.'],
            ], 429),
        ]);

        $provider = new GeminiAiProvider(apiKey: 'dummy_key');
        $prompt = new AiPromptDTO(actionType: ActionType::SUMMARIZE);

        $response = $provider->process($prompt);

        $this->assertFalse($response->success);
        $this->assertStringContainsString('Limite de requisições do Gemini excedido', $response->errorMessage);
    }
}
