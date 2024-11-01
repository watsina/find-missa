<?php

namespace App\Imports;

use App\Models\Church;
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

            if (! isset($row['name']) || ! isset($row['church_type_name']) || ! isset($row['diocese_name'])) {
                continue;
            }

            Diocese::firstOrCreate(
                ['name' => $row['diocese_name']],
                ['type' => $row['diocese_type'], 'rite' => $row['rite_name']],
            );

            Church::firstOrCreate(
                ['name' => $row['name']],
                [
                    'type' => $row['church_type_name'],
                    'latitude' => $row['latitude'],
                    'longitude' => $row['longitude'],
                    'city' => $row['city'],
                ],
            );
        }
    }
}
