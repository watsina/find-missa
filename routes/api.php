<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImportDataController;
use App\Http\Controllers\SwitchTeamController;
use App\Http\Controllers\TeamMemberController;

require __DIR__.'/auth.php';

Route::middleware(['auth:sanctum'])->get('/auth/user', function (Request $request) {
    return $request->user();
})->name('auth.user');

//admin routes
Route::middleware('auth', 'admin')->group(function () {
    Route::post('import-data', ImportDataController::class)->middleware('userWithTeam')->name('import-data');
    Route::apiResource('team.invitations', App\Http\Controllers\TeamInvitationController::class)->only('index', 'store', 'destroy');
    Route::apiResource('users', App\Http\Controllers\UserController::class);
    Route::apiResource('teams', App\Http\Controllers\TeamController::class);
    // acl
    Route::get('roles-permissions', App\Http\Controllers\Acl\GetRolesAndPermissionsController::class);
    Route::apiResource('roles', App\Http\Controllers\Acl\RolesController::class)->only('index', 'show', 'update');
    Route::apiResource('permissions', App\Http\Controllers\Acl\PermissionsController::class)->only('index', 'show', 'store', 'update');
    Route::apiResource('user.roles', App\Http\Controllers\Acl\UserRolesController::class)->only('index', 'store')->middleware('userWithTeam');
});

Route::middleware('auth', 'userWithTeam')->group(function () {

    Route::get('dashboard', DashboardController::class)->name('dashboard.index');
    Route::put('switch-team', SwitchTeamController::class);
    Route::apiResource('team.members', TeamMemberController::class)->only('index', 'show', 'update', 'destroy');
    Route::apiResource('church', App\Http\Controllers\ChurchController::class);
    Route::apiResource('diocese', App\Http\Controllers\DioceseController::class);
    Route::apiResource('parish', App\Http\Controllers\ParishController::class);
    Route::apiResource('priest', App\Http\Controllers\PriestController::class);
    Route::apiResource('sub-parish', App\Http\Controllers\SubParishController::class);
    Route::apiResource('activity-time', App\Http\Controllers\ActivityTimeController::class);
    Route::apiResource('activity-time-exceptions', App\Http\Controllers\ActivityTimeExceptionController::class);
});
