<?php

namespace Tests\Feature;

use App\DTO\CaptureResultDTO;
use App\Events\ScreenChanged;
use App\Services\Screen\ChangeDetectorInterface;
use App\Services\Screen\MockChangeDetector;
use App\Services\Screen\ScreenMonitorService;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ScreenMonitorChangeDetectionTest extends TestCase
{
    public function test_screen_monitor_dispatches_screen_changed_event_when_change_detected(): void
    {
        Event::fake([ScreenChanged::class]);

        $mockDetector = new MockChangeDetector(true);
        $monitorService = new ScreenMonitorService($mockDetector);

        $capture = new CaptureResultDTO(
            filePath: '/tmp/frame1.png',
            hash: 'hash1',
            timestamp: time(),
            hasChanged: true,
            dimensions: ['x' => 0, 'y' => 0, 'width' => 100, 'height' => 100]
        );

        $result = $monitorService->processCapture($capture);

        $this->assertTrue($result);
        $this->assertEquals($capture, $monitorService->getLastCapture());

        Event::assertDispatched(ScreenChanged::class, function (ScreenChanged $event) use ($capture) {
            return $event->captureResult->filePath === $capture->filePath;
        });
    }

    public function test_screen_monitor_does_not_dispatch_event_when_no_change_detected(): void
    {
        Event::fake([ScreenChanged::class]);

        $mockDetector = new MockChangeDetector(false);
        $monitorService = new ScreenMonitorService($mockDetector);

        $capture = new CaptureResultDTO(
            filePath: '/tmp/frame2.png',
            hash: 'hash2',
            timestamp: time(),
            hasChanged: false,
            dimensions: ['x' => 0, 'y' => 0, 'width' => 100, 'height' => 100]
        );

        $result = $monitorService->processCapture($capture);

        $this->assertFalse($result);
        Event::assertNotDispatched(ScreenChanged::class);
    }
}
