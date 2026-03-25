<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Permission Categories
    |--------------------------------------------------------------------------
    |
    | These are the human-readable labels for permission groups, used in the
    | UI when displaying permissions in a grouped/categorized manner.
    |
    */
    'permission_categories' => [
        'diocese' => 'Diocese',
        'parish' => 'Parish',
        'subParish' => 'Sub Parish',
        'church' => 'Church',
        'activityTime' => 'Activity Time',
        'priest' => 'Priest',
        'holiday' => 'Holiday',
        'schedulePeriod' => 'Schedule Period',
        'globalException' => 'Global Exceptions',
        'team' => 'Team',
        'teamMember' => 'Team Member',
        'role' => 'Roles & Permissions',
        'data' => 'Data Import/Export',
        'actionLog' => 'Action Logs',
    ],

    /*
    |--------------------------------------------------------------------------
    | Permissions
    |--------------------------------------------------------------------------
    |
    | Define all available permissions grouped by category. Each permission
    | has a name (used in code) and a description (shown in UI).
    |
    */
    'permissions' => [
        'diocese' => [
            ['name' => 'dioceses.write', 'description' => 'Create and update diocese(s)'],
            ['name' => 'dioceses.read', 'description' => 'View diocese(s)'],
        ],
        'parish' => [
            ['name' => 'parishes.write', 'description' => 'Create and update parish(es)'],
            ['name' => 'parishes.read', 'description' => 'View parish(es)'],
        ],
        'subParish' => [
            ['name' => 'sub-parishes.write', 'description' => 'Create and update sub parish(es)'],
            ['name' => 'sub-parishes.read', 'description' => 'View sub parish(es)'],
        ],
        'church' => [
            ['name' => 'churches.write', 'description' => 'Create and update church(es)'],
            ['name' => 'churches.read', 'description' => 'View church(es)'],
        ],
        'activityTime' => [
            ['name' => 'activity-times.write', 'description' => 'Create and update activity time(s)'],
            ['name' => 'activity-times.read', 'description' => 'View activity time(s)'],
        ],
        'priest' => [
            ['name' => 'priests.write', 'description' => 'Create and update priest(s)'],
            ['name' => 'priests.read', 'description' => 'View priest(s)'],
        ],
        'holiday' => [
            ['name' => 'holidays.write', 'description' => 'Create and update holiday(s)'],
            ['name' => 'holidays.read', 'description' => 'View holiday(s)'],
        ],
        'schedulePeriod' => [
            ['name' => 'schedule-periods.write', 'description' => 'Create and update schedule period(s)'],
            ['name' => 'schedule-periods.read', 'description' => 'View schedule period(s)'],
        ],
        'globalException' => [
            ['name' => 'global-exceptions.read', 'description' => 'View global activity exceptions'],
            ['name' => 'global-exceptions.write', 'description' => 'Create and update global exceptions'],
        ],
        'team' => [
            ['name' => 'teams.read', 'description' => 'View team(s)'],
            ['name' => 'teams.write', 'description' => 'Create and update team(s)'],
        ],
        'teamMember' => [
            ['name' => 'team-members.read', 'description' => 'View team member(s)'],
            ['name' => 'team-members.write', 'description' => 'Invite and update team member(s)'],
        ],
        'role' => [
            ['name' => 'roles.read', 'description' => 'View roles and permissions'],
            ['name' => 'roles.write', 'description' => 'Create and update roles'],
        ],
        'data' => [
            ['name' => 'data.import', 'description' => 'Import data from files'],
            ['name' => 'data.export', 'description' => 'Export data to files'],
        ],
        'actionLog' => [
            ['name' => 'action-logs.read', 'description' => 'View user action logs'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Roles
    |--------------------------------------------------------------------------
    |
    | Predefined roles that can be seeded. Note that roles are team-scoped,
    | so these are just templates that teams can use as a starting point.
    |
    */
    'roles' => [
        'diocese' => [
            ['name' => 'diocese-editor', 'description' => 'Views and edits diocese details.'],
            ['name' => 'diocese-viewer', 'description' => 'Views diocese details.'],
        ],
        'parish' => [
            ['name' => 'parish-editor', 'description' => 'Views and edits parish details.'],
            ['name' => 'parish-viewer', 'description' => 'Views parish details.'],
            ['name' => 'parish-priest-editor', 'description' => 'Views and edits details of priests in a parish.'],
        ],
        'subParish' => [
            ['name' => 'subParish-editor', 'description' => 'Views and edits sub parish details.'],
            ['name' => 'subParish-viewer', 'description' => 'Views sub parish details.'],
        ],
        'church' => [
            ['name' => 'church-editor', 'description' => 'Views and edits church details.'],
            ['name' => 'church-viewer', 'description' => 'Views church details.'],
            ['name' => 'church-activity-editor', 'description' => 'Views and edits church activities.'],
        ],
    ],
];
