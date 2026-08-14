<?php

namespace App\Events\Native;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ToggleOverlayShortcutPressed
{
    use Dispatchable, SerializesModels;
}
