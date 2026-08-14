<?php

namespace App\Events\Native;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TogglePauseShortcutPressed
{
    use Dispatchable, SerializesModels;
}
