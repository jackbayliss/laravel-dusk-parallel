<?php

namespace JackBayliss\DuskParallel\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class SwitchDatabaseForParallelTesting
{
    public function handle(Request $request, Closure $next): mixed
    {
        if ($token = $request->cookies->get('dusk_db_token')) {
            $base = preg_replace('/_test_\d+$/', '', config('database.connections.' . config('database.default') . '.database'));
            $testDatabase = "{$base}_test_{$token}";

            foreach (array_keys(config('database.connections')) as $connection) {
                Config::set("database.connections.{$connection}.database", $testDatabase);
                DB::purge($connection);
            }
        }

        return $next($request);
    }
}
