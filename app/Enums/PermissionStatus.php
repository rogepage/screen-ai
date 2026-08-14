<?php

namespace App\Enums;

enum PermissionStatus: string
{
    case GRANTED = 'granted';
    case DENIED = 'denied';
    case NOT_DETERMINED = 'not_determined';
    case UNKNOWN = 'unknown';
}
