<?php

namespace Tests\Unit;

use App\DTO\AnalysisHistoryDTO;
use App\Enums\ActionType;
use App\Enums\AnalysisStatus;
use Tests\TestCase;

class AnalysisHistoryDTOTest extends TestCase
{
    public function test_dto_array_transformation_and_creation(): void
    {
        $dto = new AnalysisHistoryDTO(
            id: 1,
            actionType: ActionType::TRANSLATE,
            provider: 'gemini',
            model: 'gemini-1.5-flash',
            status: AnalysisStatus::SUCCESS,
            responseText: 'Texto traduzido',
            errorMessage: null,
            durationMs: 145.2,
            userInstruction: null,
            language: 'pt-BR',
            createdAt: '2026-08-14T12:00:00Z'
        );

        $array = $dto->toArray();

        $this->assertEquals(1, $array['id']);
        $this->assertEquals('translate', $array['action_type']);
        $this->assertEquals('gemini', $array['provider']);
        $this->assertEquals('success', $array['status']);
        $this->assertEquals('Texto traduzido', $array['response_text']);

        $restored = AnalysisHistoryDTO::fromArray($array);

        $this->assertEquals($dto->id, $restored->id);
        $this->assertEquals($dto->actionType, $restored->actionType);
        $this->assertEquals($dto->status, $restored->status);
        $this->assertEquals($dto->responseText, $restored->responseText);
    }
}
