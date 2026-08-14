<?php

namespace App\Enums;

enum AnalysisStatus: string
{
    case SUCCESS = 'success';
    case FAILED = 'failed';
}
