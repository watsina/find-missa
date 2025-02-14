<?php

namespace App\Imports;

use App\Models\Church;
use App\Models\Parish;
use App\Models\Diocese;
use App\Models\SubParish;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class DioceseChurchesImport implements SkipsEmptyRows, ToCollection, WithHeadingRow, WithValidation
{
    public function __construct(protected Diocese $diocese) {}

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $parishType = 'parish';
            $subParish = null;

            $parish = Parish::firstOrCreate(
                ['name' => trim($row['parish_name']), 'diocese_id' => $this->diocese->id],
                [
                    'district' => trim($row['district']),
                    'city' => trim($row['town']),
                    'contact_email' => trim($row['contact_email']),
                    'office_address' => trim($row['office_address']),
                    'website_url' => trim($row['website_url']),
                ],
            );

            if (isset($row['sub_parish_name'])) {
                $parishType = 'subParish';
                $subParish = SubParish::firstOrCreate(
                    ['name' => trim($row['sub_parish_name']), 'parish_id' => $parish->id],
                    [
                        'district' => trim($row['district']),
                        'city' => trim($row['town']),
                        'contact_email' => trim($row['contact_email']),
                        'office_address' => trim($row['office_address']),
                        'website_url' => trim($row['website_url']),
                    ],
                );
            }

            Church::firstOrCreate(
                [
                    'name' => trim($row['church_name']),
                    'parishable_id' => $parishType === 'parish' ? $parish->id : $subParish->id,
                    'parishable_type' => $parishType,
                ],
                [
                    'type' => Str::kebab($parishType),
                    'city' => trim($row['town']),
                    'district' => trim($row['district']),
                    'contact_email' => trim($row['contact_email']),
                    'contact_phone' => trim($row['church_contact_phone']),
                    'street_address' => trim($row['office_address']),
                    'website_url' => trim($row['website_url']),
                    'latitude' => $row['latitude'],
                    'longitude' => $row['longitude'],
                    'street_address' => trim($row['office_address']),
                    'postal_address' => trim($row['postal_address']),
                    'google_maps_link' => trim($row['google_maps_link']),
                    'district' => trim($row['district']),
                ],
            );
        }
    }

    public function rules(): array
    {
        $required = 'required|string|max:255';
        $optional = 'nullable|string|max:255';
        $email = 'nullable|email|max:255';
        $coordinate = 'nullable|numeric|between:-180.9999999,180.9999999';

        return [
            'parish_name' => $required,
            '*.parish_name' => $required,

            'sub_parish_name' => $optional,
            '*.sub_parish_name' => $optional,

            'church_name' => $required,
            '*.church_name' => $required,

            'town' => $required,
            '*.town' => $required,

            'district' => $required,
            '*.district' => $required,

            'website_url' => $optional,
            '*.website_url' => $optional,

            'contact_email' => $email,
            '*.contact_email' => $email,

            'latitude' => $coordinate,
            '*.latitude' => $coordinate,

            'longitude' => $coordinate,
            '*.longitude' => $coordinate,

            'office_address' => $optional,
            '*.office_address' => $optional,

            'google_maps_link' => $optional,
            '*.google_maps_link' => $optional,

            'church_contact_phone' => $optional,
            '*.church_contact_phone' => $optional,

            'office_address' => $optional,
            '*.city' => $optional,

            'postal_address' => $optional,
            '*.postal_address' => $optional,
        ];
    }
}
