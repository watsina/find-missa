<?php

namespace App\Imports;

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
    /**
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function collection(Collection $rows)
    {

        foreach ($rows as $row) {

            $diocese = Diocese::firstOrCreate(
                [
                    'name' => $row['diocese_name'],
                ],
                [
                    'type' => $row['diocese_type'],
                ],
            );

            $parish = Parish::firstOrCreate(
                [
                    'name' => $row['parish_name'],
                    'diocese_id' => $diocese->id,
                ],
                [
                    'city' => $row['parish_town'],
                ],
            );

            Church::firstOrCreate(
                [
                    'name' => $row['church_name'],
                    'parishable_id' => $parish->id,
                    'parishable_type' => 'parish',
                ],
                [
                    'type' => $row['church_type'],
                    'latitude' => $row['latitude'],
                    'longitude' => $row['longitude'],
                    'city' => $row['city'],
                ],
            );
        }
    }

    public function rules(): array
    {
        $required = 'required|string|max:255';

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

            'latitude' => $required,
            '*.latitude' => $required,

            'longitude' => $required,
            '*.longitude' => $required,

            'city' => $required,
            'city' => $required,
        ];
    }
}
