<?php

namespace Tests\Unit;

use App\DTO\AnalysisHistoryDTO;
use App\Enums\ActionType;
use App\Enums\AnalysisStatus;
use App\Repositories\EloquentAnalysisHistoryRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EloquentAnalysisHistoryRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentAnalysisHistoryRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new EloquentAnalysisHistoryRepository();
    }

    public function test_can_create_and_find_analysis_history(): void
    {
        $dto = new AnalysisHistoryDTO(
            actionType: ActionType::INTERPRET,
            provider: 'mock',
            model: 'mock-v1',
            status: AnalysisStatus::SUCCESS,
            responseText: 'Tela analisada com sucesso',
            durationMs: 80.5,
            language: 'pt-BR'
        );

        $created = $this->repository->create($dto);

        $this->assertNotNull($created->id);
        $this->assertEquals(ActionType::INTERPRET, $created->actionType);

        $found = $this->repository->findById($created->id);
        $this->assertNotNull($found);
        $this->assertEquals('Tela analisada com sucesso', $found->responseText);
    }

    public function test_get_paginated_with_filters(): void
    {
        // Criar 3 registros
        $this->repository->create(new AnalysisHistoryDTO(
            actionType: ActionType::TRANSLATE,
            status: AnalysisStatus::SUCCESS,
            responseText: 'Traduzido 1'
        ));
        $this->repository->create(new AnalysisHistoryDTO(
            actionType: ActionType::TRANSLATE,
            status: AnalysisStatus::FAILED,
            errorMessage: 'Erro 2'
        ));
        $this->repository->create(new AnalysisHistoryDTO(
            actionType: ActionType::EXPLAIN,
            status: AnalysisStatus::SUCCESS,
            responseText: 'Explicado 3'
        ));

        // Buscar todos
        $all = $this->repository->getPaginated(page: 1, perPage: 10);
        $this->assertEquals(3, $all['total']);
        $this->assertCount(3, $all['items']);

        // Filtrar por TRANSLATE
        $translateOnly = $this->repository->getPaginated(page: 1, perPage: 10, action: ActionType::TRANSLATE);
        $this->assertEquals(2, $translateOnly['total']);

        // Filtrar por FAILED
        $failedOnly = $this->repository->getPaginated(page: 1, perPage: 10, status: AnalysisStatus::FAILED);
        $this->assertEquals(1, $failedOnly['total']);
    }

    public function test_delete_and_clear_all(): void
    {
        $item1 = $this->repository->create(new AnalysisHistoryDTO(actionType: ActionType::SUMMARIZE));
        $item2 = $this->repository->create(new AnalysisHistoryDTO(actionType: ActionType::ANSWER));

        $this->assertTrue($this->repository->delete($item1->id));
        $this->assertNull($this->repository->findById($item1->id));
        $this->assertNotNull($this->repository->findById($item2->id));

        $this->assertTrue($this->repository->clearAll());
        $this->assertNull($this->repository->findById($item2->id));
    }
}
