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
                'details' => ['name' => 'St Charles Lwanga Parish', 'city' => 'Ntinda', 'district' => 'Kampala'],
                'church_details' => [
                    [
                        'details' => [
                            'name' => 'St Charles Lwanga Parish',
                            'type' => 'parish',
                            'city' => 'Ntinda',
                            'district' => 'Kampala',
                            'website_url' => 'https://stcharleslwangantinda.org/',
                            'google_maps_link' => 'https://maps.app.goo.gl/3mp9pdKq44Joi2689',
                            'contact_phone' => '+256 787 484976',
                        ],
                        'activity_details' => [
                            // sunday activities
                            ['type' => 'mass', 'occurrence' => 'weekly', 'start_time' => '7:00', 'end_time' => '8:30', 'day_of_week' => 'Sunday', 'language' => 'Luganda'],
                            ['type' => 'mass', 'occurrence' => 'weekly', 'start_time' => '8:30', 'end_time' => '10:00', 'day_of_week' => 'Sunday', 'language' => 'English'],
                            ['type' => 'mass', 'occurrence' => 'weekly', 'start_time' => '10:00', 'end_time' => '11:30', 'day_of_week' => 'Sunday', 'language' => 'English'],
                            ['type' => 'mass', 'occurrence' => 'weekly', 'start_time' => '11:30', 'end_time' => '13:00', 'day_of_week' => 'Sunday', 'language' => 'Luganda'],
                            ['type' => 'mass', 'occurrence' => 'weekly', 'start_time' => '13:00', 'end_time' => '14:30', 'day_of_week' => 'Sunday', 'language' => 'English'],
                            ['type' => 'mass', 'occurrence' => 'weekly', 'start_time' => '17:00', 'end_time' => '18:30', 'day_of_week' => 'Sunday', 'language' => 'English'],
                            // weekday activities
                            ['type' => 'mass', 'occurrence' => 'weekly', 'start_time' => '7:00', 'end_time' => '7:30', 'day_of_week' => 'Monday', 'language' => 'English'],
                            ['type' => 'mass', 'occurrence' => 'weekly', 'start_time' => '13:00', 'end_time' => '13:30', 'day_of_week' => 'Monday', 'language' => 'English'],
                            ['type' => 'mass', 'occurrence' => 'weekly', 'start_time' => '7:00', 'end_time' => '7:30', 'day_of_week' => 'Tuesday', 'language' => 'English'],
                            ['type' => 'mass', 'occurrence' => 'weekly', 'start_time' => '13:00', 'end_time' => '13:30', 'day_of_week' => 'Tuesday', 'language' => 'English'],
                            ['type' => 'mass', 'occurrence' => 'weekly', 'start_time' => '7:00', 'end_time' => '7:30', 'day_of_week' => 'Wednesday', 'language' => 'English'],
                            ['type' => 'mass', 'occurrence' => 'weekly', 'start_time' => '13:00', 'end_time' => '13:30', 'day_of_week' => 'Wednesday', 'language' => 'English'],
                            ['type' => 'mass', 'occurrence' => 'weekly', 'start_time' => '7:00', 'end_time' => '7:30', 'day_of_week' => 'Thursday', 'language' => 'English'],
                            ['type' => 'adoration', 'occurrence' => 'weekly', 'start_time' => '7:00', 'end_time' => '9:00', 'day_of_week' => 'Thursday', 'language' => 'English'],
                            ['type' => 'adoration', 'occurrence' => 'weekly', 'start_time' => '17:30', 'end_time' => '19:00', 'day_of_week' => 'Thursday', 'language' => 'English'],
                            ['type' => 'mass', 'occurrence' => 'weekly', 'start_time' => '19:00', 'end_time' => '19:45', 'day_of_week' => 'Thursday', 'language' => 'English'],
                            ['type' => 'mass', 'occurrence' => 'weekly', 'start_time' => '7:00', 'end_time' => '7:30', 'day_of_week' => 'Friday', 'language' => 'English'],
                            ['type' => 'mass', 'occurrence' => 'weekly', 'start_time' => '13:00', 'end_time' => '13:30', 'day_of_week' => 'Friday', 'language' => 'English'],
                            ['type' => 'mass', 'occurrence' => 'weekly', 'start_time' => '7:00', 'end_time' => '7:30', 'day_of_week' => 'Saturday', 'language' => 'English'],
                            ['type' => 'anticipation-mass', 'occurrence' => 'weekly', 'start_time' => '17:30', 'end_time' => '19:00', 'day_of_week' => 'Saturday', 'language' => 'English'],
                            ['type' => 'confession', 'occurrence' => 'weekly', 'start_time' => '7:30', 'end_time' => '8:30', 'day_of_week' => 'Saturday', 'language' => 'English,Luganda'],
                        ],
                    ],
                ],
            ],
            [
                'details' => ['name' => 'Kigoowa Catholic Parish', 'city' => 'Kisaasi', 'district' => 'Kampala'],
                'church_details' => [
                    [
                        'details' => [
                            'name' => 'St. Aderea Kaggwa Catholic',
                            'type' => 'parish',
                            'city' => 'Kisaasi',
                            'district' => 'Kampala',
                            'website_url' => '',
                            'google_maps_link' => 'https://maps.app.goo.gl/Fg7rLHqVGfQVTQhw8',
                            'contact_phone' => '+256 753 576874',
                        ],
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
                            ['type' => 'confession', 'occurrence' => 'weekly', 'start_time' => '7:30', 'end_time' => '8:30', 'day_of_week' => 'Saturday', 'language' => 'English,Luganda'],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
