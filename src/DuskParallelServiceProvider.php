<?php

namespace JackBayliss\DuskParallel;

use Illuminate\Support\ServiceProvider;
use JackBayliss\DuskParallel\Console\DuskParallelCommand;

class DuskParallelServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                DuskParallelCommand::class,
            ]);
        }
    }
}
