<?php

use App\Http\Controllers\Api\V1\Auth\OtpAuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/send-otp', [OtpAuthController::class, 'sendOtp'])->middleware('throttle:otp');
    Route::post('/verify-otp', [OtpAuthController::class, 'verifyOtp'])->middleware('throttle:otp');
    Route::post('/refresh-token', [OtpAuthController::class, 'refreshToken'])->middleware('auth:sanctum');
});