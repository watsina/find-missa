<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImportDataController;

require __DIR__.'/auth.php';

Route::middleware(['auth:sanctum'])->get('/auth/user', function (Request $request) {
    return $request->user();
})->name('auth.user');

Route::middleware('auth')->group(function () {
    Route::post('import-data', ImportDataController::class)->name('import-data')->middleware('admin');

    Route::get('dashboard', DashboardController::class)->name('dashboard.index');
    Route::apiResource('users', App\Http\Controllers\UserController::class)->middleware('admin');
    Route::apiResource('user-invitations', App\Http\Controllers\UserInvitationController::class)->only('store', 'destroy')->middleware('admin');
    Route::apiResource('church', App\Http\Controllers\ChurchController::class);
    Route::apiResource('diocese', App\Http\Controllers\DioceseController::class);
    Route::apiResource('parish', App\Http\Controllers\ParishController::class);
    Route::apiResource('priest', App\Http\Controllers\PriestController::class);
    Route::apiResource('sub-parish', App\Http\Controllers\SubParishController::class);
    Route::apiResource('activity-time', App\Http\Controllers\ActivityTimeController::class);
    Route::apiResource('activity-time-exception', App\Http\Controllers\ActivityTimeExceptionController::class);
});
