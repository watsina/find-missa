<?php

namespace App\Models;

use App\Casts\Timecast;
use App\Enums\DayPattern;
use Laravel\Scout\Searchable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use App\Models\Concerns\HasScheduleLogic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActivitySchedule extends BaseModel
{
    use HasFactory;
    use HasScheduleLogic;
    use Searchable;

    protected static function booted(): void
    {
        static::created(fn () => Cache::clear());
        static::updated(fn () => Cache::clear());
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'church_id' => 'integer',
            'team_id' => 'integer',
            'schedule_period_id' => 'integer',
            'start_time' => Timecast::class,
            'end_time' => Timecast::class,
            'day_pattern' => DayPattern::class,
            'days_of_week' => 'array',
            'week_of_month' => 'integer',
            'has_day_variations' => 'boolean',
            'effective_from' => 'date:Y-m-d',
            'effective_until' => 'date:Y-m-d',
        ];
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return $this->only('start_time', 'day_pattern', 'type', 'language');
    }

    /**
     * Scope a query to only include schedules that match a given day name (e.g., "Sunday").
     *
     * Unlike scopeMatchesDayPattern, this does not require a full Carbon date
     * and excludes MonthlyNth patterns (which need specific date context).
     */
    public function scopeMatchesDayOfWeek(Builder $query, string $dayOfWeek): void
    {
        $normalizedDay = ucfirst(strtolower($dayOfWeek));
        $isWeekday = in_array($normalizedDay, ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']);
        $isSaturday = $normalizedDay === 'Saturday';
        $isSunday = $normalizedDay === 'Sunday';

        $query->where(function (Builder $q) use ($normalizedDay, $isWeekday, $isSaturday, $isSunday) {
            $q->where('day_pattern', DayPattern::Daily->value)
                ->when($isWeekday, fn (Builder $inner) => $inner->orWhere('day_pattern', DayPattern::Weekdays->value))
                ->when($isSaturday, fn (Builder $inner) => $inner->orWhere('day_pattern', DayPattern::Saturday->value))
                ->when($isSunday, fn (Builder $inner) => $inner->orWhere('day_pattern', DayPattern::Sunday->value))
                ->orWhere(function (Builder $inner) use ($normalizedDay) {
                    $inner->where('day_pattern', DayPattern::Specific->value)
                        ->whereJsonContains('days_of_week', $normalizedDay);
                });
        });
    }

    /**
     * Scope a query to only include schedules that match the given date's day pattern.
     */
    public function scopeMatchesDayPattern(Builder $query, Carbon $date): void
    {
        $dayOfWeek = $date->dayName;
        $isWeekday = in_array($dayOfWeek, ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']);
        $isSaturday = $dayOfWeek === 'Saturday';
        $isSunday = $dayOfWeek === 'Sunday';

        $query->where(function (Builder $q) use ($dayOfWeek, $isWeekday, $isSaturday, $isSunday, $date) {
            $q->where('day_pattern', DayPattern::Daily->value)
                ->when($isWeekday, fn (Builder $inner) => $inner->orWhere('day_pattern', DayPattern::Weekdays->value))
                ->when($isSaturday, fn (Builder $inner) => $inner->orWhere('day_pattern', DayPattern::Saturday->value))
                ->when($isSunday, fn (Builder $inner) => $inner->orWhere('day_pattern', DayPattern::Sunday->value))
                ->orWhere(function (Builder $inner) use ($dayOfWeek) {
                    $inner->where('day_pattern', DayPattern::Specific->value)
                        ->whereJsonContains('days_of_week', $dayOfWeek);
                })
                ->orWhere(function (Builder $inner) use ($dayOfWeek, $date) {
                    $weekNumber = (int) ceil($date->day / 7);
                    $isLastOccurrence = $date->copy()->addWeek()->month !== $date->month;

                    $inner->where('day_pattern', DayPattern::MonthlyNth->value)
                        ->where('day_of_week', $dayOfWeek)
                        ->where(function (Builder $weekQuery) use ($weekNumber, $isLastOccurrence) {
                            $weekQuery->where('week_of_month', $weekNumber);
                            if ($isLastOccurrence) {
                                $weekQuery->orWhere('week_of_month', self::WEEK_LAST);
                            }
                        });
                });
        });
    }

    /**
     * Check if this schedule occurs on a given date based on its day pattern.
     */
    public function occursOn(Carbon $date): bool
    {
        $dayPattern = $this->day_pattern instanceof DayPattern
            ? $this->day_pattern
            : DayPattern::tryFrom($this->day_pattern);

        $dayOfWeek = $date->dayName;

        return match ($dayPattern) {
            DayPattern::Daily => true,
            DayPattern::Weekdays => in_array($dayOfWeek, ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']),
            DayPattern::Saturday => $dayOfWeek === 'Saturday',
            DayPattern::Sunday => $dayOfWeek === 'Sunday',
            DayPattern::Specific => in_array($dayOfWeek, $this->days_of_week ?? []),
            DayPattern::MonthlyNth => $this->occursOnMonthlyNth($date),
            default => false,
        };
    }

    /**
     * Get the resolved values for a specific day, considering day details.
     *
     * @return array{start_time: string, end_time: string|null, language: string, activity_type_description: string|null, is_skipped: bool}
     */
    public function getResolvedValuesForDay(string $dayOfWeek): array
    {
        $dayDetail = $this->dayDetails->firstWhere('day_of_week', strtolower($dayOfWeek))
            ?? $this->dayDetails->firstWhere('day_of_week', $dayOfWeek);

        return [
            'start_time' => $dayDetail?->start_time ?? $this->start_time,
            'end_time' => $dayDetail?->end_time ?? $this->end_time,
            'language' => $dayDetail?->language ?? $this->language,
            'activity_type_description' => $dayDetail?->activity_type_description ?? $this->activity_type_description,
            'is_skipped' => $dayDetail?->is_skipped ?? false,
        ];
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function schedulePeriod(): BelongsTo
    {
        return $this->belongsTo(SchedulePeriod::class);
    }

    public function dayDetails(): HasMany
    {
        return $this->hasMany(ScheduleDayDetail::class);
    }
}
