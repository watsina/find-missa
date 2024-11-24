<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImportDataController;
use App\Http\Controllers\SwitchTeamController;
use App\Http\Controllers\TeamMemberController;
use App\Http\Controllers\UserActionLogController;
use App\Http\Controllers\AppUsers\SearchActivitiesController;

require __DIR__.'/auth.php';

Route::get('/search', SearchActivitiesController::class)->name('search');

Route::middleware(['auth:sanctum'])->get('/auth/user', function (Request $request) {
    return $request->user();
})->name('auth.user');

//admin routes
Route::middleware('auth', 'admin')->group(function () {
    Route::post('import-data', ImportDataController::class)->middleware('userWithTeam')->name('import-data');
    Route::apiResource('team.invitations', App\Http\Controllers\TeamInvitationController::class)->only('index', 'store', 'destroy');
    Route::apiResource('users', App\Http\Controllers\UserController::class)->except('destroy');
    Route::apiResource('teams', App\Http\Controllers\TeamController::class)->except('destroy');

    Route::get('action-logs', UserActionLogController::class)->middleware('userWithTeam');
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
    Route::apiResource('churches', App\Http\Controllers\ChurchController::class)->except('destroy');
    Route::apiResource('dioceses', App\Http\Controllers\DioceseController::class)->except('destroy');
    Route::apiResource('parishes', App\Http\Controllers\ParishController::class)->except('destroy');
    Route::apiResource('priests', App\Http\Controllers\PriestController::class)->except('destroy');
    Route::apiResource('sub-parishes', App\Http\Controllers\SubParishController::class)->except('destroy');
    Route::apiResource('activity-times', App\Http\Controllers\ActivityTimeController::class)->except('destroy');
    Route::apiResource('activity-time-exceptions', App\Http\Controllers\ActivityTimeExceptionController::class)->except('destroy');
});
