<?php

namespace App\Models;

use App\Casts\Timecast;
use App\Enums\DayPattern;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdorationChapelSchedule extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'adoration_chapel_id' => 'integer',
            'day_pattern' => DayPattern::class,
            'days_of_week' => 'array',
            'opens_at' => Timecast::class,
            'closes_at' => Timecast::class,
            'is_open_24_hours' => 'boolean',
            'is_closed' => 'boolean',
            'effective_from' => 'date:Y-m-d',
            'effective_until' => 'date:Y-m-d',
        ];
    }

    /**
     * Get the chapel that owns this schedule.
     */
    public function adorationChapel(): BelongsTo
    {
        return $this->belongsTo(AdorationChapel::class);
    }

    /**
     * Get the day details for this schedule.
     */
    public function dayDetails(): HasMany
    {
        return $this->hasMany(AdorationChapelScheduleDetail::class);
    }

    /**
     * Scope a query to only include schedules effective on a given date.
     */
    public function scopeEffectiveOn(Builder $query, Carbon|string|null $date = null): void
    {
        $date = $date instanceof Carbon ? $date : Carbon::parse($date ?? 'today');
        $dateString = $date->toDateString();

        $query->where(function (Builder $q) use ($dateString) {
            $q->where(function (Builder $inner) {
                $inner->whereNull('effective_from')
                    ->whereNull('effective_until');
            })
                ->orWhere(function (Builder $inner) use ($dateString) {
                    $inner->whereNotNull('effective_from')
                        ->whereNull('effective_until')
                        ->whereDate('effective_from', '<=', $dateString);
                })
                ->orWhere(function (Builder $inner) use ($dateString) {
                    $inner->whereNull('effective_from')
                        ->whereNotNull('effective_until')
                        ->whereDate('effective_until', '>=', $dateString);
                })
                ->orWhere(function (Builder $inner) use ($dateString) {
                    $inner->whereNotNull('effective_from')
                        ->whereNotNull('effective_until')
                        ->whereDate('effective_from', '<=', $dateString)
                        ->whereDate('effective_until', '>=', $dateString);
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

        $query->where(function (Builder $q) use ($dayOfWeek, $isWeekday, $isSaturday, $isSunday) {
            $q->where('day_pattern', DayPattern::Daily->value)
                ->when($isWeekday, fn (Builder $inner) => $inner->orWhere('day_pattern', DayPattern::Weekdays->value))
                ->when($isSaturday, fn (Builder $inner) => $inner->orWhere('day_pattern', DayPattern::Saturday->value))
                ->when($isSunday, fn (Builder $inner) => $inner->orWhere('day_pattern', DayPattern::Sunday->value))
                ->orWhere(function (Builder $inner) use ($dayOfWeek) {
                    $inner->where('day_pattern', DayPattern::Specific->value)
                        ->whereJsonContains('days_of_week', $dayOfWeek);
                });
        });
    }

    /**
     * Check if this schedule is currently effective.
     */
    public function isEffective(?Carbon $date = null): bool
    {
        $date = $date ?? Carbon::today();

        if (is_null($this->effective_from) && is_null($this->effective_until)) {
            return true;
        }

        if (! is_null($this->effective_from) && $this->effective_from->gt($date)) {
            return false;
        }

        if (! is_null($this->effective_until) && $this->effective_until->lt($date)) {
            return false;
        }

        return true;
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
            default => false,
        };
    }

    /**
     * Get the resolved hours for a specific day, considering day details.
     *
     * @return array{opens_at: string|null, closes_at: string|null, is_open_24_hours: bool, is_closed: bool}
     */
    public function getResolvedHoursForDay(string $dayOfWeek): array
    {
        $dayDetail = $this->dayDetails->firstWhere('day_of_week', strtolower($dayOfWeek))
            ?? $this->dayDetails->firstWhere('day_of_week', $dayOfWeek);

        // If there's a day detail override, use its values
        if ($dayDetail) {
            return [
                'opens_at' => $dayDetail->opens_at ?? $this->opens_at,
                'closes_at' => $dayDetail->closes_at ?? $this->closes_at,
                'is_open_24_hours' => $dayDetail->is_open_24_hours ?? $this->is_open_24_hours,
                'is_closed' => $dayDetail->is_closed ?? $this->is_closed,
            ];
        }

        // Otherwise, use the schedule's default values
        return [
            'opens_at' => $this->opens_at,
            'closes_at' => $this->closes_at,
            'is_open_24_hours' => $this->is_open_24_hours,
            'is_closed' => $this->is_closed,
        ];
    }

    /**
     * Get a formatted representation of this schedule for display.
     */
    public function getFormattedSchedule(): array
    {
        $dayPattern = $this->day_pattern instanceof DayPattern
            ? $this->day_pattern
            : DayPattern::tryFrom($this->day_pattern);

        $patternLabel = $dayPattern?->label() ?? $this->day_pattern;

        if ($this->is_closed) {
            return [
                'pattern' => $patternLabel,
                'hours' => 'Closed',
                'effective_from' => $this->effective_from?->format('M j, Y'),
                'effective_until' => $this->effective_until?->format('M j, Y'),
            ];
        }

        if ($this->is_open_24_hours) {
            return [
                'pattern' => $patternLabel,
                'hours' => 'Open 24 hours',
                'effective_from' => $this->effective_from?->format('M j, Y'),
                'effective_until' => $this->effective_until?->format('M j, Y'),
            ];
        }

        $opensAt = $this->opens_at ? Carbon::parse($this->opens_at)->format('g:i A') : null;
        $closesAt = $this->closes_at ? Carbon::parse($this->closes_at)->format('g:i A') : null;

        $hours = $opensAt;
        if ($closesAt) {
            $hours .= ' - '.$closesAt;
        }

        return [
            'pattern' => $patternLabel,
            'hours' => $hours,
            'effective_from' => $this->effective_from?->format('M j, Y'),
            'effective_until' => $this->effective_until?->format('M j, Y'),
        ];
    }
}
