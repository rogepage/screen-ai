<?php

namespace Tests\Unit;

use App\Services\Maintenance\CleanupService;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class CleanupServiceTest extends TestCase
{
    public function test_cleanup_service_removes_temporary_files(): void
    {
        $dir = storage_path('app/captures');
        if (! File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $dummyFile = $dir.'/dummy_test.png';
        File::put($dummyFile, 'fake image content');
        $this->assertFileExists($dummyFile);

        $service = new CleanupService;
        $deleted = $service->cleanAllTemporaryFiles();

        $this->assertGreaterThanOrEqual(1, $deleted);
        $this->assertFileDoesNotExist($dummyFile);
    }
}
