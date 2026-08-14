<?php

use App\Http\Controllers\MonitorController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/selection', function () {
    return view('selection');
});

Route::get('/overlay', function () {
    return view('overlay');
});

Route::get('/settings', function () {
    return view('settings');
});

Route::get('/onboarding', function () {
    return view('onboarding');
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
    Route::get('/pipeline', [MonitorController::class, 'getPipelineStats']);
    Route::post('/cycle', [MonitorController::class, 'executeCycle']);
});

Route::post('/api/ai/analyze', [MonitorController::class, 'analyze']);

Route::prefix('api/history')->group(function () {
    Route::get('/', [MonitorController::class, 'getHistory']);
    Route::get('/{id}', [MonitorController::class, 'getHistoryItem']);
    Route::delete('/{id}', [MonitorController::class, 'deleteHistoryItem']);
    Route::delete('/', [MonitorController::class, 'clearHistory']);
});

Route::prefix('api/native')->group(function () {
    Route::post('/overlay/toggle', [MonitorController::class, 'toggleOverlay']);
    Route::get('/status', [MonitorController::class, 'getNativeStatus']);
});

Route::prefix('api/settings')->group(function () {
    Route::get('/', [MonitorController::class, 'getSettings']);
    Route::post('/', [MonitorController::class, 'saveSettings']);
    Route::post('/test-ai', [MonitorController::class, 'testAiConnection']);
    Route::post('/test-capture', [MonitorController::class, 'testCapturePreview']);
});

Route::prefix('api/startup')->group(function () {
    Route::get('/status', [MonitorController::class, 'getStartupStatus']);
    Route::post('/onboarding/complete', [MonitorController::class, 'completeOnboarding']);
    Route::post('/permissions/open-settings', [MonitorController::class, 'openPermissionSettings']);
});

Route::get('/api/health', [MonitorController::class, 'getHealth']);
Route::get('/api/diagnostics/export', [MonitorController::class, 'exportDiagnostics']);
Route::post('/api/maintenance/cleanup', [MonitorController::class, 'cleanupTemporaryFiles']);
