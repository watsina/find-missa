<?php

use App\Models;

return [
    'user' => Models\User::class,
    'login' => Models\Login::class,
    'team' => Models\Team::class,
    'team-invitation' => Models\TeamInvitation::class,
    'team-member' => Models\TeamMember::class,
    'role' => Models\Role::class,
    'permission' => Models\Permission::class,
    'diocese' => Models\Diocese::class,
    'parish' => Models\Parish::class,
    'subParish' => Models\SubParish::class,
    'church' => Models\Church::class,
    'activity-time' => Models\ActivityTime::class,
    'activity-time-exception' => Models\ActivityTimeException::class,
    'activity-tag' => Models\ActivityTag::class,
    'priest' => Models\Priest::class,
    'global-activity-time-exception' => Models\GlobalActivityTimeException::class,
    'holiday' => Models\Holiday::class,
    'holiday-instance' => Models\HolidayInstance::class,
    'schedule-period' => Models\SchedulePeriod::class,
];
