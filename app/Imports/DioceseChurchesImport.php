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
        $teamId = $this->diocese->team_id ?? session('current_team_id') ?? auth()->user()?->current_team_id;

        foreach ($rows as $row) {
            $parishType = 'parish';
            $subParish = null;

            $parish = Parish::firstOrCreate(
                ['name' => trim($row['parish_name']), 'diocese_id' => $this->diocese->id],
                [
                    'district' => trim($row['district']),
                    'city' => trim($row['town']),
                    'contact_email' => ! empty($row['contact_email']) ? trim($row['contact_email']) : null,
                    'office_address' => ! empty($row['office_address']) ? trim($row['office_address']) : null,
                    'website_url' => ! empty($row['website_url']) ? trim($row['website_url']) : null,
                    'team_id' => $teamId,
                ],
            );

            $hasSubParish = ! empty(trim((string) ($row['sub_parish_name'] ?? '')));

            if ($hasSubParish) {
                $parishType = 'subParish';
                $subParish = SubParish::firstOrCreate(
                    ['name' => trim($row['sub_parish_name']), 'parish_id' => $parish->id],
                    [
                        'district' => trim($row['district']),
                        'city' => trim($row['town']),
                        'contact_email' => ! empty($row['contact_email']) ? trim($row['contact_email']) : null,
                        'office_address' => ! empty($row['office_address']) ? trim($row['office_address']) : null,
                        'website_url' => ! empty($row['website_url']) ? trim($row['website_url']) : null,
                        'team_id' => $teamId,
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
                    'contact_email' => ! empty($row['contact_email']) ? trim($row['contact_email']) : null,
                    'contact_phone' => ! empty($row['church_contact_phone']) ? trim($row['church_contact_phone']) : null,
                    'street_address' => ! empty($row['office_address']) ? trim($row['office_address']) : null,
                    'website_url' => ! empty($row['website_url']) ? trim($row['website_url']) : null,
                    'latitude' => $row['latitude'] ?? null,
                    'longitude' => $row['longitude'] ?? null,
                    'postal_address' => ! empty($row['postal_address']) ? trim($row['postal_address']) : null,
                    'google_maps_link' => ! empty($row['google_maps_link']) ? trim($row['google_maps_link']) : null,
                    'team_id' => $teamId,
                ],
            );
        }
    }

    public function prepareForValidation(array $data, int $index): array
    {
        foreach ($data as $key => $val) {
            if (is_string($val)) {
                $data[$key] = trim($val);
            }
        }

        if (isset($data['postal_address']) && $data['postal_address'] !== '') {
            $data['postal_address'] = (string) $data['postal_address'];
        }

        if (isset($data['church_contact_phone']) && $data['church_contact_phone'] !== '') {
            $data['church_contact_phone'] = (string) $data['church_contact_phone'];
        }

        return $data;
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

            'city' => $optional,
            '*.city' => $optional,

            'postal_address' => $optional,
            '*.postal_address' => $optional,
        ];
    }
}
