<?php

namespace Tests\Feature;

use App\DTO\CaptureResultDTO;
use App\Enums\ActionType;
use App\Enums\AnalysisStatus;
use App\Models\AnalysisHistory;
use App\Services\Screen\ScreenMonitorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AnalysisHistoryIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempDir = storage_path('framework/testing/history_integration_test');

        if (!File::exists($this->tempDir)) {
            File::makeDirectory($this->tempDir, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        if (File::exists($this->tempDir)) {
            File::deleteDirectory($this->tempDir);
        }
        parent::tearDown();
    }

    public function test_analysis_pipeline_automatically_persists_history_record(): void
    {
        // 1. Simular uma captura de tela
        $srcPath = $this->tempDir . '/frame_history.png';
        $img = imagecreatetruecolor(400, 300);
        $magenta = imagecolorallocate($img, 255, 0, 255);
        imagefill($img, 0, 0, $magenta);
        imagepng($img, $srcPath);

        $capture = new CaptureResultDTO(
            filePath: $srcPath,
            hash: md5_file($srcPath),
            timestamp: time(),
            hasChanged: true
        );

        $monitor = $this->app->make(ScreenMonitorService::class);
        $monitor->processCapture($capture);

        // 2. Executar análise via HTTP
        $response = $this->postJson('/api/ai/analyze', [
            'action_type' => 'summarize',
            'user_instruction' => 'Resuma o erro da tela',
            'language' => 'pt-BR',
        ]);

        $response->assertStatus(200);

        // 3. Verificar no banco de dados SQLite se o histórico foi gravado
        $this->assertDatabaseHas('analysis_histories', [
            'action_type' => 'summarize',
            'provider' => 'mock',
            'status' => 'success',
            'language' => 'pt-BR',
        ]);
    }

    public function test_can_query_history_api_with_pagination_and_filters(): void
    {
        AnalysisHistory::create([
            'action_type' => ActionType::INTERPRET,
            'provider' => 'mock',
            'status' => AnalysisStatus::SUCCESS,
            'response_text' => 'Texto interpretado',
        ]);

        AnalysisHistory::create([
            'action_type' => ActionType::TRANSLATE,
            'provider' => 'gemini',
            'status' => AnalysisStatus::FAILED,
            'error_message' => 'Erro na tradução',
        ]);

        // Consulta geral
        $response = $this->getJson('/api/history?page=1&per_page=10');
        $response->assertStatus(200);
        $response->assertJsonPath('total', 2);

        // Filtro por action_type
        $filterResponse = $this->getJson('/api/history?action_type=translate');
        $filterResponse->assertStatus(200);
        $filterResponse->assertJsonPath('total', 1);
        $filterResponse->assertJsonPath('items.0.action_type', 'translate');
    }

    public function test_can_get_single_history_item_and_delete(): void
    {
        $item = AnalysisHistory::create([
            'action_type' => ActionType::EXPLAIN,
            'provider' => 'mock',
            'status' => AnalysisStatus::SUCCESS,
            'response_text' => 'Explicação completa do código.',
        ]);

        // Obter detalhes
        $getResponse = $this->getJson('/api/history/' . $item->id);
        $getResponse->assertStatus(200);
        $getResponse->assertJsonPath('item.response_text', 'Explicação completa do código.');

        // Excluir item
        $deleteResponse = $this->deleteJson('/api/history/' . $item->id);
        $deleteResponse->assertStatus(200);
        $this->assertDatabaseMissing('analysis_histories', ['id' => $item->id]);
    }

    public function test_can_clear_all_history_records(): void
    {
        AnalysisHistory::create(['action_type' => ActionType::INTERPRET, 'provider' => 'mock', 'status' => AnalysisStatus::SUCCESS]);
        AnalysisHistory::create(['action_type' => ActionType::ANSWER, 'provider' => 'mock', 'status' => AnalysisStatus::SUCCESS]);

        $this->assertDatabaseCount('analysis_histories', 2);

        $clearResponse = $this->deleteJson('/api/history');
        $clearResponse->assertStatus(200);

        $this->assertDatabaseCount('analysis_histories', 0);
    }
}
