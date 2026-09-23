<?php

use App\Http\Controllers\Api\V1\Client\ClientProfileController;
use App\Http\Controllers\Api\V1\Client\ClientRequestController;
use App\Http\Controllers\Api\V1\Client\ClientProviderController;
use Illuminate\Support\Facades\Route;

Route::prefix('client')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/profile', [ClientProfileController::class, 'show']);
    Route::put('/profile', [ClientProfileController::class, 'update']);

    Route::get('/requests', [ClientRequestController::class, 'index']);
    Route::post('/requests', [ClientRequestController::class, 'store']);
    Route::get('/requests/{serviceRequest}', [ClientRequestController::class, 'show']);
    Route::post('/requests/{serviceRequest}/cancel', [ClientRequestController::class, 'cancel']);

    Route::get('/providers/nearby', [ClientProviderController::class, 'nearby']);
    Route::get('/providers/{provider}', [ClientProviderController::class, 'show']);
});