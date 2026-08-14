<?php

namespace Tests\Unit;

use App\DTO\AiPromptDTO;
use App\DTO\AiResponseDTO;
use App\DTO\CaptureResultDTO;
use App\DTO\MonitorConfigDTO;
use App\DTO\ScreenRegionDTO;
use App\Enums\ActionType;
use App\Enums\AiProviderType;
use PHPUnit\Framework\TestCase;

class DtoTest extends TestCase
{
    public function test_screen_region_dto_serialization(): void
    {
        $dto = new ScreenRegionDTO(x: 10, y: 20, width: 800, height: 600, displayId: 1);
        $array = $dto->toArray();

        $this->assertEquals(10, $array['x']);
        $this->assertEquals(20, $array['y']);
        $this->assertEquals(800, $array['width']);
        $this->assertEquals(600, $array['height']);
        $this->assertEquals(1, $array['display_id']);

        $restored = ScreenRegionDTO::fromArray($array);
        $this->assertEquals($dto->x, $restored->x);
        $this->assertEquals($dto->y, $restored->y);
        $this->assertEquals($dto->width, $restored->width);
        $this->assertEquals($dto->height, $restored->height);
        $this->assertEquals($dto->displayId, $restored->displayId);
    }

    public function test_capture_result_dto_contract(): void
    {
        $dto = new CaptureResultDTO(
            filePath: '/tmp/capture.png',
            hash: 'abc123hash',
            timestamp: 1700000000,
            hasChanged: true,
            dimensions: ['width' => 1920, 'height' => 1080]
        );

        $array = $dto->toArray();
        $this->assertEquals('/tmp/capture.png', $array['file_path']);
        $this->assertTrue($array['has_changed']);

        $restored = CaptureResultDTO::fromArray($array);
        $this->assertEquals($dto->filePath, $restored->filePath);
        $this->assertEquals($dto->hash, $restored->hash);
    }

    public function test_ai_prompt_dto(): void
    {
        $dto = new AiPromptDTO(
            imagePath: '/tmp/test.png',
            actionType: ActionType::TRANSLATE,
            userInstruction: 'Traduzir para Inglês'
        );

        $array = $dto->toArray();
        $this->assertEquals('translate', $array['action_type']);
        $this->assertEquals('Traduzir para Inglês', $array['user_instruction']);

        $restored = AiPromptDTO::fromArray($array);
        $this->assertEquals(ActionType::TRANSLATE, $restored->actionType);
    }

    public function test_ai_response_dto(): void
    {
        $dto = new AiResponseDTO(
            text: 'Resposta Mock',
            success: true,
            provider: 'mock',
            model: 'mock-v1'
        );

        $array = $dto->toArray();
        $this->assertEquals('Resposta Mock', $array['text']);
        $this->assertEquals('mock', $array['provider']);

        $restored = AiResponseDTO::fromArray($array);
        $this->assertEquals($dto->text, $restored->text);
    }

    public function test_monitor_config_dto(): void
    {
        $region = new ScreenRegionDTO(x: 0, y: 0, width: 100, height: 100);
        $dto = new MonitorConfigDTO(
            intervalMs: 2000,
            selectedRegion: $region,
            aiProvider: AiProviderType::MOCK,
            actionType: ActionType::EXPLAIN
        );

        $array = $dto->toArray();
        $this->assertEquals(2000, $array['interval_ms']);
        $this->assertEquals('mock', $array['ai_provider']);

        $restored = MonitorConfigDTO::fromArray($array);
        $this->assertEquals(2000, $restored->intervalMs);
        $this->assertEquals(ActionType::EXPLAIN, $restored->actionType);
    }
}
