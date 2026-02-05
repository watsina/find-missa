<?php

return [
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
            ['name' => 'activiy-time.write', 'description' => 'Create and update activiy time(s)'],
            ['name' => 'activiy-time.read', 'description' => 'View activiy time(s)'],
        ],
        'priest' => [
            ['name' => 'priest.write', 'description' => 'Create and update and update priest(s)'],
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
    ],
    'roles' => [
        'diocese' => [
            ['name' => 'diocese-editor', 'description' => 'Views and reads diocese details.'],
            ['name' => 'diocese-viewer', 'description' => 'Views diocese details.'],
        ],
        'parish' => [
            ['name' => 'parish-editor', 'description' => 'Views and edits parish details.'],
            ['name' => 'parish-viewer', 'description' => 'Views parish details.'],
            ['name' => 'parish-priest-editor', 'description' => 'Views and reads details of priests in a parish.'],
        ],
        'subParish' => [
            ['name' => 'subParish-editor', 'description' => 'Views and readd sub parish details.'],
            ['name' => 'subParish-viewer', 'description' => 'Views parish details.'],
        ],
        'church' => [
            // should also have the permission to view parishes and subparishes
            ['name' => 'church-editor', 'description' => 'Views and reads church details.'],
            ['name' => 'church-viewer', 'description' => 'Views church details.'],
            ['name' => 'church-activity-editor', 'description' => 'Views and reads church activities.'],
        ],
    ],
];
