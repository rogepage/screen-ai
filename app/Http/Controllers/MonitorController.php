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

use App\Enums\AnalysisStatus;
use App\Services\AI\AnalysisHistoryService;
use App\Services\AI\AnalysisService;
use App\Services\Native\NativeBridgeService;
use App\Services\Screen\ImageProcessorInterface;

class MonitorController extends Controller
{
    public function __construct(
        private readonly ScreenMonitorService $monitorService,
        private readonly ScreenRegionService $regionService,
        private readonly ScreenCaptureInterface $captureService,
        private readonly AiManagerService $aiManagerService,
        private readonly ImageProcessorInterface $imageProcessor,
        private readonly AnalysisService $analysisService,
        private readonly AnalysisHistoryService $historyService,
        private readonly NativeBridgeService $nativeBridge
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
            $hasChanged = $this->monitorService->processCapture($captureResult);

            return response()->json([
                'message' => 'Frame capturado com sucesso',
                'capture' => array_merge($captureResult->toArray(), [
                    'has_changed' => $hasChanged,
                ]),
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
        $instruction = $request->input('user_instruction');
        $language = $request->input('language');

        $lastCapture = $this->monitorService->getLastCapture();

        if ($lastCapture === null) {
            // Se ainda não houver captura em memória, tenta realizar uma para demonstrar no teste/UI
            $region = $this->regionService->getRegion() ?? new ScreenRegionDTO(x: 0, y: 0, width: 800, height: 600);
            try {
                $lastCapture = $this->captureService->captureRegion($region);
                $this->monitorService->processCapture($lastCapture);
            } catch (\Throwable $e) {
                return response()->json([
                    'error' => 'Nenhuma captura de tela disponível para análise. Execute uma captura primeiro.',
                ], 422);
            }
        }

        try {
            $processedImage = $this->imageProcessor->process($lastCapture);
            $responseDTO = $this->analysisService->analyze(
                image: $processedImage,
                action: $actionType,
                userInstruction: $instruction,
                language: $language
            );

            return response()->json([
                'action_type' => $actionType->value,
                'response' => $responseDTO->toArray(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Falha ao analisar a imagem: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getHistory(Request $request): JsonResponse
    {
        $page = (int) $request->input('page', 1);
        $perPage = (int) $request->input('per_page', 20);
        $actionStr = $request->input('action_type');
        $statusStr = $request->input('status');

        $action = $actionStr ? ActionType::tryFrom($actionStr) : null;
        $status = $statusStr ? AnalysisStatus::tryFrom($statusStr) : null;

        $historyData = $this->historyService->listHistory($page, $perPage, $action, $status);

        return response()->json([
            'items' => array_map(fn ($dto) => $dto->toArray(), $historyData['items']),
            'total' => $historyData['total'],
            'page' => $historyData['page'],
            'per_page' => $historyData['per_page'],
            'last_page' => $historyData['last_page'],
        ]);
    }

    public function getHistoryItem(int $id): JsonResponse
    {
        $item = $this->historyService->getAnalysis($id);

        if (!$item) {
            return response()->json(['error' => 'Registro de análise não encontrado.'], 404);
        }

        return response()->json(['item' => $item->toArray()]);
    }

    public function deleteHistoryItem(int $id): JsonResponse
    {
        $deleted = $this->historyService->deleteAnalysis($id);

        if (!$deleted) {
            return response()->json(['error' => 'Registro não encontrado para exclusão.'], 404);
        }

        return response()->json(['message' => 'Registro de análise excluído com sucesso.']);
    }

    public function clearHistory(): JsonResponse
    {
        $this->historyService->clearHistory();

        return response()->json(['message' => 'Todo o histórico de análises foi limpo com sucesso.']);
    }

    public function toggleOverlay(): JsonResponse
    {
        $isOpen = $this->nativeBridge->toggleOverlay();

        return response()->json([
            'message' => $isOpen ? 'Overlay aberto com sucesso' : 'Overlay fechado',
            'overlay_open' => $isOpen,
        ]);
    }

    public function getNativeStatus(): JsonResponse
    {
        return response()->json($this->nativeBridge->getNativeStatus());
    }
}

