<?php

namespace App\Events;

use App\DTO\ScreenRegionDTO;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RegionSelected
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly ScreenRegionDTO $region
    ) {}
}
