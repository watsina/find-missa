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
            ['name' => 'diocese.write', 'description' => 'Create and update diocese(s)'],
            ['name' => 'diocese.read', 'description' => 'View diocese(s)'],
        ],
        'parish' => [
            ['name' => 'parish.write', 'description' => 'Create and update parish(es)'],
            ['name' => 'parish.read', 'description' => 'View parish(es)'],
        ],
        'subParish' => [
            ['name' => 'subParish.write', 'description' => 'Create and update subParish(es)'],
            ['name' => 'subParish.read', 'description' => 'View subParish(es)'],
        ],
        'church' => [
            ['name' => 'church.write', 'description' => 'Create and update church(es)'],
            ['name' => 'church.read', 'description' => 'View church(es)'],
        ],
        'activityTime' => [
            ['name' => 'activity-time.write', 'description' => 'Create and update activity time(s)'],
            ['name' => 'activity-time.read', 'description' => 'View activity time(s)'],
        ],
        'priest' => [
            ['name' => 'priest.write', 'description' => 'Create and update priest(s)'],
            ['name' => 'priest.read', 'description' => 'View priest(s)'],
        ],
        'holiday' => [
            ['name' => 'holiday.write', 'description' => 'Create and update holiday(s)'],
            ['name' => 'holiday.read', 'description' => 'View holiday(s)'],
        ],
        'schedulePeriod' => [
            ['name' => 'schedule-period.write', 'description' => 'Create and update schedule period(s)'],
            ['name' => 'schedule-period.read', 'description' => 'View schedule period(s)'],
        ],
        'globalException' => [
            ['name' => 'global-exception.read', 'description' => 'View global activity exceptions'],
            ['name' => 'global-exception.write', 'description' => 'Create and update global exceptions'],
        ],
        'teamMember' => [
            ['name' => 'team-member.read', 'description' => 'View team member(s)'],
            ['name' => 'team-member.write', 'description' => 'Invite and update team member(s)'],
        ],
        'role' => [
            ['name' => 'role.read', 'description' => 'View roles and permissions'],
            ['name' => 'role.write', 'description' => 'Create and update roles'],
        ],
        'data' => [
            ['name' => 'data.import', 'description' => 'Import data from files'],
            ['name' => 'data.export', 'description' => 'Export data to files'],
        ],
        'actionLog' => [
            ['name' => 'action-log.read', 'description' => 'View user action logs'],
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
