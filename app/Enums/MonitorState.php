<?php

namespace App\Enums;

enum MonitorState: string
{
    case STOPPED = 'stopped';
    case STARTING = 'starting';
    case MONITORING = 'monitoring';
    case PAUSED = 'paused';
    case PROCESSING = 'processing';
    case ERROR = 'error';
}
