<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImportDataController;

require __DIR__.'/auth.php';

Route::middleware(['auth:sanctum'])->get('/auth/user', function (Request $request) {
    return $request->user();
})->name('auth.user');

//admin routes
Route::middleware('auth', 'admin')->group(function () {
    Route::post('import-data', ImportDataController::class)->name('import-data');
    Route::apiResource('team.invitations', App\Http\Controllers\TeamInvitationController::class)->only('index', 'store', 'destroy');
    Route::apiResource('users', App\Http\Controllers\UserController::class);
    Route::apiResource('teams', App\Http\Controllers\TeamController::class);
    // acl
    Route::apiResource('roles', App\Http\Controllers\Acl\RolesController::class)->only('index', 'show', 'update');
});

Route::middleware('auth')->group(function () {

    Route::get('dashboard', DashboardController::class)->name('dashboard.index');
    Route::apiResource('church', App\Http\Controllers\ChurchController::class);
    Route::apiResource('diocese', App\Http\Controllers\DioceseController::class);
    Route::apiResource('parish', App\Http\Controllers\ParishController::class);
    Route::apiResource('priest', App\Http\Controllers\PriestController::class);
    Route::apiResource('sub-parish', App\Http\Controllers\SubParishController::class);
    Route::apiResource('activity-time', App\Http\Controllers\ActivityTimeController::class);
    Route::apiResource('activity-time-exception', App\Http\Controllers\ActivityTimeExceptionController::class);
});
