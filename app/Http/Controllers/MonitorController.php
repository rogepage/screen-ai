<?php

namespace App\Http\Controllers;

use App\DTO\AiPromptDTO;
use App\DTO\AppSettingsDTO;
use App\DTO\MonitorConfigDTO;
use App\DTO\ScreenRegionDTO;
use App\Enums\ActionType;
use App\Enums\AnalysisStatus;
use App\Services\AI\AiManagerService;
use App\Services\AI\AnalysisHistoryService;
use App\Services\AI\AnalysisService;
use App\Services\Diagnostics\DiagnosticService;
use App\Services\Maintenance\CleanupService;
use App\Services\Native\NativeBridgeService;
use App\Services\Native\PermissionServiceInterface;
use App\Services\Observability\HealthCheckService;
use App\Services\Screen\ContinuousMonitorService;
use App\Services\Screen\ImageProcessorInterface;
use App\Services\Screen\ScreenCaptureInterface;
use App\Services\Screen\ScreenMonitorService;
use App\Services\Screen\ScreenRegionService;
use App\Services\Settings\AppSettingsService;
use App\Services\Startup\StartupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

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
        private readonly NativeBridgeService $nativeBridge,
        private readonly ContinuousMonitorService $continuousMonitor,
        private readonly AppSettingsService $settingsService,
        private readonly StartupService $startupService,
        private readonly PermissionServiceInterface $permissionService,
        private readonly HealthCheckService $healthService,
        private readonly DiagnosticService $diagnosticService,
        private readonly CleanupService $cleanupService
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
                'error' => 'Falha na captura: '.$e->getMessage(),
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
                'error' => 'Falha ao analisar a imagem: '.$e->getMessage(),
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

        if (! $item) {
            return response()->json(['error' => 'Registro de análise não encontrado.'], 404);
        }

        return response()->json(['item' => $item->toArray()]);
    }

    public function deleteHistoryItem(int $id): JsonResponse
    {
        $deleted = $this->historyService->deleteAnalysis($id);

        if (! $deleted) {
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

    public function executeCycle(): JsonResponse
    {
        $capture = $this->continuousMonitor->runCycle($this->monitorService);

        return response()->json([
            'message' => $capture ? 'Ciclo executado com captura de frame' : 'Ciclo executado (sem captura/mudança)',
            'capture' => $capture?->toArray(),
            'stats' => $this->continuousMonitor->getStats(),
        ]);
    }

    public function getPipelineStats(): JsonResponse
    {
        return response()->json([
            'state' => $this->monitorService->getState()->value,
            'stats' => $this->continuousMonitor->getStats(),
        ]);
    }

    public function getStartupStatus(): JsonResponse
    {
        return response()->json($this->startupService->checkStartupStatus());
    }

    public function completeOnboarding(): JsonResponse
    {
        $this->settingsService->markOnboardingCompleted();

        return response()->json(['message' => 'Onboarding concluído com sucesso.']);
    }

    public function openPermissionSettings(): JsonResponse
    {
        $this->permissionService->openSystemSettings();

        return response()->json(['message' => 'Ajustes do Sistema solicitados.']);
    }

    public function getSettings(): JsonResponse
    {
        return response()->json($this->settingsService->getSettings()->toArray());
    }

    public function saveSettings(Request $request): JsonResponse
    {
        $dto = AppSettingsDTO::fromArray($request->all());
        $errors = $this->settingsService->saveSettings($dto);

        if (! empty($errors)) {
            return response()->json(['errors' => $errors], 422);
        }

        return response()->json([
            'message' => 'Configurações salvas com sucesso.',
            'settings' => $this->settingsService->getSettings()->toArray(),
        ]);
    }

    public function testAiConnection(Request $request): JsonResponse
    {
        $providerName = $request->input('ai_provider');
        $model = $request->input('ai_model');

        try {
            $promptDTO = new AiPromptDTO(
                actionType: ActionType::INTERPRET,
                userInstruction: 'Teste de conexão com a IA.'
            );

            $response = $this->aiManagerService->analyze($promptDTO, $providerName);

            return response()->json([
                'success' => $response->success,
                'provider' => $response->provider,
                'model' => $response->model,
                'message' => $response->success ? 'Conexão realizada com sucesso.' : 'Não foi possível conectar à IA.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Não foi possível conectar à IA.',
            ], 500);
        }
    }

    public function testCapturePreview(): JsonResponse
    {
        $region = $this->regionService->getRegion() ?? new ScreenRegionDTO(x: 0, y: 0, width: 800, height: 600);

        try {
            $capture = $this->captureService->captureRegion($region);
            $processed = $this->imageProcessor->process($capture);

            return response()->json([
                'success' => true,
                'message' => 'Captura de tela realizada com sucesso.',
                'preview' => [
                    'file_path' => $processed->processedPath,
                    'width' => $processed->width,
                    'height' => $processed->height,
                    'file_size' => $processed->fileSize,
                    'format' => $processed->format,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Falha ao testar captura: '.$e->getMessage(),
            ], 500);
        }
    }

    public function getHealth(): JsonResponse
    {
        return response()->json($this->healthService->checkHealth());
    }

    public function exportDiagnostics(): JsonResponse
    {
        return response()->json($this->diagnosticService->generateReport());
    }

    public function cleanupTemporaryFiles(): JsonResponse
    {
        $deleted = $this->cleanupService->cleanAllTemporaryFiles();

        return response()->json([
            'message' => "Limpeza de manutenção concluída ({$deleted} arquivos removidos).",
            'deleted_count' => $deleted,
        ]);
    }
}
