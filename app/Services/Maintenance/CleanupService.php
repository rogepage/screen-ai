<?php

namespace App\Services\Maintenance;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Throwable;

class CleanupService
{
    /** @var array<string> */
    private array $cleanupDirectories;

    public function __construct()
    {
        $this->cleanupDirectories = [
            storage_path('app/captures'),
            storage_path('app/processed_captures'),
        ];
    }

    /**
     * Remove arquivos temporários mais antigos que o tempo limite em segundos.
     */
    public function cleanOrphanCaptures(int $olderThanSeconds = 300): int
    {
        $deletedCount = 0;
        $thresholdTime = time() - $olderThanSeconds;

        foreach ($this->cleanupDirectories as $dir) {
            if (! File::exists($dir)) {
                continue;
            }

            try {
                $files = File::files($dir);
                foreach ($files as $file) {
                    if ($file->getMTime() < $thresholdTime) {
                        File::delete($file->getRealPath());
                        $deletedCount++;
                    }
                }
            } catch (Throwable $e) {
                Log::warning('Erro ao limpar arquivos temporários em '.$dir.': '.$e->getMessage());
            }
        }

        return $deletedCount;
    }

    /**
     * Limpeza forçada de todos os arquivos temporários.
     */
    public function cleanAllTemporaryFiles(): int
    {
        $deletedCount = 0;

        foreach ($this->cleanupDirectories as $dir) {
            if (! File::exists($dir)) {
                continue;
            }

            try {
                $files = File::files($dir);
                foreach ($files as $file) {
                    File::delete($file->getRealPath());
                    $deletedCount++;
                }
            } catch (Throwable $e) {
                Log::warning('Erro ao limpar diretório temporário: '.$e->getMessage());
            }
        }

        return $deletedCount;
    }
}
