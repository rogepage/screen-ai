<?php

namespace App\Services\AI;

use App\DTO\AiPromptDTO;
use App\DTO\AiResponseDTO;

interface AiProviderInterface
{
    public function getName(): string;

    public function process(AiPromptDTO $prompt): AiResponseDTO;
}
