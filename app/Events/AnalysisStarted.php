<?php

namespace App\Events;

use App\DTO\AiPromptDTO;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AnalysisStarted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly AiPromptDTO $prompt
    ) {}
}
