<?php

namespace Tests\Unit;

use App\DTO\ScreenRegionDTO;
use App\Events\RegionCleared;
use App\Events\RegionSelected;
use App\Services\Screen\ScreenRegionService;
use Illuminate\Support\Facades\Event;
use InvalidArgumentException;
use Tests\TestCase;

class ScreenRegionServiceTest extends TestCase
{
    public function test_initial_region_is_null(): void
    {
        $service = new ScreenRegionService;
        $this->assertNull($service->getRegion());
    }

    public function test_can_set_valid_region_and_dispatch_event(): void
    {
        Event::fake();

        $service = new ScreenRegionService;
        $dto = new ScreenRegionDTO(x: 50, y: 100, width: 800, height: 600);

        $saved = $service->setRegion($dto);

        $this->assertEquals($dto, $saved);
        $this->assertEquals($dto, $service->getRegion());

        Event::assertDispatched(RegionSelected::class, function ($event) {
            return $event->region->x === 50 && $event->region->width === 800;
        });
    }

    public function test_throws_exception_on_zero_or_negative_dimensions(): void
    {
        $service = new ScreenRegionService;

        $this->expectException(InvalidArgumentException::class);
        $service->setRegion(new ScreenRegionDTO(x: 0, y: 0, width: 0, height: 100));
    }

    public function test_throws_exception_on_negative_coordinates(): void
    {
        $service = new ScreenRegionService;

        $this->expectException(InvalidArgumentException::class);
        $service->setRegion(new ScreenRegionDTO(x: -10, y: 0, width: 100, height: 100));
    }

    public function test_can_clear_region_and_dispatch_event(): void
    {
        Event::fake();

        $service = new ScreenRegionService;
        $service->setRegion(new ScreenRegionDTO(x: 10, y: 10, width: 100, height: 100));

        $service->clearRegion('Testing clear');

        $this->assertNull($service->getRegion());
        Event::assertDispatched(RegionCleared::class);
    }
}
