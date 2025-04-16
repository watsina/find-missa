<?php

use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use App\Models\Login;
use App\Models\Church;
use App\Models\Parish;
use App\Models\Priest;
use App\Models\Diocese;
use App\Models\SubParish;
use App\Models\Permission;
use App\Models\TeamMember;
use App\Models\ActivityTime;
use App\Models\TeamInvitation;
use App\Models\ActivityTimeException;
use App\Models\GlobalActivityTimeException;

return [
    'user' => User::class,
    'login' => Login::class,
    'team' => Team::class,
    'team-invitation' => TeamInvitation::class,
    'team-member' => TeamMember::class,
    'role' => Role::class,
    'permission' => Permission::class,
    'diocese' => Diocese::class,
    'parish' => Parish::class,
    'subParish' => SubParish::class,
    'church' => Church::class,
    'activity-time' => ActivityTime::class,
    'activity-time-exception' => ActivityTimeException::class,
    'priest' => Priest::class,
    'global-activity-time-exception' => GlobalActivityTimeException::class,
];
