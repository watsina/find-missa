<?php

namespace App\Imports;

use App\Models\Team;
use App\Models\Church;
use App\Models\Parish;
use App\Models\Diocese;
use App\Enums\DayPattern;
use App\Models\SubParish;
use Illuminate\Support\Str;
use App\Models\ActivityTime;
use App\Models\ActivitySchedule;
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

    public function __construct(protected ?Team $team = null) {}

    public function collection(Collection $rows)
    {
        $teamId = $this->team?->id ?? session('current_team_id') ?? auth()->user()?->current_team_id;

        foreach ($rows as $row) {
            $dioceseName = trim($row['diocese_name']);
            $diocese = Diocese::firstOrCreate(
                ['name' => $dioceseName],
                ['team_id' => $teamId]
            );

            $parishType = 'parish';
            $parishName = trim($row['parish_name']);
            $parishTown = trim($row['parish_town'] ?? $row['city'] ?? '');
            $parishDistrict = ! empty($row['parish_district']) ? trim($row['parish_district']) : null;

            $parish = Parish::firstOrCreate(
                ['name' => $parishName, 'diocese_id' => $diocese->id],
                [
                    'district' => $parishDistrict,
                    'city' => $parishTown,
                    'team_id' => $teamId,
                ],
            );

            $parishableId = $parish->id;
            $hasSubParish = ! empty(trim((string) ($row['sub_parish_name'] ?? '')));

            if ($hasSubParish) {
                $parishType = 'subParish';
                $subParishName = trim($row['sub_parish_name']);
                $subParishTown = ! empty($row['sub_parish_town']) ? trim($row['sub_parish_town']) : $parishTown;
                $subParishDistrict = ! empty($row['sub_parish_district']) ? trim($row['sub_parish_district']) : $parishDistrict;

                $subParish = SubParish::firstOrCreate(
                    ['name' => $subParishName, 'parish_id' => $parish->id],
                    [
                        'district' => $subParishDistrict,
                        'city' => $subParishTown,
                        'team_id' => $teamId,
                    ],
                );
                $parishableId = $subParish->id;
            }

            $churchName = trim($row['church_name']);
            $churchCity = trim($row['city'] ?? $parishTown);
            $churchDistrict = $parishType === 'subParish'
                ? ($subParishDistrict ?? $parishDistrict)
                : $parishDistrict;

            $church = Church::firstOrCreate(
                [
                    'name' => $churchName,
                    'parishable_id' => $parishableId,
                    'parishable_type' => $parishType,
                ],
                [
                    'type' => Str::kebab($parishType),
                    'city' => $churchCity,
                    'district' => $churchDistrict,
                    'team_id' => $teamId,
                ],
            );

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
            'diocese_name',
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
        $optional = 'nullable|string|max:255';

        return [
            'diocese_name' => $required,
            '*.diocese_name' => $required,

            'parish_name' => $required,
            '*.parish_name' => $required,

            'parish_town' => $optional,
            '*.parish_town' => $optional,

            'parish_district' => $optional,
            '*.parish_district' => $optional,

            'sub_parish_name' => $optional,
            '*.sub_parish_name' => $optional,

            'sub_parish_town' => $optional,
            '*.sub_parish_town' => $optional,

            'sub_parish_district' => $optional,
            '*.sub_parish_district' => $optional,

            'church_name' => $required,
            '*.church_name' => $required,

            'city' => $optional,
            '*.city' => $optional,

            'day_of_week' => $required,
            '*.day_of_week' => $required,

            'language' => 'required|string|max:100',
            '*.language' => 'required|string|max:100',

            'start_time' => ['required', 'date_format:H:i'],
            '*.start_time' => ['required', 'date_format:H:i'],

            'end_time' => ['nullable', 'date_format:H:i'],
            '*.end_time' => ['nullable', 'date_format:H:i'],

            'activity_type' => $required,
            '*.activity_type' => $required,
        ];
    }
}
