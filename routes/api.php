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

Route::apiResource('user', App\Http\Controllers\UserController::class)->except('destroy');

Route::apiResource('church', App\Http\Controllers\ChurchController::class)->except('destroy');

Route::apiResource('diocese', App\Http\Controllers\DioceseController::class)->except('destroy');

Route::apiResource('parish', App\Http\Controllers\ParishController::class)->except('destroy');

Route::apiResource('priest', App\Http\Controllers\PriestController::class)->except('destroy');

Route::apiResource('sub-parish', App\Http\Controllers\SubParishController::class)->except('destroy');

Route::apiResource('activity-time', App\Http\Controllers\ActivityTimeController::class)->except('destroy');
