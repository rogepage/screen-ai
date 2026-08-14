<?php

namespace App\Events;

use App\DTO\MonitorConfigDTO;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MonitorStarted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly MonitorConfigDTO $config
    ) {}
}
