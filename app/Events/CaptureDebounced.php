<?php

namespace App\Events;

use App\DTO\CaptureResultDTO;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CaptureDebounced
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly CaptureResultDTO $capture
    ) {}
}
