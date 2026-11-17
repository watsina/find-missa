<?php

namespace App\Imports;

use App\Models\Team;
use App\Models\Church;
use App\Models\Parish;
use App\Models\Diocese;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ParishChurchDataImport implements SkipsEmptyRows, ToCollection, WithHeadingRow, WithValidation
{
    public function __construct(protected ?Team $team = null) {}

    public function collection(Collection $rows)
    {
        $teamId = $this->team?->id ?? session('current_team_id') ?? auth()->user()?->current_team_id;

        foreach ($rows as $row) {
            $diocese = Diocese::firstOrCreate(
                [
                    'name' => trim($row['diocese_name']),
                ],
                [
                    'type' => trim($row['diocese_type']),
                    'team_id' => $teamId,
                ],
            );

            $parish = Parish::firstOrCreate(
                [
                    'name' => trim($row['parish_name']),
                    'diocese_id' => $diocese->id,
                ],
                [
                    'city' => trim($row['parish_town']),
                    'team_id' => $teamId,
                ],
            );

            Church::firstOrCreate(
                [
                    'name' => trim($row['church_name']),
                    'parishable_id' => $parish->id,
                    'parishable_type' => 'parish',
                ],
                [
                    'type' => trim($row['church_type']),
                    'latitude' => $row['latitude'],
                    'longitude' => $row['longitude'],
                    'city' => trim($row['city']),
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

        return $data;
    }

    public function rules(): array
    {
        $required = 'required|string|max:255';
        $coordinate = 'required|numeric|between:-180.9999999,180.9999999';

        return [
            'diocese_name' => $required,
            '*.diocese_name' => $required,

            'diocese_type' => $required,
            '*.diocese_type' => $required,

            'parish_name' => $required,
            '*.parish_name' => $required,

            'parish_town' => $required,
            '*.parish_town' => $required,

            'church_name' => $required,
            '*.church_name' => $required,

            'church_type' => $required,
            '*.church_type' => $required,

            'latitude' => $coordinate,
            '*.latitude' => $coordinate,

            'longitude' => $coordinate,
            '*.longitude' => $coordinate,

            'city' => $required,
            '*.city' => $required,
        ];
    }
}
