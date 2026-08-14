<?php

namespace App\Services\Observability;

use Illuminate\Support\Facades\Log;

class ObservabilityService
{
    public function generateCorrelationId(): string
    {
        return 'cid_'.bin2hex(random_bytes(6));
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function logEvent(
        string $level,
        string $component,
        string $event,
        ?string $correlationId = null,
        array $context = []
    ): void {
        $sanitizedContext = $this->sanitizeContext($context);

        $payload = [
            'component' => $component,
            'event' => $event,
            'correlation_id' => $correlationId ?? 'none',
            'context' => $sanitizedContext,
        ];

        $logMessage = sprintf(
            '[%s] component=%s event=%s correlation_id=%s',
            strtoupper($level),
            $component,
            $event,
            $correlationId ?? 'none'
        );

        match (strtolower($level)) {
            'error' => Log::error($logMessage, $payload),
            'warning' => Log::warning($logMessage, $payload),
            'debug' => Log::debug($logMessage, $payload),
            default => Log::info($logMessage, $payload),
        };
    }

    /**
     * Remove tokens, chaves e dados sensíveis de logs.
     *
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function sanitizeContext(array $context): array
    {
        $sanitized = [];
        $sensitiveKeys = ['api_key', 'secret', 'password', 'token', 'raw_image', 'base64', 'bearer'];

        foreach ($context as $key => $value) {
            $lowerKey = strtolower((string) $key);
            $isSensitive = false;

            if ($lowerKey === 'key' || str_ends_with($lowerKey, '_key')) {
                $isSensitive = true;
            } else {
                foreach ($sensitiveKeys as $pattern) {
                    if (str_contains($lowerKey, $pattern)) {
                        $isSensitive = true;
                        break;
                    }
                }
            }

            if ($isSensitive) {
                $sanitized[$key] = '********';
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeContext($value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }
}
