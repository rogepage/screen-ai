<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RegionCleared
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $reason = 'User cleared selection'
    ) {}
}
