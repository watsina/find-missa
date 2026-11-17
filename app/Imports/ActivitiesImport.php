<?php

namespace App\Imports;

use App\Models\Church;
use App\Models\Parish;
use App\Models\Diocese;
use App\Enums\DayPattern;
use App\Models\SubParish;
use Illuminate\Support\Str;
use App\Models\ActivityTime;
use Illuminate\Validation\Rule;
use App\Models\ActivitySchedule;
use App\Enums\ImportActivityType;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class ActivitiesImport extends DefaultValueBinder implements SkipsEmptyRows, ToCollection, WithCustomValueBinder, WithHeadingRow, WithValidation
{
    public function __construct(protected Diocese $diocese) {}

    public function collection(Collection $rows)
    {
        $teamId = $this->diocese->team_id ?? session('current_team_id') ?? auth()->user()?->current_team_id;

        foreach ($rows as $row) {
            $parishType = 'parish';
            $subParish = null;

            $parish = Parish::where('name', trim($row['parish_name']))
                ->where('diocese_id', $this->diocese->id)
                ->firstOrFail();

            $hasSubParish = ! empty(trim((string) ($row['sub_parish_name'] ?? '')));

            if ($hasSubParish) {
                $parishType = 'subParish';
                $subParish = SubParish::where('name', trim($row['sub_parish_name']))
                    ->where('parish_id', $parish->id)
                    ->firstOrFail();
            }

            $church = Church::where('name', trim($row['church_name']))
                ->where('parishable_id', $parishType === 'parish' ? $parish->id : $subParish->id)
                ->where('parishable_type', $parishType)
                ->firstOrFail();

            $rawActivityType = trim($row['activity_type']);
            $kebabType = Str::kebab($rawActivityType);
            $type = match ($kebabType) {
                'benediction' => 'adoration',
                default => $kebabType,
            };

            $startTime = date('H:i', strtotime(trim($row['start_time'])));
            $endTime = ! empty($row['end_time']) ? date('H:i', strtotime(trim($row['end_time']))) : null;
            $language = trim($row['language']);

            $patternInfo = $this->determineDayPatternAndDays(trim($row['day_of_week']));
            $dayPattern = $patternInfo['pattern'];
            $daysOfWeek = $patternInfo['days'];
            $dayOfWeek = $patternInfo['day_of_week'];
            $individualDays = $patternInfo['individual_days'];

            // 1. Create or find ActivitySchedule (v2)
            $scheduleQuery = ActivitySchedule::where('church_id', $church->id)
                ->where('type', $type)
                ->where('start_time', $startTime)
                ->where('day_pattern', $dayPattern->value)
                ->where('language', $language)
                ->whereNull('schedule_period_id');

            if ($daysOfWeek) {
                $schedule = $scheduleQuery->get()->first(function ($s) use ($daysOfWeek) {
                    $existingDays = $s->days_of_week ?? [];
                    sort($existingDays);
                    $newDays = $daysOfWeek;
                    sort($newDays);

                    return $existingDays === $newDays;
                });
            } else {
                $schedule = $scheduleQuery->whereNull('days_of_week')->first();
            }

            if (! $schedule) {
                ActivitySchedule::create([
                    'church_id' => $church->id,
                    'team_id' => $church->team_id ?? $teamId,
                    'type' => $type,
                    'activity_type_description' => $kebabType,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'language' => $language,
                    'day_pattern' => $dayPattern,
                    'days_of_week' => $daysOfWeek,
                    'day_of_week' => $dayOfWeek,
                ]);
            }

            // 2. Create legacy ActivityTime records for backward compatibility
            foreach ($individualDays as $day) {
                ActivityTime::firstOrCreate(
                    [
                        'occurrence' => 'weekly',
                        'church_id' => $church->id,
                        'type' => $kebabType,
                        'start_time' => $startTime,
                        'day_of_week' => $day,
                    ],
                    [
                        'team_id' => $church->team_id ?? $teamId,
                        'language' => $language,
                        'end_time' => $endTime,
                    ]
                );
            }
        }
    }

    /**
     * Determine day pattern, days of week array, and individual days list from raw string.
     *
     * @return array{pattern: DayPattern, days: array<string>|null, day_of_week: string|null, individual_days: array<string>}
     */
    protected function determineDayPatternAndDays(string $rawDayOfWeek): array
    {
        $normalized = strtolower(trim($rawDayOfWeek));

        if ($normalized === 'daily') {
            return [
                'pattern' => DayPattern::Daily,
                'days' => null,
                'day_of_week' => null,
                'individual_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
            ];
        }

        if ($normalized === 'weekdays') {
            return [
                'pattern' => DayPattern::Weekdays,
                'days' => null,
                'day_of_week' => null,
                'individual_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
            ];
        }

        $days = array_values(array_filter(array_map(
            fn ($d) => ucfirst(strtolower(trim($d))),
            explode(',', $rawDayOfWeek)
        )));

        $sortedDays = $days;
        sort($sortedDays);

        $weekdays = ['Friday', 'Monday', 'Thursday', 'Tuesday', 'Wednesday'];
        $allDays = ['Friday', 'Monday', 'Saturday', 'Sunday', 'Thursday', 'Tuesday', 'Wednesday'];

        if ($sortedDays === $weekdays) {
            return [
                'pattern' => DayPattern::Weekdays,
                'days' => null,
                'day_of_week' => null,
                'individual_days' => $days,
            ];
        }

        if ($sortedDays === ['Saturday']) {
            return [
                'pattern' => DayPattern::Saturday,
                'days' => null,
                'day_of_week' => 'Saturday',
                'individual_days' => ['Saturday'],
            ];
        }

        if ($sortedDays === ['Sunday']) {
            return [
                'pattern' => DayPattern::Sunday,
                'days' => null,
                'day_of_week' => 'Sunday',
                'individual_days' => ['Sunday'],
            ];
        }

        if ($sortedDays === $allDays) {
            return [
                'pattern' => DayPattern::Daily,
                'days' => null,
                'day_of_week' => null,
                'individual_days' => $days,
            ];
        }

        return [
            'pattern' => DayPattern::Specific,
            'days' => $days,
            'day_of_week' => count($days) === 1 ? $days[0] : null,
            'individual_days' => $days,
        ];
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

    public function prepareForValidation(array $data, int $index): array
    {
        foreach ($data as $key => $val) {
            if (is_string($val)) {
                $data[$key] = trim($val);
            }
        }

        if (! empty($data['start_time']) && preg_match('/^\d{1,2}:\d{2}$/', (string) $data['start_time'])) {
            $parts = explode(':', (string) $data['start_time']);
            $data['start_time'] = sprintf('%02d:%02d', (int) $parts[0], (int) $parts[1]);
        }

        if (! empty($data['end_time']) && preg_match('/^\d{1,2}:\d{2}$/', (string) $data['end_time'])) {
            $parts = explode(':', (string) $data['end_time']);
            $data['end_time'] = sprintf('%02d:%02d', (int) $parts[0], (int) $parts[1]);
        }

        return $data;
    }

    public function bindValue(Cell $cell, $value)
    {
        if (is_numeric($value) && Date::isDateTime($cell)) {
            try {
                $cell->setValueExplicit(Date::excelToDateTimeObject($value)->format('H:i'), DataType::TYPE_STRING);

                return true;
            } catch (\Throwable) {
                // fall through to parent
            }
        }

        return parent::bindValue($cell, $value);
    }

    public function rules(): array
    {
        $required = 'required|string|max:255';
        $type = ['required', Rule::enum(ImportActivityType::class)];
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

            'language' => 'required|string|max:100',
            '*.language' => 'required|string|max:100',

            'start_time' => ['required', 'date_format:H:i'],
            '*.start_time' => ['required', 'date_format:H:i'],

            'end_time' => ['nullable', 'date_format:H:i'],
            '*.end_time' => ['nullable', 'date_format:H:i'],

            'activity_type' => $type,
            '*.activity_type' => $type,
        ];
    }
}
