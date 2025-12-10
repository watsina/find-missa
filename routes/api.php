<?php

use App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Controllers\AppUsers;
use Illuminate\Support\Facades\Route;

require __DIR__.'/auth.php';

Route::get('/search', AppUsers\SearchActivitiesController::class)->name('search');
Route::get('/church-details/{church}', AppUsers\GetChurchDetailsController::class)->name('church-details');

Route::middleware(['auth:sanctum'])->get('/auth/user', function (Request $request) {
    return $request->user();
})->name('auth.user');

// admin routes
Route::middleware('auth', 'admin')->group(function () {
    Route::delete('clear-data', Controllers\ClearDataController::class)->name('clear-data');
    // Route::post('import-data', Controllers\ImportDataController::class)->middleware('userWithTeam')->name('import-data');
    Route::post('import-churches/{diocese}', Controllers\ImportDioceseChurchesController::class)->middleware('userWithTeam')->name('import-churches');
    Route::post('import-activities/{diocese}', Controllers\ImportActivitesController::class)->middleware('userWithTeam')->name('import-activities');
    Route::post('export-data', Controllers\ExportDataController::class)->middleware('userWithTeam')->name('export-data');

    Route::apiResource('team.invitations', Controllers\TeamInvitationController::class)->only('index', 'store', 'destroy');
    Route::apiResource('users', Controllers\UserController::class)->except('destroy');
    Route::apiResource('teams', Controllers\TeamController::class)->except('destroy');

    Route::get('action-logs', Controllers\UserActionLogController::class)->middleware('userWithTeam');
    // acl
    Route::get('roles-permissions', Controllers\Acl\GetRolesAndPermissionsController::class);
    Route::apiResource('roles', Controllers\Acl\RolesController::class)->only('index', 'show', 'update');
    Route::apiResource('permissions', Controllers\Acl\PermissionsController::class)->only('index', 'show', 'store', 'update');
    Route::apiResource('user.roles', Controllers\Acl\UserRolesController::class)->only('index', 'store')->middleware('userWithTeam');
});

Route::middleware('auth', 'userWithTeam')->group(function () {

    Route::get('dashboard', Controllers\DashboardController::class)->name('dashboard.index');
    Route::put('switch-team', Controllers\SwitchTeamController::class);
    Route::apiResource('team.members', Controllers\TeamMemberController::class)->only('index', 'show', 'update', 'destroy');
    Route::apiResource('churches', Controllers\ChurchController::class)->except('destroy');
    Route::apiResource('dioceses', Controllers\DioceseController::class)->except('destroy');
    Route::apiResource('parishes', Controllers\ParishController::class)->except('destroy');
    Route::apiResource('priests', Controllers\PriestController::class)->except('destroy');
    Route::apiResource('sub-parishes', Controllers\SubParishController::class)->except('destroy');
    Route::apiResource('activity-times', Controllers\ActivityTimeController::class);
    Route::apiResource('activity-time-exceptions', Controllers\ActivityTimeExceptionController::class);
    Route::apiResource('activity-tags', Controllers\ActivityTagController::class);
    Route::apiResource('global-activity-time-exceptions', Controllers\GlobalActivityTimeExceptionController::class);

    // Holidays - custom routes must come before apiResource to avoid route conflicts
    Route::get('holidays/instances/{year}', [Controllers\HolidayController::class, 'instances'])->name('holidays.instances');
    Route::get('holidays/in-range', [Controllers\HolidayController::class, 'inRange'])->name('holidays.in-range');
    Route::get('holidays/check-date', [Controllers\HolidayController::class, 'checkDate'])->name('holidays.check-date');
    Route::apiResource('holidays', Controllers\HolidayController::class);

    // Schedule Periods - custom routes must come before apiResource to avoid route conflicts
    Route::get('schedule-periods/current', [Controllers\SchedulePeriodController::class, 'current'])->name('schedule-periods.current');
    Route::get('schedule-periods/upcoming', [Controllers\SchedulePeriodController::class, 'upcoming'])->name('schedule-periods.upcoming');
    Route::get('schedule-periods/check-overlap', [Controllers\SchedulePeriodController::class, 'checkOverlap'])->name('schedule-periods.check-overlap');
    Route::apiResource('schedule-periods', Controllers\SchedulePeriodController::class);
});
