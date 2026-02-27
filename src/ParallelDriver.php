<?php

namespace JackBayliss\DuskParallel;

class ParallelDriver
{
    /**
     * The default base port for ChromeDriver instances.
     */
    const DEFAULT_BASE_PORT = 9515;

    public static function runningInParallel(): bool
    {
        return isset($_SERVER['TEST_TOKEN']) || isset($_ENV['TEST_TOKEN']);
    }

    public static function basePort(): int
    {
        return (int) ($_ENV['DUSK_DRIVER_BASE_PORT'] ?? env('DUSK_DRIVER_BASE_PORT') ?? static::DEFAULT_BASE_PORT);
    }

    public static function parallelDriverPort(): int
    {
        return static::basePort() + (int) ($_SERVER['TEST_TOKEN'] ?? $_ENV['TEST_TOKEN']);
    }

    public static function hasExplicitPort(array $arguments): bool
    {
        foreach ($arguments as $argument) {
            if (str_starts_with($argument, '--port=')) {
                return true;
            }
        }

        return false;
    }

    public static function hasExplicitDriverUrl(): bool
    {
        return isset($_ENV['DUSK_DRIVER_URL']) || env('DUSK_DRIVER_URL') !== null;
    }

    public static function resolveDriverArguments(array $arguments = []): array
    {
        if (static::runningInParallel() && ! static::hasExplicitDriverUrl() && ! static::hasExplicitPort($arguments)) {
            $arguments[] = '--port='.static::parallelDriverPort();
        }

        return $arguments;
    }

    public static function resolveDriverUrl(): string
    {
        $url = $_ENV['DUSK_DRIVER_URL'] ?? env('DUSK_DRIVER_URL');

        if (! $url && static::runningInParallel()) {
            $url = 'http://localhost:'.static::parallelDriverPort();
        }

        return $url ?? 'http://localhost:'.static::basePort();
    }
}
