<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::apiResource('user', App\Http\Controllers\UserController::class);

Route::apiResource('church', App\Http\Controllers\ChurchController::class);

Route::apiResource('diocese', App\Http\Controllers\DioceseController::class);

Route::apiResource('parish', App\Http\Controllers\ParishController::class);

Route::apiResource('priest', App\Http\Controllers\PriestController::class);

Route::apiResource('sub-parish', App\Http\Controllers\SubParishController::class);

Route::apiResource('activity-time', App\Http\Controllers\ActivityTimeController::class);

Route::apiResource('activity-time-exception', App\Http\Controllers\ActivityTimeExceptionController::class);
