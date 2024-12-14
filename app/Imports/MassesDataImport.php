<?php

namespace App\Imports;

use App\Models\Church;
use App\Models\Parish;
use App\Models\Diocese;
use App\Models\SubParish;
use Illuminate\Support\Str;
use App\Models\ActivityTime;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\Importable;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class MassesDataImport extends DefaultValueBinder implements SkipsEmptyRows, ToCollection, WithCustomValueBinder, WithHeadingRow, WithValidation
{
    use Importable;

    /**
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function collection(Collection $rows)
    {

        foreach ($rows as $row) {

            // dd($row);

            $diocese = Diocese::firstOrCreate(['name' => $row['diocese_name']]);

            $parishType = 'parish';
            $subParish = null;
            $parish = Parish::firstOrCreate(
                ['name' => $row['parish_name'], 'diocese_id' => $diocese->id],
                ['district' => $row['parish_district'], 'city' => $row['parish_town']],
            );

            if (isset($row['sub_parish_name'])) {
                $parishType = 'subParish';
                $subParish = SubParish::firstOrCreate(
                    ['name' => $row['sub_parish_name'], 'parish_id' => $parish->id],
                    ['district' => $row['sub_parish_district'], 'city' => $row['sub_parish_town']],
                );
            }

            $church = Church::firstOrCreate(
                [
                    'name' => $row['church_name'],
                    'parishable_id' => $parishType === 'parish' ? $parish->id : $subParish->id,
                    'parishable_type' => $parishType,
                ],
                [
                    'type' => Str::kebab($parishType),
                    'city' => $row['city'],
                    'district' => $parishType === 'parish' ? $row['parish_district'] : $row['parish_district'],
                ],
            );

            foreach (explode(',', $row['day_of_week']) as $dayOfWeek) {
                ActivityTime::firstOrCreate(
                    [
                        'occurrence' => 'weekly',
                        'church_id' => $church->id,
                        'type' => $row['activity_type'],
                        'start_time' => $row['start_time'],
                        'day_of_week' => $dayOfWeek,
                    ],
                    ['language' => $row['language']]
                );
            }
        }
    }

    public function isEmptyWhen(array $row): bool
    {
        $fields = [
            'diocese_name',
            'parish_name',
            'parish_town',
            'church_name',
            'city',
            'day_of_week',
            'language',
            'start_time',
            'activity_type',
        ];
        foreach ($fields as $field) {
            if (empty($row[$field])) {
                return true;
            }
        }

        return false;
    }

    public function bindValue(Cell $cell, $value)
    {
        if (is_numeric($value)) {
            // $cell->setValueExplicit($value, DataType::TYPE_NUMERIC);
            $cell->setValueExplicit(Date::excelToDateTimeObject($value)->format('H:i'), DataType::TYPE_STRING);

            return true;
        }

        // else return default behavior
        return parent::bindValue($cell, $value);
    }

    public function rules(): array
    {
        $required = 'required|string|max:255';

        return [
            'diocese_name' => $required,
            '*.diocese_name' => $required,

            'parish_name' => $required,
            '*.parish_name' => $required,

            'parish_town' => $required,
            '*.parish_town' => $required,

            'church_name' => $required,
            '*.church_name' => $required,

            'city' => $required,
            '*.city' => $required,

            'day_of_week' => $required,
            '*.day_of_week' => $required,

            'language' => $required,
            '*.language' => $required,

            'start_time' => ['required', 'date_format:H:i'],
            '*.start_time' => ['required', 'date_format:H:i'],

            'activity_type' => $required,
            '*.activity_type' => $required,
        ];
    }
}
