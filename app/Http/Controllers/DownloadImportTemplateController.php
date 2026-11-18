<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadImportTemplateController extends Controller
{
    /**
     * Download a CSV template for data imports with rich examples.
     */
    public function __invoke(string $type): StreamedResponse
    {
        Gate::authorize('data.import');

        $templates = [
            'masses' => [
                'filename' => 'masses_template.csv',
                'headers' => [
                    'diocese_name',
                    'parish_name',
                    'parish_town',
                    'parish_district',
                    'sub_parish_name',
                    'sub_parish_town',
                    'sub_parish_district',
                    'church_name',
                    'city',
                    'day_of_week',
                    'language',
                    'start_time',
                    'end_time',
                    'activity_type',
                ],
                'samples' => [
                    // Sunday Mass (Single day)
                    [
                        'Kampala Archdiocese',
                        'Ntinda Parish',
                        'Ntinda',
                        'Kampala',
                        '',
                        '',
                        '',
                        'St Charles Church',
                        'Ntinda',
                        'Sunday',
                        'English',
                        '07:00',
                        '08:30',
                        'Mass',
                    ],
                    // Weekday Mass spanning Mon-Fri
                    [
                        'Kampala Archdiocese',
                        'Ntinda Parish',
                        'Ntinda',
                        'Kampala',
                        '',
                        '',
                        '',
                        'St Charles Church',
                        'Ntinda',
                        'Weekdays',
                        'English',
                        '07:00',
                        '07:30',
                        'Mass',
                    ],
                    // Daily Adoration
                    [
                        'Kampala Archdiocese',
                        'Ntinda Parish',
                        'Ntinda',
                        'Kampala',
                        '',
                        '',
                        '',
                        'St Charles Church',
                        'Ntinda',
                        'Daily',
                        'English',
                        '06:00',
                        '',
                        'Adoration',
                    ],
                    // Specific multiple days (e.g., Monday, Wednesday, Friday)
                    [
                        'Kampala Archdiocese',
                        'Ntinda Parish',
                        'Ntinda',
                        'Kampala',
                        'Bukoto SubParish',
                        'Bukoto',
                        'Kampala',
                        'St Jude Chapel',
                        'Bukoto',
                        'Monday, Wednesday, Friday',
                        'Luganda',
                        '17:00',
                        '18:00',
                        'Mass',
                    ],
                ],
            ],
            'parishes' => [
                'filename' => 'parishes_template.csv',
                'headers' => [
                    'diocese_name',
                    'diocese_type',
                    'parish_name',
                    'parish_town',
                    'church_name',
                    'church_type',
                    'latitude',
                    'longitude',
                    'city',
                ],
                'samples' => [
                    // Main parish church
                    [
                        'Kampala Archdiocese',
                        'archdiocese',
                        'Ntinda Parish',
                        'Ntinda',
                        'St Charles Lwanga Church',
                        'parish',
                        '0.3541',
                        '32.6105',
                        'Ntinda',
                    ],
                    // Sub-parish main church
                    [
                        'Kampala Archdiocese',
                        'archdiocese',
                        'Ntinda Parish',
                        'Ntinda',
                        'Bukoto Sub-Parish Church',
                        'sub-parish',
                        '0.3600',
                        '32.6000',
                        'Bukoto',
                    ],
                    // Parish outstation/chapel
                    [
                        'Kampala Archdiocese',
                        'archdiocese',
                        'Ntinda Parish',
                        'Ntinda',
                        'St Kizito Chapel',
                        'chapel',
                        '0.3500',
                        '32.6200',
                        'Naguru',
                    ],
                ],
            ],
            'churches' => [
                'filename' => 'diocese_churches_template.csv',
                'headers' => [
                    'parish_name',
                    'sub_parish_name',
                    'church_name',
                    'town',
                    'district',
                    'office_address',
                    'contact_email',
                    'website_url',
                    'google_maps_link',
                    'church_contact_phone',
                    'postal_address',
                    'latitude',
                    'longitude',
                ],
                'samples' => [
                    // Parish church without sub-parish
                    [
                        'Ntinda Parish',
                        '',
                        'St Charles Church',
                        'Ntinda',
                        'Kampala',
                        'Plot 123 Main Rd',
                        'office@example.com',
                        'https://example.com',
                        'https://maps.google.com',
                        '+256700000000',
                        'P.O. Box 1234',
                        '0.3541',
                        '32.6105',
                    ],
                    // Sub-parish church example
                    [
                        'Ntinda Parish',
                        'Bukoto SubParish',
                        'St Jude Chapel',
                        'Bukoto',
                        'Kampala',
                        'Plot 45 Bukoto St',
                        'bukoto@example.com',
                        'https://example.com/bukoto',
                        'https://maps.google.com/?q=bukoto',
                        '+256701111111',
                        'P.O. Box 5678',
                        '0.3600',
                        '32.6000',
                    ],
                ],
            ],
            'activities' => [
                'filename' => 'diocese_activities_template.csv',
                'headers' => [
                    'parish_name',
                    'sub_parish_name',
                    'church_name',
                    'day_of_week',
                    'language',
                    'start_time',
                    'end_time',
                    'activity_type',
                ],
                'samples' => [
                    // Sunday Mass
                    [
                        'Ntinda Parish',
                        '',
                        'St Charles Church',
                        'Sunday',
                        'English',
                        '07:00',
                        '08:30',
                        'Mass',
                    ],
                    // Weekday Mass spanning Mon-Fri
                    [
                        'Ntinda Parish',
                        '',
                        'St Charles Church',
                        'Weekdays',
                        'English',
                        '07:00',
                        '07:30',
                        'Mass',
                    ],
                    // Confession on Saturday
                    [
                        'Ntinda Parish',
                        'Bukoto SubParish',
                        'St Jude Chapel',
                        'Saturday',
                        'English',
                        '16:30',
                        '18:00',
                        'Confession',
                    ],
                ],
            ],
        ];

        abort_unless(isset($templates[$type]), 404, 'Template type not found.');

        $template = $templates[$type];

        return response()->streamDownload(function () use ($template) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $template['headers']);
            foreach ($template['samples'] as $sample) {
                fputcsv($handle, $sample);
            }
            fclose($handle);
        }, $template['filename'], [
            'Content-Type' => 'text/csv',
        ]);
    }
}
