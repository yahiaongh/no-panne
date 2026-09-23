<?php

use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Versioned under /api/v1.
|
*/

Route::prefix('v1')->group(function () {

    Route::get('/health', fn () => ApiResponse::success([
        'service' => config('app.name'),
        'status' => 'ok',
        'time' => now()->toIso8601String(),
    ]));

    include __DIR__.'/api/v1/public.php';
    include __DIR__.'/api/v1/auth.php';
    include __DIR__.'/api/v1/client.php';
    include __DIR__.'/api/v1/provider.php';
});

Route::fallback(function (Request $request) {
    return ApiResponse::error('Ressource introuvable.', 404, 'not_found');
})->middleware('throttle:api');