<?php

namespace App\Services\AI;

use App\DTO\AiPromptDTO;
use App\DTO\AiResponseDTO;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Throwable;

class GeminiAiProvider implements AiProviderInterface
{
    private string $apiKey;
    private string $model;
    private int $timeout;
    private int $maxRetries;
    private string $responseLanguage;

    public function __construct(
        ?string $apiKey = null,
        ?string $model = null,
        ?int $timeout = null,
        ?int $maxRetries = null,
        ?string $responseLanguage = null
    ) {
        $this->apiKey = $apiKey ?? (string) config('screen_ai.ai.api_key', '');
        $this->model = $model ?? (string) config('screen_ai.ai.default_model', 'gemini-1.5-flash');
        $this->timeout = $timeout ?? (int) config('screen_ai.ai.timeout', 30);
        $this->maxRetries = $maxRetries ?? (int) config('screen_ai.ai.max_retries', 2);
        $this->responseLanguage = $responseLanguage ?? (string) config('screen_ai.ai.response_language', 'pt-BR');
    }

    public function getName(): string
    {
        return 'gemini';
    }

    public function process(AiPromptDTO $prompt): AiResponseDTO
    {
        $startTime = microtime(true);

        if (empty($this->apiKey)) {
            return new AiResponseDTO(
                text: '',
                success: false,
                provider: $this->getName(),
                model: $this->model,
                errorMessage: 'Chave de API do Gemini não configurada (AI_API_KEY).'
            );
        }

        $textPrompt = PromptBuilder::build($prompt, $this->responseLanguage);

        $parts = [
            ['text' => $textPrompt],
        ];

        if (!empty($prompt->imagePath) && File::exists($prompt->imagePath)) {
            $imageContent = @file_get_contents($prompt->imagePath);
            if ($imageContent !== false) {
                $ext = strtolower(pathinfo($prompt->imagePath, PATHINFO_EXTENSION));
                $mimeType = match ($ext) {
                    'jpg', 'jpeg' => 'image/jpeg',
                    'webp' => 'image/webp',
                    default => 'image/png',
                };

                $parts[] = [
                    'inlineData' => [
                        'mimeType' => $mimeType,
                        'data' => base64_encode($imageContent),
                    ],
                ];
            }
        }

        $payload = [
            'contents' => [
                [
                    'parts' => $parts,
                ],
            ],
        ];

        $endpoint = sprintf(
            'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent?key=%s',
            $this->model,
            $this->apiKey
        );

        try {
            $httpClient = Http::timeout($this->timeout);

            if ($this->maxRetries > 0) {
                // Executa retry estritamente em caso de erros de conexão ou HTTP 5xx
                $httpClient->retry($this->maxRetries, 200, function (Throwable $exception) {
                    if ($exception instanceof RequestException) {
                        return $exception->response?->status() >= 500;
                    }
                    return true;
                }, false);
            }

            $response = $httpClient->post($endpoint, $payload);
            $durationMs = round((microtime(true) - $startTime) * 1000.0, 2);

            if ($response->successful()) {
                $responseData = $response->json();
                $extractedText = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? '';

                return new AiResponseDTO(
                    text: trim($extractedText),
                    success: true,
                    provider: $this->getName(),
                    model: $this->model,
                    metadata: [
                        'duration_ms' => $durationMs,
                        'status' => $response->status(),
                    ]
                );
            }

            $statusCode = $response->status();
            $errorMsg = match ($statusCode) {
                401, 403 => 'Falha de autenticação com o provedor Gemini (verifique a API key).',
                429 => 'Limite de requisições do Gemini excedido (Rate limit).',
                400 => 'Requisição rejeitada pelo Gemini (Payload inválido).',
                default => sprintf('Erro na comunicação com a API do Gemini (Código HTTP %d).', $statusCode),
            };

            return new AiResponseDTO(
                text: '',
                success: false,
                provider: $this->getName(),
                model: $this->model,
                errorMessage: $errorMsg,
                metadata: [
                    'duration_ms' => $durationMs,
                    'status' => $statusCode,
                ]
            );
        } catch (Throwable $e) {
            $durationMs = round((microtime(true) - $startTime) * 1000.0, 2);

            return new AiResponseDTO(
                text: '',
                success: false,
                provider: $this->getName(),
                model: $this->model,
                errorMessage: 'Falha na requisição ao provedor de IA: ' . $e->getMessage(),
                metadata: [
                    'duration_ms' => $durationMs,
                ]
            );
        }
    }
}
