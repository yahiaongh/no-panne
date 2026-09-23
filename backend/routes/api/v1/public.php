<?php

use App\Http\Controllers\Api\V1\ReferenceDataController;
use Illuminate\Support\Facades\Route;

Route::get('/services', [ReferenceDataController::class, 'services']);
Route::get('/wilayas', [ReferenceDataController::class, 'wilayas']);