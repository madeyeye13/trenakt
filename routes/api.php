<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Scaffolding only — no client consumes this yet. Every route below is
| namespaced under /api/v1 and, aside from the health check, requires a
| Sanctum personal access token (Authorization: Bearer <token>).
|
| Still needed before this is usable (none of this can be run remotely,
| the user has to run it locally):
|   1. composer update          (pulls in laravel/sanctum, added to composer.json)
|   2. php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
|   3. php artisan migrate      (creates the personal_access_tokens table)
|
*/

Route::prefix('v1')->group(function () {
    Route::get('/ping', function () {
        return response()->json(['status' => 'ok']);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
    });
});
