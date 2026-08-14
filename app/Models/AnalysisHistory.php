<?php

namespace App\Models;

use App\Enums\ActionType;
use App\Enums\AnalysisStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalysisHistory extends Model
{
    use HasFactory;

    protected $table = 'analysis_histories';

    protected $fillable = [
        'action_type',
        'provider',
        'model',
        'status',
        'response_text',
        'error_message',
        'duration_ms',
        'user_instruction',
        'language',
    ];

    protected $casts = [
        'action_type' => ActionType::class,
        'status' => AnalysisStatus::class,
        'duration_ms' => 'float',
    ];
}
