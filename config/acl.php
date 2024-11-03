<?php

return [
    'permissions' => [
        'diocese' => [
            ['name' => 'diocese.create', 'title' => 'Create diocese(s)'],
            ['name' => 'diocese.update', 'title' => 'Update diocese(s)'],
            ['name' => 'diocese.index', 'title' => 'Get diocese(s)'],
            ['name' => 'diocese.delete', 'title' => 'Delete diocese(s)'],
        ],
        'parish' => [
            ['name' => 'parish.create', 'title' => 'Create parish(es)'],
            ['name' => 'parish.update', 'title' => 'Update parish(es)'],
            ['name' => 'parish.index', 'title' => 'Get parish(es)'],
            ['name' => 'parish.delete', 'title' => 'Delete parish(es)'],
        ],
        'subParish' => [
            ['name' => 'subParish.create', 'title' => 'Create subParish(es)'],
            ['name' => 'subParish.update', 'title' => 'Update subParish(es)'],
            ['name' => 'subParish.index', 'title' => 'Get subParish(es)'],
            ['name' => 'subParish.delete', 'title' => 'Delete subParish(es)'],
        ],
        'church' => [
            ['name' => 'church.create', 'title' => 'Create church(es)'],
            ['name' => 'church.update', 'title' => 'Update church(es)'],
            ['name' => 'church.index', 'title' => 'Get church(es)'],
            ['name' => 'church.delete', 'title' => 'Delete church(es)'],
        ],
        'activityTime' => [
            ['name' => 'activiy-time.create', 'title' => 'Create activiy time(s)'],
            ['name' => 'activiy-time.update', 'title' => 'Update activiy time(s)'],
            ['name' => 'activiy-time.index', 'title' => 'Get activiy time(s)'],
            ['name' => 'activiy-time.delete', 'title' => 'Delete activiy time(s)'],
        ],
        'priest' => [
            ['name' => 'priest.create', 'title' => 'Create priest(s)'],
            ['name' => 'priest.update', 'title' => 'Update priest(s)'],
            ['name' => 'priest.index', 'title' => 'Get priest(s)'],
            ['name' => 'priest.delete', 'title' => 'Delete priest(s)'],
        ],
    ],
    'roles' => [
        'diocese' => [
            ['name' => 'diocese-editor', 'title' => 'Views and updates diocese details.'],
            ['name' => 'diocese-viewer', 'title' => 'Views diocese details.'],
        ],
        'parish' => [
            ['name' => 'parish-editor', 'title' => 'Views and edits parish details.'],
            ['name' => 'parish-viewer', 'title' => 'Views parish details.'],
            ['name' => 'parish-priest-editor', 'title' => 'Views and updates details of priests in a parish.'],
        ],
        'subParish' => [
            ['name' => 'subParish-editor', 'title' => 'Views and updated sub parish details.'],
            ['name' => 'subParish-viewer', 'title' => 'Views parish details.'],
        ],
        'church' => [
            // should also have the permission to view parishes and subparishes
            ['name' => 'church-editor', 'title' => 'Views and updates church details.'],
            ['name' => 'church-viewer', 'title' => 'Views church details.'],
            ['name' => 'church-activity-editor', 'title' => 'Views and updates church activities.'],
        ],
    ],
];
