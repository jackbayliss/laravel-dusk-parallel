<?php

namespace JackBayliss\DuskParallel\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class SwitchDatabaseForParallelTesting
{
    public function handle(Request $request, Closure $next): mixed
    {
        if ($token = $request->cookies->get('dusk_db_token')) {
            $connection = config('database.default');
            $currentDatabase = config("database.connections.{$connection}.database");
            $baseDatabase = preg_replace('/_test_\d+$/', '', $currentDatabase);
            $testDatabase = "{$baseDatabase}_test_{$token}";

            foreach (array_keys(config('database.connections')) as $name) {
                Config::set("database.connections.{$name}.database", $testDatabase);
                DB::purge($name);
            }

            Cache::forgetDriver(config('cache.default'));
        }

        return $next($request);
    }
}
