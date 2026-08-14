<?php

namespace Tests\Unit;

use App\Services\Screen\MockChangeDetector;
use PHPUnit\Framework\TestCase;

class ChangeDetectorTest extends TestCase
{
    public function test_mock_change_detector_returns_configured_boolean(): void
    {
        $detector = new MockChangeDetector(true);
        $this->assertTrue($detector->hasChanged('/path/img1.png', '/path/img2.png'));

        $detector->setShouldAlwaysDetectChange(false);
        $this->assertFalse($detector->hasChanged('/path/img1.png', '/path/img2.png'));
    }
}
