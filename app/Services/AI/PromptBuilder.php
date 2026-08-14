<?php

namespace App\Services\AI;

use App\DTO\AiPromptDTO;
use App\Enums\ActionType;

class PromptBuilder
{
    public static function build(AiPromptDTO $prompt, ?string $language = null): string
    {
        $lang = $language ?? config('screen_ai.ai.response_language', 'pt-BR');

        $basePrompt = match ($prompt->actionType) {
            ActionType::INTERPRET => 'Analise o conteúdo da imagem e explique o que está sendo apresentado de forma clara e objetiva.',
            ActionType::TRANSLATE => 'Identifique o texto presente na imagem e traduza com precisão.',
            ActionType::ANSWER => 'Analise o conteúdo apresentado na imagem e responda à pergunta ou instrução exibida.',
            ActionType::EXPLAIN => 'Explique o conteúdo apresentado na imagem de forma didática e detalhada.',
            ActionType::SUMMARIZE => 'Resuma o conteúdo principal apresentado na imagem de forma concisa.',
            ActionType::CUSTOM => $prompt->userInstruction ?? 'Analise o conteúdo visual da imagem.',
        };

        if ($prompt->actionType !== ActionType::CUSTOM && !empty($prompt->userInstruction)) {
            $basePrompt .= sprintf("\nInstrução adicional do usuário: %s", $prompt->userInstruction);
        }

        if (!empty($prompt->systemPrompt)) {
            $basePrompt = sprintf("%s\n\n%s", $prompt->systemPrompt, $basePrompt);
        }

        return sprintf("%s\nResponda estritamente no idioma: %s.", trim($basePrompt), $lang);
    }
}
