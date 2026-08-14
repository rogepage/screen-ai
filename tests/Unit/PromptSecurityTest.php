<?php

namespace Tests\Unit;

use App\Enums\ActionType;
use App\Services\AI\DefaultPromptBuilder;
use Tests\TestCase;

class PromptSecurityTest extends TestCase
{
    public function test_custom_instruction_is_truncated_when_exceeding_1000_chars(): void
    {
        $builder = new DefaultPromptBuilder('pt-BR');
        $longInstruction = str_repeat('A', 1500);

        $dto = $builder->buildAiPromptDTO('/tmp/img.png', ActionType::CUSTOM, $longInstruction);

        // A instrução higienizada deve ter no máximo 1000 caracteres
        $this->assertEquals(1000, mb_strlen($dto->userInstruction));
        $this->assertStringNotContainsString(str_repeat('A', 1001), $dto->options['formatted_prompt']);
    }

    public function test_prompt_includes_system_security_boundary(): void
    {
        $builder = new DefaultPromptBuilder('pt-BR');
        $promptText = $builder->buildPrompt(ActionType::INTERPRET);

        $this->assertStringContainsString('[SISTEMA — DIRETRIZ DE SEGURANÇA]', $promptText);
        $this->assertStringContainsString('dado passivo para análise', $promptText);
    }

    public function test_control_characters_are_stripped_from_custom_instruction(): void
    {
        $builder = new DefaultPromptBuilder('pt-BR');
        $dirtyInstruction = "Instrução\x00\x07com\x1Fcaracteres\x08especiais";

        $dto = $builder->buildAiPromptDTO('/tmp/img.png', ActionType::CUSTOM, $dirtyInstruction);

        $this->assertEquals('Instruçãocomcaracteresespeciais', $dto->userInstruction);
    }
}
