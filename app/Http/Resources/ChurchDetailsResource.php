<?php

namespace App\Http\Resources;

use App\Models\ActivityTime;
use Illuminate\Http\Request;
use App\Enums\ActivityOccurrence;
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
        $activities = $this->activities;

        // Separate activities by their schedule context
        $regularActivities = $activities->filter(fn ($a) => $a->schedule_period_id === null);
        $schedulePeriodActivities = $activities->filter(fn ($a) => $a->schedule_period_id !== null);

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

            // Regular schedule (no schedule period)
            'regular_schedule' => [
                'mass' => $this->formatActivitiesByOccurrence(
                    $regularActivities->filter(fn ($a) => in_array($a->type, ['mass', 'anticipation-mass', 'traditional-latin-mass']))
                ),
                'confession' => $this->formatActivitiesByOccurrence(
                    $regularActivities->filter(fn ($a) => $a->type === 'confession')
                ),
                'adoration' => $this->formatActivitiesByOccurrence(
                    $regularActivities->filter(fn ($a) => $a->type === 'adoration')
                ),
            ],

            // Schedule period specific activities
            'schedule_periods' => $this->schedulePeriods?->map(function ($period) use ($schedulePeriodActivities) {
                $periodActivities = $schedulePeriodActivities->filter(fn ($a) => $a->schedule_period_id === $period->id);

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
                        'mass' => $this->formatActivitiesByOccurrence(
                            $periodActivities->filter(fn ($a) => in_array($a->type, ['mass', 'anticipation-mass', 'traditional-latin-mass']))
                        ),
                        'confession' => $this->formatActivitiesByOccurrence(
                            $periodActivities->filter(fn ($a) => $a->type === 'confession')
                        ),
                        'adoration' => $this->formatActivitiesByOccurrence(
                            $periodActivities->filter(fn ($a) => $a->type === 'adoration')
                        ),
                    ],
                ];
            }) ?? [],

            // Legacy format for backward compatibility
            'mass' => $this->activities()->whereIn('type', ['mass', 'anticipation-mass', 'traditional-latin-mass'])->get(),
            'confession' => $this->activities()->where('type', 'confession')->get(),
            'adoration' => $this->activities()->where('type', 'adoration')->get(),
        ];
    }

    /**
     * Format activities grouped by occurrence type.
     *
     * @return array<string, mixed>
     */
    protected function formatActivitiesByOccurrence(Collection $activities): array
    {
        $weekly = $activities->filter(fn ($a) => $this->getOccurrenceValue($a) === 'weekly');
        $daily = $activities->filter(fn ($a) => $this->getOccurrenceValue($a) === 'daily');
        $monthlyNthWeekday = $activities->filter(fn ($a) => $this->getOccurrenceValue($a) === 'monthly-nth-weekday');
        $oneTime = $activities->filter(fn ($a) => $this->getOccurrenceValue($a) === 'one-time');

        // Filter one-time events to only include current or future dates
        $today = now()->toDateString();
        $oneTime = $oneTime->filter(fn ($a) => $a->date && $a->date->gte($today));

        return [
            'weekly' => $this->formatWeeklyActivities($weekly),
            'daily' => $daily->map(fn ($a) => $this->formatActivity($a))->values()->all(),
            'monthly' => $this->formatMonthlyActivities($monthlyNthWeekday),
            'one_time' => $oneTime->map(fn ($a) => $this->formatActivity($a))->values()->all(),
        ];
    }

    /**
     * Get the occurrence value as a string.
     */
    protected function getOccurrenceValue(ActivityTime $activity): string
    {
        $occurrence = $activity->occurrence;

        if ($occurrence instanceof ActivityOccurrence) {
            return $occurrence->value;
        }

        return (string) $occurrence;
    }

    /**
     * Format weekly activities grouped by day of week.
     *
     * @return array<string, array>
     */
    protected function formatWeeklyActivities(Collection $activities): array
    {
        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $grouped = [];

        foreach ($days as $day) {
            $dayActivities = $activities->filter(fn ($a) => strcasecmp($a->day_of_week, $day) === 0);
            if ($dayActivities->isNotEmpty()) {
                $grouped[$day] = $dayActivities
                    ->sortBy('start_time')
                    ->map(fn ($a) => $this->formatActivity($a))
                    ->values()
                    ->all();
            }
        }

        return $grouped;
    }

    /**
     * Format monthly activities grouped by week of month and day.
     *
     * @return array<string, array>
     */
    protected function formatMonthlyActivities(Collection $activities): array
    {
        $weekLabels = [
            1 => 'First',
            2 => 'Second',
            3 => 'Third',
            4 => 'Fourth',
            5 => 'Last',
        ];

        $grouped = [];

        foreach ($activities as $activity) {
            $weekLabel = $weekLabels[$activity->week_of_month] ?? 'Unknown';
            $key = "{$weekLabel} {$activity->day_of_week}";

            if (! isset($grouped[$key])) {
                $grouped[$key] = [
                    'label' => $key,
                    'week_of_month' => $activity->week_of_month,
                    'day_of_week' => $activity->day_of_week,
                    'activities' => [],
                ];
            }

            $grouped[$key]['activities'][] = $this->formatActivity($activity);
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
     * Format a single activity time.
     *
     * @return array<string, mixed>
     */
    protected function formatActivity(ActivityTime $activity): array
    {
        return [
            'id' => $activity->id,
            'type' => $activity->type,
            'activity_type_description' => $activity->activity_type_description,
            'start_time' => $activity->start_time,
            'end_time' => $activity->end_time,
            'language' => $activity->language,
            'occurrence' => $this->getOccurrenceValue($activity),
            'day_of_week' => $activity->day_of_week,
            'week_of_month' => $activity->week_of_month,
            'week_of_month_label' => $activity->week_of_month_label,
            'date' => $activity->date?->toDateString(),
            'effective_from' => $activity->effective_from?->toDateString(),
            'effective_until' => $activity->effective_until?->toDateString(),
            'has_exceptions' => $activity->exceptions->isNotEmpty(),
        ];
    }
}
