<?php

namespace App\Services\Native;

use App\Enums\PermissionStatus;

interface PermissionServiceInterface
{
    public function getScreenRecordingStatus(): PermissionStatus;

    public function openSystemSettings(): void;

    public function explainRequirement(): string;
}
