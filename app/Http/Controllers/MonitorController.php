<?php

namespace App\Http\Controllers;

use App\DTO\AiPromptDTO;
use App\DTO\MonitorConfigDTO;
use App\Enums\ActionType;
use App\DTO\ScreenRegionDTO;
use App\Services\AI\AiManagerService;
use App\Services\Screen\ScreenCaptureInterface;
use App\Services\Screen\ScreenMonitorService;
use App\Services\Screen\ScreenRegionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class MonitorController extends Controller
{
    public function __construct(
        private readonly ScreenMonitorService $monitorService,
        private readonly ScreenRegionService $regionService,
        private readonly ScreenCaptureInterface $captureService,
        private readonly AiManagerService $aiManagerService
    ) {}

    public function getState(): JsonResponse
    {
        return response()->json([
            'state' => $this->monitorService->getState()->value,
            'config' => $this->monitorService->getConfig()?->toArray(),
            'region' => $this->regionService->getRegion()?->toArray(),
        ]);
    }

    public function getRegion(): JsonResponse
    {
        $region = $this->regionService->getRegion();

        return response()->json([
            'region' => $region?->toArray(),
        ]);
    }

    public function setRegion(Request $request): JsonResponse
    {
        try {
            $dto = ScreenRegionDTO::fromArray($request->all());
            $savedRegion = $this->regionService->setRegion($dto);
            $this->monitorService->updateRegion($savedRegion);

            return response()->json([
                'message' => 'Região definida com sucesso',
                'region' => $savedRegion->toArray(),
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function clearRegion(): JsonResponse
    {
        $this->regionService->clearRegion();

        return response()->json([
            'message' => 'Região limpa com sucesso',
        ]);
    }

    public function start(Request $request): JsonResponse
    {
        $actionTypeStr = $request->input('action_type', 'interpret');
        $actionType = ActionType::tryFrom($actionTypeStr) ?? ActionType::INTERPRET;

        $config = new MonitorConfigDTO(
            intervalMs: (int) $request->input('interval_ms', 1000),
            selectedRegion: $this->regionService->getRegion(),
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

    public function captureFrame(): JsonResponse
    {
        $region = $this->regionService->getRegion() ?? new ScreenRegionDTO(x: 0, y: 0, width: 800, height: 600);

        try {
            $captureResult = $this->captureService->captureRegion($region);

            return response()->json([
                'message' => 'Frame capturado com sucesso',
                'capture' => $captureResult->toArray(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Falha na captura: ' . $e->getMessage(),
            ], 500);
        }
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

