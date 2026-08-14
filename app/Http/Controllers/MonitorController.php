<?php

namespace App\Http\Controllers;

use App\DTO\AiPromptDTO;
use App\DTO\MonitorConfigDTO;
use App\Enums\ActionType;
use App\Services\AI\AiManagerService;
use App\Services\Screen\ScreenMonitorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MonitorController extends Controller
{
    public function __construct(
        private readonly ScreenMonitorService $monitorService,
        private readonly AiManagerService $aiManagerService
    ) {}

    public function getState(): JsonResponse
    {
        return response()->json([
            'state' => $this->monitorService->getState()->value,
            'config' => $this->monitorService->getConfig()?->toArray(),
        ]);
    }

    public function start(Request $request): JsonResponse
    {
        $actionTypeStr = $request->input('action_type', 'interpret');
        $actionType = ActionType::tryFrom($actionTypeStr) ?? ActionType::INTERPRET;

        $config = new MonitorConfigDTO(
            intervalMs: (int) $request->input('interval_ms', 1000),
            actionType: $actionType
        );

        $this->monitorService->start($config);

        return response()->json([
            'message' => 'Monitor iniciado',
            'state' => $this->monitorService->getState()->value,
            'config' => $config->toArray(),
        ]);
    }

    public function stop(): JsonResponse
    {
        $this->monitorService->stop();

        return response()->json([
            'message' => 'Monitor parado',
            'state' => $this->monitorService->getState()->value,
        ]);
    }

    public function pause(): JsonResponse
    {
        $this->monitorService->pause();

        return response()->json([
            'message' => 'Monitor pausado',
            'state' => $this->monitorService->getState()->value,
        ]);
    }

    public function analyze(Request $request): JsonResponse
    {
        $actionTypeStr = $request->input('action_type', 'interpret');
        $actionType = ActionType::tryFrom($actionTypeStr) ?? ActionType::INTERPRET;
        $instruction = $request->input('user_instruction', 'Test instruction');

        $prompt = new AiPromptDTO(
            actionType: $actionType,
            userInstruction: $instruction
        );

        $response = $this->aiManagerService->analyze($prompt);

        return response()->json([
            'prompt' => $prompt->toArray(),
            'response' => $response->toArray(),
        ]);
    }
}
