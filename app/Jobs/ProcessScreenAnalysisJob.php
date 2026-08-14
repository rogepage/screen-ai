<?php

namespace App\Jobs;

use App\DTO\CaptureResultDTO;
use App\Enums\ActionType;
use App\Services\AI\AnalysisService;
use App\Services\Screen\ImageProcessorInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessScreenAnalysisJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly CaptureResultDTO $capture,
        public readonly ActionType $actionType = ActionType::INTERPRET,
        public readonly ?string $userInstruction = null,
        public readonly ?string $language = null
    ) {}

    public function handle(
        ImageProcessorInterface $imageProcessor,
        AnalysisService $analysisService
    ): void {
        try {
            $processedImage = $imageProcessor->process($this->capture);

            $analysisService->analyze(
                image: $processedImage,
                action: $this->actionType,
                userInstruction: $this->userInstruction,
                language: $this->language
            );
        } catch (Throwable $e) {
            Log::error('Erro ao executar Job de Análise Contínua de Tela: ' . $e->getMessage(), [
                'capture_path' => $this->capture->filePath,
                'action_type' => $this->actionType->value,
            ]);
        }
    }
}
