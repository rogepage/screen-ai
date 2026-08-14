<?php

namespace App\Services\Screen;

use Illuminate\Support\Facades\File;

class DesktopScreenshotWatcherService
{
    /**
     * @var array<string, bool>
     */
    private array $processedFiles = [];

    private string $watchFolder;

    /**
     * @var array<int, string>
     */
    private array $patterns;

    private int $stabilityDelayMs;

    /**
     * @param  array<int, string>|null  $patterns
     */
    public function __construct(
        ?string $watchFolder = null,
        ?array $patterns = null,
        int $stabilityDelayMs = 300
    ) {
        $this->watchFolder = $watchFolder ?? (string) config('screen_ai.watcher.watch_folder', env('HOME', '/Users/roger').'/Desktop');
        $this->patterns = $patterns ?? (array) config('screen_ai.watcher.patterns', [
            'Captura de Tela*.png',
            'Captura de Tela*.jpg',
            'Screenshot*.png',
            'Screenshot*.jpg',
        ]);
        $this->stabilityDelayMs = $stabilityDelayMs;
    }

    public function getWatchFolder(): string
    {
        return $this->watchFolder;
    }

    public function setWatchFolder(string $folder): void
    {
        $this->watchFolder = rtrim($folder, '/');
    }

    public function isFolderAccessible(): bool
    {
        return File::exists($this->watchFolder) && File::isDirectory($this->watchFolder) && is_readable($this->watchFolder);
    }

    /**
     * Inicializa a lista de arquivos conhecidos para não reprocessar capturas antigas existentes.
     */
    public function initializeExistingFilesAsSeen(): int
    {
        $existing = $this->findMatchingFiles();
        foreach ($existing as $file) {
            $this->processedFiles[$file] = true;
        }

        return count($existing);
    }

    /**
     * Retorna a lista de novos arquivos de captura encontrados.
     *
     * @return array<int, string>
     */
    public function scanNewScreenshots(): array
    {
        if (! $this->isFolderAccessible()) {
            return [];
        }

        $allFiles = $this->findMatchingFiles();
        $newFiles = [];

        foreach ($allFiles as $filePath) {
            if (isset($this->processedFiles[$filePath])) {
                continue;
            }

            // Validar que o arquivo é legível e possui tamanho > 0
            if (File::exists($filePath) && is_readable($filePath)) {
                $size = @filesize($filePath);
                if ($size !== false && $size > 0) {
                    $newFiles[] = $filePath;
                }
            }
        }

        // Ordenar por data de modificação crescente (mais antigo primeiro)
        usort($newFiles, function ($a, $b) {
            return (@filemtime($a) ?: 0) <=> (@filemtime($b) ?: 0);
        });

        return $newFiles;
    }

    public function markAsProcessed(string $filePath): void
    {
        $this->processedFiles[$filePath] = true;
    }

    public function isProcessed(string $filePath): bool
    {
        return isset($this->processedFiles[$filePath]);
    }

    public function clearProcessedHistory(): void
    {
        $this->processedFiles = [];
    }

    /**
     * @return array<int, string>
     */
    private function findMatchingFiles(): array
    {
        if (! File::exists($this->watchFolder)) {
            return [];
        }

        $matched = [];

        foreach ($this->patterns as $pattern) {
            $globPath = $this->watchFolder.'/'.$pattern;
            $files = glob($globPath);

            if ($files !== false) {
                foreach ($files as $file) {
                    $matched[] = $file;
                }
            }
        }

        return array_values(array_unique($matched));
    }
}
