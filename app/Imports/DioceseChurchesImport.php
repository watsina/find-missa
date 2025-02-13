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
                ['name' => $row['parish_name'], 'diocese_id' => $this->diocese->id],
                [
                    'district' => $row['district'],
                    'city' => $row['town'],
                    'contact_email' => $row['contact_email'],
                    'office_address' => $row['office_address'],
                    'website_url' => $row['website_url'],
                ],
            );

            if (isset($row['sub_parish_name'])) {
                $parishType = 'subParish';
                $subParish = SubParish::firstOrCreate(
                    ['name' => $row['sub_parish_name'], 'parish_id' => $parish->id],
                    [
                        'district' => $row['district'],
                        'city' => $row['town'],
                        'contact_email' => $row['contact_email'],
                        'office_address' => $row['office_address'],
                        'website_url' => $row['website_url'],
                    ],
                );
            }

            Church::firstOrCreate(
                [
                    'name' => $row['church_name'],
                    'parishable_id' => $parishType === 'parish' ? $parish->id : $subParish->id,
                    'parishable_type' => $parishType,
                ],
                [
                    'type' => Str::kebab($parishType),
                    'city' => $row['town'],
                    'district' => $row['district'],
                    'contact_email' => $row['contact_email'],
                    'contact_phone' => $row['church_contact_phone'],
                    'street_address' => $row['office_address'],
                    'website_url' => $row['website_url'],
                    'latitude' => $row['latitude'],
                    'longitude' => $row['longitude'],
                    'street_address' => $row['office_address'],
                    'postal_address' => $row['postal_address'],
                    'google_maps_link' => $row['google_maps_link'],
                    'district' => $row['district'],
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
