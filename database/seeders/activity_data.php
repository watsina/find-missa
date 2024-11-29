<?php

return [
    [
        'diocese_details' => ['name' => 'Kampala Archdiocese', 'type' => 'archdiocese'],
        'parishes' => [
            [
                'details' => ['name' => 'St Augustine Chaplaincy', 'city' => 'Makerere', 'district' => 'Kampala'],
                'church_details' => [
                    [
                        'details' => ['name' => 'St Augustine', 'type' => 'chapel', 'city' => 'Makerere', 'district' => 'Kampala'],
                    ],
                ],
            ],
            [
                'details' => ['name' => 'Ntinda', 'city' => 'Ntinda', 'district' => 'Kampala'],
                'church_details' => [
                    [
                        'details' => ['name' => 'Ntinda', 'type' => 'parish', 'city' => 'Ntinda', 'district' => 'Kampala'],
                    ],
                ],
            ],
            [
                'details' => ['name' => 'Kigoowa', 'city' => 'Kisaasi', 'district' => 'Kampala'],
                'church_details' => [
                    [
                        'details' => ['name' => 'Kigoowa', 'type' => 'parish', 'city' => 'Kisaasi', 'district' => 'Kampala'],
                        'activity_details' => [
                            // sunday activities
                            ['type' => 'mass', 'occurrence' => 'weekly', 'start_time' => '7:00', 'end_time' => '8:15', 'day_of_week' => 'Sunday', 'language' => 'Luganda'],
                            ['type' => 'mass', 'occurrence' => 'weekly', 'start_time' => '8:15', 'end_time' => '9:15', 'day_of_week' => 'Sunday', 'language' => 'English'],
                            ['type' => 'mass', 'occurrence' => 'weekly', 'start_time' => '9:15', 'end_time' => '10:30', 'day_of_week' => 'Sunday', 'language' => 'English'],
                            ['type' => 'mass', 'occurrence' => 'weekly', 'start_time' => '10:30', 'end_time' => '11:45', 'day_of_week' => 'Sunday', 'language' => 'Luganda'],
                            ['type' => 'mass', 'occurrence' => 'weekly', 'start_time' => '11:45', 'end_time' => '13:00', 'day_of_week' => 'Sunday', 'language' => 'Luganda'],
                            ['type' => 'mass', 'occurrence' => 'weekly', 'start_time' => '17:00', 'end_time' => '18:15', 'day_of_week' => 'Sunday', 'language' => 'English/Luganda'],
                            // weekday activities
                            ['type' => 'mass', 'occurrence' => 'weekly', 'start_time' => '7:00', 'end_time' => '7:30', 'day_of_week' => 'Monday', 'language' => 'English/Luganda'],
                            ['type' => 'mass', 'occurrence' => 'weekly', 'start_time' => '18:00', 'end_time' => '18:30', 'day_of_week' => 'Monday', 'language' => 'English/Luganda'],
                            ['type' => 'mass', 'occurrence' => 'weekly', 'start_time' => '7:00', 'end_time' => '7:30', 'day_of_week' => 'Tuesday', 'language' => 'English'],
                            ['type' => 'mass', 'occurrence' => 'weekly', 'start_time' => '18:00', 'end_time' => '18:30', 'day_of_week' => 'Tuesday', 'language' => 'English'],
                            ['type' => 'mass', 'occurrence' => 'weekly', 'start_time' => '7:00', 'end_time' => '7:30', 'day_of_week' => 'Wednesday', 'language' => 'Luganda'],
                            ['type' => 'mass', 'occurrence' => 'weekly', 'start_time' => '18:00', 'end_time' => '18:30', 'day_of_week' => 'Wednesday', 'language' => 'Luganda'],
                            ['type' => 'mass', 'occurrence' => 'weekly', 'start_time' => '7:00', 'end_time' => '7:30', 'day_of_week' => 'Thursday', 'language' => 'English'],
                            ['type' => 'adoration', 'occurrence' => 'weekly', 'start_time' => '17:00', 'end_time' => '18:00', 'day_of_week' => 'Thursday', 'language' => 'English'],
                            ['type' => 'mass', 'occurrence' => 'weekly', 'start_time' => '19:00', 'end_time' => '19:45', 'day_of_week' => 'Thursday', 'language' => 'English'],
                            ['type' => 'mass', 'occurrence' => 'weekly', 'start_time' => '7:00', 'end_time' => '7:30', 'day_of_week' => 'Friday', 'language' => 'Luganda'],
                            ['type' => 'mass', 'occurrence' => 'weekly', 'start_time' => '18:00', 'end_time' => '18:30', 'day_of_week' => 'Friday', 'language' => 'Luganda'],
                            ['type' => 'mass', 'occurrence' => 'weekly', 'start_time' => '7:00', 'end_time' => '7:30', 'day_of_week' => 'Saturday', 'language' => 'Luganda'],
                            ['type' => 'confession', 'occurrence' => 'weekly', 'start_time' => '7:30', 'end_time' => '8:30', 'day_of_week' => 'Saturday', 'language' => 'English/Luganda'],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
