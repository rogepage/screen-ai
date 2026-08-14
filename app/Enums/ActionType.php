<?php

namespace App\Enums;

enum ActionType: string
{
    case INTERPRET = 'interpret';
    case TRANSLATE = 'translate';
    case ANSWER = 'answer';
    case EXPLAIN = 'explain';
    case SUMMARIZE = 'summarize';
    case CUSTOM = 'custom';
}
