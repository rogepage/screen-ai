<?php

namespace Tests\Unit;

use App\DTO\AiPromptDTO;
use App\Enums\ActionType;
use App\Services\AI\DefaultPromptBuilder;
use Tests\TestCase;

class DefaultPromptBuilderTest extends TestCase
{
    public function test_builds_prompt_for_all_action_types(): void
    {
        $builder = new DefaultPromptBuilder('pt-BR');

        foreach (ActionType::cases() as $action) {
            $promptText = $builder->buildPrompt($action, 'instrução teste', 'pt-BR');
            $this->assertNotEmpty($promptText);
            $this->assertStringContainsString('DIRETRIZ DE SEGURANÇA', $promptText);
            $this->assertStringContainsString('Idioma de resposta obrigatório: pt-BR.', $promptText);
        }
    }

    public function test_custom_action_uses_sanitized_user_instruction(): void
    {
        $builder = new DefaultPromptBuilder('pt-BR');
        $promptText = $builder->buildPrompt(ActionType::CUSTOM, 'Explique o erro no terminal', 'en-US');

        $this->assertStringContainsString('Explique o erro no terminal', $promptText);
        $this->assertStringContainsString('Idioma de resposta obrigatório: en-US.', $promptText);
    }

    public function test_builds_ai_prompt_dto_with_formatted_options(): void
    {
        $builder = new DefaultPromptBuilder('pt-BR');
        $dto = $builder->buildAiPromptDTO(
            imagePath: '/tmp/test.png',
            action: ActionType::EXPLAIN,
            userInstruction: 'Como resolver este bug?',
            language: 'es-ES'
        );

        $this->assertInstanceOf(AiPromptDTO::class, $dto);
        $this->assertEquals('/tmp/test.png', $dto->imagePath);
        $this->assertEquals(ActionType::EXPLAIN, $dto->actionType);
        $this->assertEquals('Como resolver este bug?', $dto->userInstruction);
        $this->assertEquals('es-ES', $dto->options['language']);
        $this->assertStringContainsString('DIRETRIZ DE SEGURANÇA', $dto->options['formatted_prompt']);
    }
}
