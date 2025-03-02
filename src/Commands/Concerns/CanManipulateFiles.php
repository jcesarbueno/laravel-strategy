<?php

declare(strict_types=1);

namespace Jcesarbueno\LaravelStrategy\Commands\Concerns;

use Illuminate\Filesystem\Filesystem;
use ReflectionClass;

trait CanManipulateFiles
{
    /**
     * @param  array<string>  $paths
     */
    protected function checkForCollision(array $paths): bool
    {
        foreach ($paths as $path) {
            if (! $this->fileExists($path)) {
                continue;
            }

            return true;
        }

        return false;
    }

    /**
     * @param  array<string, list<string>|string>  $replacements
     */
    protected function copyStubToApp(string $stub, string $targetPath, array $replacements = []): void
    {
        $filesystem = app(Filesystem::class);

        if (! $this->fileExists($stubPath = base_path("stubs/laravel-strategy/{$stub}.stub"))) {
            $stubPath = $this->getDefaultStubPath()."/{$stub}.stub";
        }

        $stub = str($filesystem->get($stubPath));

        $stub = $stub->replace('{{ namespace }}', str($targetPath)->prepend('App\\')->beforeLast('/')->replace('/', '\\'));

        foreach ($replacements as $key => $replacement) {
            if (is_array($replacement)) {
                $replacement = implode("\n", $replacement);
            }
            $stub = $stub->replace("{{ {$key} }}", $replacement);
        }

        $stub = (string) $stub;

        $this->writeFile(app_path($targetPath), $stub);
    }

    protected function fileExists(string $path): bool
    {
        $filesystem = app(Filesystem::class);

        return $filesystem->exists($path);
    }

    protected function writeFile(string $path, string $contents): void
    {
        $filesystem = app(Filesystem::class);

        $filesystem->ensureDirectoryExists(
            pathinfo($path, PATHINFO_DIRNAME),
        );

        $filesystem->put($path, $contents);
    }

    protected function getDefaultStubPath(): string
    {
        $reflectionClass = new ReflectionClass($this);

        return (string) str((string) $reflectionClass->getFileName())
            ->beforeLast('Commands')
            ->append('../stubs');
    }
}
