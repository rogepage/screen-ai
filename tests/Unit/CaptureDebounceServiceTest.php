<?php

namespace Tests\Unit;

use App\DTO\CaptureResultDTO;
use App\Events\CaptureDebounced;
use App\Services\Screen\CaptureDebounceService;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class CaptureDebounceServiceTest extends TestCase
{
    public function test_debounce_allows_first_change_and_dispatches_event(): void
    {
        Event::fake();

        $service = new CaptureDebounceService(debounceMs: 500);
        $capture = new CaptureResultDTO(
            filePath: '/tmp/frame1.png',
            hash: 'hash1',
            timestamp: time(),
            hasChanged: true
        );

        $processed = $service->shouldProcess($capture);

        $this->assertTrue($processed);
        Event::assertDispatched(CaptureDebounced::class);
    }

    public function test_debounce_filters_out_rapid_subsequent_changes(): void
    {
        Event::fake();

        $service = new CaptureDebounceService(debounceMs: 1000);
        $capture = new CaptureResultDTO(
            filePath: '/tmp/frame1.png',
            hash: 'hash1',
            timestamp: time(),
            hasChanged: true
        );

        // Primeira chamada passa
        $this->assertTrue($service->shouldProcess($capture));

        // Chamada imediata (0ms decorridos) deve ser descartada
        $this->assertFalse($service->shouldProcess($capture));

        $stats = $service->getStats();
        $this->assertEquals(1, $stats['debounced_count']);
        $this->assertEquals(1, $stats['dropped_count']);
    }
}
