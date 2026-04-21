<?php

namespace App\Models;

use App\Casts\Timecast;
use Laravel\Scout\Searchable;
use Illuminate\Support\Carbon;
use App\Enums\ActivityOccurrence;
use App\Models\Concerns\HasScheduleLogic;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActivityTime extends BaseModel
{
    use HasFactory;
    use HasScheduleLogic;
    use Searchable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts()
    {
        return [
            'id' => 'integer',
            'church_id' => 'integer',
            'schedule_period_id' => 'integer',
            'date' => 'date:Y-m-d',
            'effective_from' => 'date:Y-m-d',
            'effective_until' => 'date:Y-m-d',
            'bi_weekly_start_date' => 'date:Y-m-d',
            'start_time' => Timecast::class,
            'end_time' => Timecast::class,
            'week_of_month' => 'integer',
            'occurrence' => ActivityOccurrence::class,
        ];
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        return $this->only('start_time', 'occurrence', 'day_of_week', 'type');
    }

    /**
     * Check if this activity occurs on a given date based on its occurrence pattern.
     */
    public function occursOn(Carbon $date): bool
    {
        $occurrence = $this->occurrence instanceof ActivityOccurrence
            ? $this->occurrence
            : ActivityOccurrence::tryFrom($this->occurrence);

        return match ($occurrence) {
            ActivityOccurrence::Daily => true,
            ActivityOccurrence::OneTime => $this->date?->isSameDay($date) ?? false,
            ActivityOccurrence::Weekly => $this->occursOnWeekly($date),
            ActivityOccurrence::MonthlyNthWeekday => $this->occursOnMonthlyNth($date),
            default => false,
        };
    }

    /**
     * Check if this activity occurs on a given date for weekly occurrence.
     */
    protected function occursOnWeekly(Carbon $date): bool
    {
        return strtolower($date->format('l')) === strtolower($this->day_of_week);
    }

    /**
     * Override trait method to use case-insensitive comparison for v1 data compatibility.
     */
    protected function occursOnMonthlyNth(Carbon $date): bool
    {
        if (! $this->week_of_month || ! $this->day_of_week) {
            return false;
        }

        if (strtolower($date->format('l')) !== strtolower($this->day_of_week)) {
            return false;
        }

        $weekNumber = (int) ceil($date->day / 7);

        if ($this->week_of_month === self::WEEK_LAST) {
            $nextWeek = $date->copy()->addWeek();

            return $nextWeek->month !== $date->month;
        }

        return $weekNumber === $this->week_of_month;
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function schedulePeriod(): BelongsTo
    {
        return $this->belongsTo(SchedulePeriod::class);
    }

    public function exceptions(): HasMany
    {
        return $this->hasMany(ActivityTimeException::class);
    }
}
