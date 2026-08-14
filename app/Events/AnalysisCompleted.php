<?php

namespace App\Events;

use App\DTO\AiResponseDTO;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AnalysisCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly AiResponseDTO $response
    ) {}
}
