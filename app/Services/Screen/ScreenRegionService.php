<?php

namespace App\Services\Screen;

use App\DTO\ScreenRegionDTO;
use App\Events\RegionCleared;
use App\Events\RegionSelected;
use InvalidArgumentException;

class ScreenRegionService
{
    private ?ScreenRegionDTO $currentRegion = null;

    public function getRegion(): ?ScreenRegionDTO
    {
        return $this->currentRegion;
    }

    public function setRegion(ScreenRegionDTO $region): ScreenRegionDTO
    {
        if ($region->width <= 0 || $region->height <= 0) {
            throw new InvalidArgumentException('Largura e altura da região devem ser maiores que zero.');
        }

        if ($region->x < 0 || $region->y < 0) {
            throw new InvalidArgumentException('Coordenadas X e Y não podem ser negativas.');
        }

        $this->currentRegion = $region;

        RegionSelected::dispatch($this->currentRegion);

        return $this->currentRegion;
    }

    public function clearRegion(string $reason = 'User requested'): void
    {
        $this->currentRegion = null;

        RegionCleared::dispatch($reason);
    }
}
