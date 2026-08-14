<?php

namespace App\Enums;

enum OnboardingStep: string
{
    case FIRST_RUN = 'first_run';
    case PERMISSIONS = 'permissions';
    case AI_CONFIGURATION = 'ai_configuration';
    case MONITOR_CONFIGURATION = 'monitor_configuration';
    case REGION_CONFIGURATION = 'region_configuration';
    case TESTING = 'testing';
    case COMPLETED = 'completed';
}
