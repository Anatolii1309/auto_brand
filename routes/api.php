<?php

use App\Http\Controllers\Api\CarOptionsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CarController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('cars', CarController::class);
Route::get('car-options/makes', [CarOptionsController::class, 'getMakes']);
Route::get('car-options/models/{make}', [CarOptionsController::class, 'getModels']);
