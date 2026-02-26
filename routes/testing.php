<?php

use Illuminate\Support\Facades\Route;

// Sets a plain-text cookie that SwitchDatabaseForParallelTesting reads to route
// browser requests to the correct per-worker database during parallel Dusk tests.
Route::get('/__dusk/use-database/{token}', function (int $token) {
    return response('', 200)->cookie('dusk_db_token', (string) $token);
});
