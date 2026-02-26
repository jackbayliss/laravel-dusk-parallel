<?php

namespace JackBayliss\DuskParallel;

use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Support\ServiceProvider;
use JackBayliss\DuskParallel\Console\DuskParallelCommand;
use JackBayliss\DuskParallel\Http\Middleware\SwitchDatabaseForParallelTesting;

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

    /**
     * Bootstrap the service provider.
     */
    public function boot(): void
    {
        if (! $this->app->environment('testing')) {
            return;
        }

        // Register the route that sets the dusk_db_token cookie, allowing the browser
        // to carry its worker's database token with every subsequent request.
        $this->loadRoutesFrom(__DIR__ . '/../routes/testing.php');

        // Exclude dusk_db_token from encryption so it can be read as plain text
        // before EncryptCookies processes the other cookies.
        $this->app->afterResolving(EncryptCookies::class, function (EncryptCookies $middleware) {
            $middleware->disableFor('dusk_db_token');
        });

        // Prepend to the web group so the database is switched before StartSession
        // (which may connect to the DB) and before any authenticated route handlers.
        $this->app['router']->prependMiddlewareToGroup('web', SwitchDatabaseForParallelTesting::class);
    }
}
