<?php

namespace App\Imports;

use App\Models\Church;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ChurchImport implements SkipsEmptyRows, ToModel, WithHeadingRow
{
    /**
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        if (! isset($row['name']) || ! isset($row['church_type_name'])) {
            return null;
        }

        return new Church([
            'name' => $row['name'],
            'type' => $row['church_type_name'],
            'latitude' => $row['latitude'],
            'longitude' => $row['longitude'],
        ]);
    }
}
