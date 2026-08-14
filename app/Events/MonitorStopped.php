<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MonitorStopped
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $reason = 'User requested'
    ) {}
}
