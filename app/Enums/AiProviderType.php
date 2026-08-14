<?php

namespace App\Enums;

enum AiProviderType: string
{
    case MOCK = 'mock';
    case OPENAI = 'openai';
    case ANTHROPIC = 'anthropic';
    case GEMINI = 'gemini';
}
