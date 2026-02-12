<?php

use App\Http\Controllers;
use Illuminate\Http\Request;
use App\Features\DataImportExport;
use App\Http\Controllers\AppUsers;
use Illuminate\Support\Facades\Route;
use Laravel\Pennant\Middleware\EnsureFeaturesAreActive;

require __DIR__.'/auth.php';

// V1 search API disabled - use v2 instead
// Route::get('/search', AppUsers\SearchActivitiesController::class)->name('search');
Route::get('/search-v2', AppUsers\SearchActivitiesV2Controller::class)->name('search.v2');
Route::get('/church-details/{church}', AppUsers\GetChurchDetailsController::class)->name('church-details');
Route::get('/holiday-info', AppUsers\HolidayInfoController::class)->name('holiday-info');

Route::middleware(['auth:sanctum'])->get('/auth/user', function (Request $request) {
    return $request->user();
})->name('auth.user');

// ACL - Permission categories (must be defined before apiResource to avoid route conflict)
Route::middleware(['auth', 'userWithTeam'])
    ->get('permissions/categories', [Controllers\Acl\PermissionsController::class, 'categories'])
    ->name('permissions.categories');

// admin routes
Route::middleware('auth', 'admin')->group(function () {
    Route::delete('clear-data', Controllers\ClearDataController::class)->name('clear-data');
    // Route::post('import-data', Controllers\ImportDataController::class)->middleware('userWithTeam')->name('import-data');
    Route::post('import-churches/{diocese}', Controllers\ImportDioceseChurchesController::class)->middleware('userWithTeam')->name('import-churches');
    Route::post('import-activities/{diocese}', Controllers\ImportActivitesController::class)->middleware('userWithTeam')->name('import-activities');
    Route::post('export-data', Controllers\ExportDataController::class)->middleware('userWithTeam')->name('export-data');

    // Full data export/import (JSON format) - Protected by feature flag
    Route::get('full-export', Controllers\FullDataExportController::class)
        ->middleware(['userWithTeam', EnsureFeaturesAreActive::class.':'.DataImportExport::class])
        ->name('full-export');
    Route::post('full-import', Controllers\FullDataImportController::class)
        ->middleware(['userWithTeam', EnsureFeaturesAreActive::class.':'.DataImportExport::class])
        ->name('full-import');

    Route::apiResource('team.invitations', Controllers\TeamInvitationController::class)->only('index', 'store', 'destroy');
    Route::apiResource('users', Controllers\UserController::class)->except('destroy');
    Route::apiResource('teams', Controllers\TeamController::class)->except('destroy');

    Route::get('action-logs', Controllers\UserActionLogController::class)->middleware('userWithTeam');
    // acl - admin only
    Route::get('roles-permissions', Controllers\Acl\GetRolesAndPermissionsController::class);
    Route::apiResource('permissions', Controllers\Acl\PermissionsController::class)->only('index', 'show', 'store', 'update');
});

Route::middleware('auth', 'userWithTeam')->group(function () {

    Route::get('dashboard', Controllers\DashboardController::class)->name('dashboard.index');
    Route::put('switch-team', Controllers\SwitchTeamController::class);
    Route::apiResource('team.members', Controllers\TeamMemberController::class)->only('index', 'show', 'update', 'destroy');

    // Sync Code Name Routes
    Route::post('churches/{church}/sync-codename', [Controllers\ChurchController::class, 'syncCodeName'])->name('churches.sync-codename');
    Route::post('parishes/{parish}/sync-codename', [Controllers\ParishController::class, 'syncCodeName'])->name('parishes.sync-codename');
    Route::post('sub-parishes/{subParish}/sync-codename', [Controllers\SubParishController::class, 'syncCodeName'])->name('sub-parishes.sync-codename');
    Route::post('dioceses/{diocese}/sync-codename', [Controllers\DioceseController::class, 'syncCodeName'])->name('dioceses.sync-codename');

    Route::apiResource('churches', Controllers\ChurchController::class)->except('destroy');
    Route::apiResource('dioceses', Controllers\DioceseController::class)->except('destroy');
    Route::apiResource('parishes', Controllers\ParishController::class)->except('destroy');
    Route::apiResource('priests', Controllers\PriestController::class)->except('destroy');
    Route::apiResource('sub-parishes', Controllers\SubParishController::class)->except('destroy');
    Route::apiResource('activity-times', Controllers\ActivityTimeController::class);
    Route::apiResource('activity-time-exceptions', Controllers\ActivityTimeExceptionController::class);
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

    // Activity Schedules v2
    Route::apiResource('activity-schedules', Controllers\ActivityScheduleController::class);
    Route::apiResource('one-time-activities', Controllers\OneTimeActivityController::class);
    Route::apiResource('schedule-rules', Controllers\ScheduleRuleController::class);
    Route::apiResource('schedule-overrides', Controllers\ScheduleOverrideController::class);
    Route::apiResource('liturgical-schedules', Controllers\LiturgicalScheduleController::class);

    // Adoration Chapel (nested under churches - one per church)
    Route::get('churches/{church}/adoration-chapel', [Controllers\AdorationChapelController::class, 'show'])->name('churches.adoration-chapel.show');
    Route::post('churches/{church}/adoration-chapel', [Controllers\AdorationChapelController::class, 'store'])->name('churches.adoration-chapel.store');
    Route::put('churches/{church}/adoration-chapel', [Controllers\AdorationChapelController::class, 'update'])->name('churches.adoration-chapel.update');
    Route::delete('churches/{church}/adoration-chapel', [Controllers\AdorationChapelController::class, 'destroy'])->name('churches.adoration-chapel.destroy');

    // ACL - Team-scoped roles (authorization handled in form requests)
    Route::apiResource('roles', Controllers\Acl\RolesController::class)->only('index', 'show', 'store', 'update', 'destroy');
    Route::apiResource('user.roles', Controllers\Acl\UserRolesController::class)->only('index', 'store');
});
