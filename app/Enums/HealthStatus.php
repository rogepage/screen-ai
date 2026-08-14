<?php

namespace App\Enums;

enum HealthStatus: string
{
    case HEALTHY = 'healthy';
    case DEGRADED = 'degraded';
    case ERROR = 'error';
}
