<?php

use App\Http\Controllers\MonitorController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('api/monitor')->group(function () {
    Route::get('/state', [MonitorController::class, 'getState']);
    Route::post('/start', [MonitorController::class, 'start']);
    Route::post('/stop', [MonitorController::class, 'stop']);
    Route::post('/pause', [MonitorController::class, 'pause']);
});

Route::post('/api/ai/analyze', [MonitorController::class, 'analyze']);

