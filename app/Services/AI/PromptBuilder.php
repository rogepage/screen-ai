<?php

namespace App\Services\AI;

use App\DTO\AiPromptDTO;

class PromptBuilder
{
    public static function build(AiPromptDTO $prompt, ?string $language = null): string
    {
        $builder = app()->has(PromptBuilderInterface::class)
            ? app(PromptBuilderInterface::class)
            : new DefaultPromptBuilder;

        $lang = $language ?? ($prompt->options['language'] ?? null);

        return $builder->buildPrompt(
            action: $prompt->actionType,
            userInstruction: $prompt->userInstruction,
            language: $lang,
            systemPrompt: $prompt->systemPrompt
        );
    }
}
