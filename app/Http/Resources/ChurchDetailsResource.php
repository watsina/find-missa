<?php

namespace App\Http\Resources;

use App\Enums\DayPattern;
use Illuminate\Http\Request;
use App\Models\OneTimeActivity;
use App\Models\ActivitySchedule;
use Illuminate\Support\Collection;
use Illuminate\Http\Resources\Json\JsonResource;

class ChurchDetailsResource extends JsonResource
{
    /**
     * Create a new resource instance.
     */
    public function __construct(
        $resource,
        protected ?Collection $schedulePeriods = null
    ) {
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $activitySchedules = $this->activitySchedules ?? collect();
        $oneTimeActivities = $this->oneTimeActivities ?? collect();

        // Separate schedules by their schedule context
        $regularSchedules = $activitySchedules->filter(fn ($s) => $s->schedule_period_id === null);
        $schedulePeriodSchedules = $activitySchedules->filter(fn ($s) => $s->schedule_period_id !== null);

        // Filter one-time activities to only include current or future dates
        $today = now()->toDateString();
        $futureOneTimeActivities = $oneTimeActivities->filter(fn ($a) => $a->date && $a->date->gte($today));

        return [
            'details' => [
                'id' => $this->id,
                'name' => $this->name,
                'type' => $this->type,
                'coordinates' => $this->when($this->longitude || $this->latitude, "($this->longitude, $this->latitude)"),
                'street_address' => $this->street_address,
                'city' => $this->city,
                'district' => $this->district,
                'postal_address' => $this->postal_address,
                'contact_email' => $this->contact_email,
                'website_url' => $this->website_url,
                'google_maps_link' => $this->google_maps_link,
                'contact_phone' => $this->contact_phone,
                'longitude' => $this->longitude,
                'latitude' => $this->latitude,
                'parish' => $this->whenLoaded('parishable'),
            ],

            // Regular schedule (no schedule period) grouped by day pattern
            'regular_schedule' => [
                'mass' => $this->formatSchedulesByDayPattern(
                    $regularSchedules->filter(fn ($s) => in_array($s->type, ['mass', 'anticipation-mass', 'traditional-latin-mass']))
                ),
                'confession' => $this->formatSchedulesByDayPattern(
                    $regularSchedules->filter(fn ($s) => $s->type === 'confession')
                ),
                'adoration' => $this->formatSchedulesByDayPattern(
                    $regularSchedules->filter(fn ($s) => $s->type === 'adoration')
                ),
            ],

            // One-time activities (future dates only)
            'one_time_activities' => [
                'mass' => $this->formatOneTimeActivities(
                    $futureOneTimeActivities->filter(fn ($a) => in_array($a->type, ['mass', 'anticipation-mass', 'traditional-latin-mass']))
                ),
                'confession' => $this->formatOneTimeActivities(
                    $futureOneTimeActivities->filter(fn ($a) => $a->type === 'confession')
                ),
                'adoration' => $this->formatOneTimeActivities(
                    $futureOneTimeActivities->filter(fn ($a) => $a->type === 'adoration')
                ),
            ],

            // Liturgical schedules (holiday schedules)
            'liturgical_schedules' => LiturgicalScheduleResource::collection($this->whenLoaded('liturgicalSchedules')),

            // Adoration chapel (permanent adoration space with its own hours)
            'adoration_chapel' => $this->when(
                $this->relationLoaded('adorationChapel') && $this->adorationChapel !== null,
                fn () => new AdorationChapelResource($this->adorationChapel)
            ),

            // Schedule period specific activities
            'schedule_periods' => $this->schedulePeriods?->map(function ($period) use ($schedulePeriodSchedules) {
                $periodSchedules = $schedulePeriodSchedules->filter(fn ($s) => $s->schedule_period_id === $period->id);

                return [
                    'id' => $period->id,
                    'name' => $period->name,
                    'slug' => $period->slug,
                    'description' => $period->description,
                    'start_date' => $period->start_date?->toDateString(),
                    'end_date' => $period->end_date?->toDateString(),
                    'formatted_date_range' => $period->formatted_date_range,
                    'is_current' => $period->isCurrent(),
                    'activities' => [
                        'mass' => $this->formatSchedulesByDayPattern(
                            $periodSchedules->filter(fn ($s) => in_array($s->type, ['mass', 'anticipation-mass', 'traditional-latin-mass']))
                        ),
                        'confession' => $this->formatSchedulesByDayPattern(
                            $periodSchedules->filter(fn ($s) => $s->type === 'confession')
                        ),
                        'adoration' => $this->formatSchedulesByDayPattern(
                            $periodSchedules->filter(fn ($s) => $s->type === 'adoration')
                        ),
                    ],
                ];
            }) ?? [],
        ];
    }

