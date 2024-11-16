<?php

namespace App\Imports;

use App\Models\Church;
use App\Models\Parish;
use App\Models\Diocese;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DataImport implements SkipsEmptyRows, ToCollection, WithHeadingRow
{
    /**
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function collection(Collection $rows)
    {

        foreach ($rows as $row) {

            // we'll use validatin instead
            // if (! isset($row['name']) || ! isset($row['church_type_name']) || ! isset($row['diocese_name'])) {
            //     continue;
            // }

            $diocese = Diocese::firstOrCreate(
                ['name' => $row['diocese_name']],
                ['type' => $row['diocese_type']],
            );

            $parish = Parish::firstOrCreate(
                ['name' => $row['parish_name'], 'diocese_id' => $diocese->id],
                ['city' => $row['parish_town']],
            );

            Church::firstOrCreate(
                ['name' => $row['church_name'], 'parishable_id' => $parish->id, 'parishable_type' => 'parish'],
                [
                    'type' => $row['church_type'],
                    'latitude' => $row['latitude'],
                    'longitude' => $row['longitude'],
                    'city' => $row['city'],
                ],
            );
        }
    }
}
