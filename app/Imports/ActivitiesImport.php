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
use Maatwebsite\Excel\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;

class ActivitiesImport extends DefaultValueBinder implements SkipsEmptyRows, ToCollection, WithCustomValueBinder, WithHeadingRow, WithValidation
{
    public function __construct(protected Diocese $diocese) {}

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $parishType = 'parish';
            $subParish = null;

            $parish = Parish::where('name', $row['parish_name'])
                ->where('diocese_id', $this->diocese->id)
                ->firstOrFail();

            if (isset($row['sub_parish_name'])) {
                $parishType = 'subParish';
                $subParish = SubParish::where('name', $row['sub_parish_name'])
                    ->where('parish_id', $parish->id)
                    ->firstOrFail();
            }

            $church = Church::where('name', $row['church_name'])
                ->where('parishable_id', $parishType === 'parish' ? $parish->id : $subParish->id)
                ->where('parishable_type', $parishType)
                ->firstOrFail();
            // ->first();

            // if (!$church) {
            //     dd($row['church_name']);
            // }

            foreach (explode(',', $row['day_of_week']) as $dayOfWeek) {
                ActivityTime::firstOrCreate(
                    [
                        'occurrence' => 'weekly',
                        'church_id' => $church->id,
                        'type' => Str::kebab($row['activity_type']),
                        'start_time' => $row['start_time'],
                        'day_of_week' => trim($dayOfWeek),
                    ],
                    ['language' => $row['language']]
                );
            }
        }
    }

    public function isEmptyWhen(array $row): bool
    {
        $fields = [
            'parish_name',
            'church_name',
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
        $parish = 'required|string|exists:parishes,name';
        $subParish = 'nullable|string|exists:sub_parishes,name';
        $optional = 'nullable|string|max:255';

        return [
            'parish_name' => $required,
            '*.parish_name' => $required,

            'sub_parish_name' => $optional,
            '*.sub_parish_name' => $optional,

            'church_name' => $required,
            '*.church_name' => $required,

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
