<?php

namespace Tests\Unit;

use App\Enums\ActionType;
use App\Enums\AiProviderType;
use App\Enums\MonitorState;
use PHPUnit\Framework\TestCase;

class EnumTest extends TestCase
{
    public function test_monitor_state_enum_values(): void
    {
        $this->assertEquals('stopped', MonitorState::STOPPED->value);
        $this->assertEquals('starting', MonitorState::STARTING->value);
        $this->assertEquals('monitoring', MonitorState::MONITORING->value);
        $this->assertEquals('paused', MonitorState::PAUSED->value);
        $this->assertEquals('processing', MonitorState::PROCESSING->value);
        $this->assertEquals('error', MonitorState::ERROR->value);
    }

    public function test_ai_provider_type_enum_values(): void
    {
        $this->assertEquals('mock', AiProviderType::MOCK->value);
        $this->assertEquals('openai', AiProviderType::OPENAI->value);
        $this->assertEquals('anthropic', AiProviderType::ANTHROPIC->value);
        $this->assertEquals('gemini', AiProviderType::GEMINI->value);
    }

    public function test_action_type_enum_values(): void
    {
        $this->assertEquals('interpret', ActionType::INTERPRET->value);
        $this->assertEquals('translate', ActionType::TRANSLATE->value);
        $this->assertEquals('answer', ActionType::ANSWER->value);
        $this->assertEquals('explain', ActionType::EXPLAIN->value);
        $this->assertEquals('summarize', ActionType::SUMMARIZE->value);
        $this->assertEquals('custom', ActionType::CUSTOM->value);
    }
}
