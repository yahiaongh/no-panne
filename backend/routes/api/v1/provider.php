<?php

use App\Http\Controllers\Api\V1\Provider\ProviderProfileController;
use App\Http\Controllers\Api\V1\Provider\ProviderRegisterController;
use App\Http\Controllers\Api\V1\Provider\ProviderRequestController;
use Illuminate\Support\Facades\Route;

Route::prefix('provider')->group(function () {
    Route::post('/register', [ProviderRegisterController::class, 'store'])->middleware('auth:sanctum');

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/profile', [ProviderProfileController::class, 'show']);
        Route::put('/profile', [ProviderProfileController::class, 'update']);
        Route::put('/availability', [ProviderProfileController::class, 'availability']);
        Route::put('/location', [ProviderProfileController::class, 'location']);

        Route::get('/requests/active', [ProviderRequestController::class, 'active']);
        Route::post('/requests/{serviceRequest}/accept', [ProviderRequestController::class, 'accept']);
        Route::post('/requests/{serviceRequest}/reject', [ProviderRequestController::class, 'reject']);
        Route::post('/requests/{serviceRequest}/status', [ProviderRequestController::class, 'updateStatus']);
        Route::post('/requests/{serviceRequest}/complete', [ProviderRequestController::class, 'complete']);
    });
});