<?php

namespace App\Services\AI;

use App\DTO\AiPromptDTO;
use App\Enums\ActionType;

interface PromptBuilderInterface
{
    public function buildPrompt(
        ActionType $action,
        ?string $userInstruction = null,
        ?string $language = null,
        ?string $systemPrompt = null
    ): string;

    public function buildAiPromptDTO(
        ?string $imagePath,
        ActionType $action,
        ?string $userInstruction = null,
        ?string $language = null,
        ?string $systemPrompt = null
    ): AiPromptDTO;
}
