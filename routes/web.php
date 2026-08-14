<?php

use App\Http\Controllers\MonitorController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/selection', function () {
    return view('selection');
});

Route::prefix('api/monitor')->group(function () {
    Route::get('/state', [MonitorController::class, 'getState']);
    Route::get('/region', [MonitorController::class, 'getRegion']);
    Route::post('/region', [MonitorController::class, 'setRegion']);
    Route::delete('/region', [MonitorController::class, 'clearRegion']);
    Route::post('/start', [MonitorController::class, 'start']);
    Route::post('/stop', [MonitorController::class, 'stop']);
    Route::post('/pause', [MonitorController::class, 'pause']);
    Route::post('/capture', [MonitorController::class, 'captureFrame']);
});

Route::post('/api/ai/analyze', [MonitorController::class, 'analyze']);

Route::prefix('api/history')->group(function () {
    Route::get('/', [MonitorController::class, 'getHistory']);
    Route::get('/{id}', [MonitorController::class, 'getHistoryItem']);
    Route::delete('/{id}', [MonitorController::class, 'deleteHistoryItem']);
    Route::delete('/', [MonitorController::class, 'clearHistory']);
});


