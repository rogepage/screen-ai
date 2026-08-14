<?php

namespace Tests\Unit;

use App\DTO\AiPromptDTO;
use App\Enums\ActionType;
use App\Services\AI\PromptBuilder;
use Tests\TestCase;

class PromptBuilderTest extends TestCase
{
    public function test_builds_interpret_prompt_in_configured_language(): void
    {
        $prompt = new AiPromptDTO(actionType: ActionType::INTERPRET);
        $result = PromptBuilder::build($prompt, 'pt-BR');

        $this->assertStringContainsString('Analise o conteúdo da imagem', $result);
        $this->assertStringContainsString('Responda estritamente no idioma: pt-BR.', $result);
    }

    public function test_builds_translate_prompt(): void
    {
        $prompt = new AiPromptDTO(actionType: ActionType::TRANSLATE);
        $result = PromptBuilder::build($prompt, 'en-US');

        $this->assertStringContainsString('Identifique o texto presente na imagem e traduza', $result);
        $this->assertStringContainsString('Responda estritamente no idioma: en-US.', $result);
    }

    public function test_builds_custom_prompt_with_user_instruction(): void
    {
        $prompt = new AiPromptDTO(
            actionType: ActionType::CUSTOM,
            userInstruction: 'Qual é o erro retornado no terminal?'
        );
        $result = PromptBuilder::build($prompt, 'pt-BR');

        $this->assertStringContainsString('Qual é o erro retornado no terminal?', $result);
        $this->assertStringContainsString('Responda estritamente no idioma: pt-BR.', $result);
    }

    public function test_includes_system_prompt_when_provided(): void
    {
        $prompt = new AiPromptDTO(
            actionType: ActionType::SUMMARIZE,
            systemPrompt: 'Você é um assistente sênior de desenvolvimento.'
        );
        $result = PromptBuilder::build($prompt, 'pt-BR');

        $this->assertStringContainsString('Você é um assistente sênior de desenvolvimento.', $result);
        $this->assertStringContainsString('Resuma o conteúdo principal', $result);
    }
}