    /**
     * Format schedules grouped by day pattern.
     *
     * @return array<string, array>
     */
    protected function formatSchedulesByDayPattern(Collection $schedules): array
    {
        return [
            'weekdays' => $this->formatSchedules(
                $schedules->filter(fn ($s) => $this->getDayPatternValue($s) === DayPattern::Weekdays->value)
            ),
            'saturday' => $this->formatSchedules(
                $schedules->filter(fn ($s) => $this->getDayPatternValue($s) === DayPattern::Saturday->value)
            ),
            'sunday' => $this->formatSchedules(
                $schedules->filter(fn ($s) => $this->getDayPatternValue($s) === DayPattern::Sunday->value)
            ),
            'daily' => $this->formatSchedules(
                $schedules->filter(fn ($s) => $this->getDayPatternValue($s) === DayPattern::Daily->value)
            ),
            'specific' => $this->formatSpecificSchedules(
                $schedules->filter(fn ($s) => $this->getDayPatternValue($s) === DayPattern::Specific->value)
            ),
            'monthly_nth' => $this->formatMonthlySchedules(
                $schedules->filter(fn ($s) => $this->getDayPatternValue($s) === DayPattern::MonthlyNth->value)
            ),
        ];
    }

    /**
     * Get the day pattern value from a schedule.
     */
    protected function getDayPatternValue(ActivitySchedule $schedule): string
    {
        $dayPattern = $schedule->day_pattern;

        if ($dayPattern instanceof DayPattern) {
            return $dayPattern->value;
        }

        return (string) $dayPattern;
    }

    /**
     * Format a collection of schedules.
     *
     * @return array<int, array>
     */
    protected function formatSchedules(Collection $schedules): array
    {
        return $schedules
            ->sortBy('start_time')
            ->map(fn ($s) => $this->formatSchedule($s))
            ->values()
            ->all();
    }

    /**
     * Format specific day schedules (grouped by days_of_week).
     *
     * @return array<int, array>
     */
    protected function formatSpecificSchedules(Collection $schedules): array
    {
        return $schedules
            ->sortBy('start_time')
            ->map(fn ($s) => $this->formatSchedule($s))
            ->values()
            ->all();
    }

    /**
     * Format monthly-nth schedules (grouped by week of month and day).
     *
     * @return array<int, array>
     */
    protected function formatMonthlySchedules(Collection $schedules): array
    {
        $weekLabels = [
            1 => 'First',
            2 => 'Second',
            3 => 'Third',
            4 => 'Fourth',
            5 => 'Last',
        ];

        $grouped = [];

        foreach ($schedules as $schedule) {
            $weekLabel = $weekLabels[$schedule->week_of_month] ?? 'Unknown';
            $key = "{$weekLabel} {$schedule->day_of_week}";

            if (! isset($grouped[$key])) {
                $grouped[$key] = [
                    'label' => $key,
                    'week_of_month' => $schedule->week_of_month,
                    'day_of_week' => $schedule->day_of_week,
                    'schedules' => [],
                ];
            }

            $grouped[$key]['schedules'][] = $this->formatSchedule($schedule);
        }

        // Sort by week_of_month, then by day order
        $dayOrder = ['Sunday' => 0, 'Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3, 'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6];

        uasort($grouped, function ($a, $b) use ($dayOrder) {
            if ($a['week_of_month'] !== $b['week_of_month']) {
                return $a['week_of_month'] <=> $b['week_of_month'];
            }

            return ($dayOrder[$a['day_of_week']] ?? 7) <=> ($dayOrder[$b['day_of_week']] ?? 7);
        });

        return array_values($grouped);
    }

    /**
     * Format a single activity schedule.
     *
     * @return array<string, mixed>
     */
    protected function formatSchedule(ActivitySchedule $schedule): array
    {
        return [
            'id' => $schedule->id,
            'type' => $schedule->type,
            'activity_type_description' => $schedule->activity_type_description,
            'start_time' => $schedule->start_time,
            'end_time' => $schedule->end_time,
            'language' => $schedule->language,
            'day_pattern' => $this->getDayPatternValue($schedule),
            'days_of_week' => $schedule->days_of_week,
            'week_of_month' => $schedule->week_of_month,
            'week_of_month_label' => $schedule->week_of_month_label,
            'day_of_week' => $schedule->day_of_week,
            'has_day_variations' => $schedule->has_day_variations,
            'day_details' => $schedule->has_day_variations
                ? ScheduleDayDetailResource::collection($schedule->dayDetails)
                : [],
            'effective_from' => $schedule->effective_from?->toDateString(),
            'effective_until' => $schedule->effective_until?->toDateString(),
        ];
    }

    /**
     * Format one-time activities.
     *
     * @return array<int, array>
     */
    protected function formatOneTimeActivities(Collection $activities): array
    {
        return $activities
            ->sortBy('date')
            ->map(fn ($a) => $this->formatOneTimeActivity($a))
            ->values()
            ->all();
    }

    /**
     * Format a single one-time activity.
     *
     * @return array<string, mixed>
     */
    protected function formatOneTimeActivity(OneTimeActivity $activity): array
    {
        return [
            'id' => $activity->id,
            'type' => $activity->type,
            'activity_type_description' => $activity->activity_type_description,
            'date' => $activity->date->toDateString(),
            'start_time' => $activity->start_time,
            'end_time' => $activity->end_time,
            'language' => $activity->language,
            'reason' => $activity->reason,
        ];
    }
}
