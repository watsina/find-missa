<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdorationChapel extends BaseModel
{
    use HasFactory;

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
        ];
    }

    /**
     * Get the church that owns this chapel.
     */
    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    /**
     * Get the schedules for this chapel.
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(AdorationChapelSchedule::class);
    }

    /**
     * Get the exceptions for this chapel.
     */
    public function exceptions(): HasMany
    {
        return $this->hasMany(AdorationChapelException::class);
    }

    /**
     * Check if this chapel is open at a given date/time.
     */
    public function isOpenAt(Carbon $dateTime): bool
    {
        // Check for exceptions first
        if ($this->hasExceptionOn($dateTime)) {
            return false;
        }

        // Find an applicable schedule for this date
        $schedule = $this->getApplicableSchedule($dateTime);

        if (! $schedule) {
            return false;
        }

        // Check if closed on this day pattern
        if ($schedule->is_closed) {
            return false;
        }

        // Get the resolved hours for this specific day (with any day-specific overrides)
        $hours = $schedule->getResolvedHoursForDay($dateTime->dayName);

        // If this specific day is closed, return false
        if ($hours['is_closed']) {
            return false;
        }

        // If open 24 hours, it's open
        if ($hours['is_open_24_hours']) {
            return true;
        }

        // Check if the time falls within opening hours
        return $this->isTimeWithinHours($dateTime, $hours['opens_at'], $hours['closes_at']);
    }

    /**
     * Check if there's an exception on the given date.
     */
    public function hasExceptionOn(Carbon $date): bool
    {
        return $this->exceptions()
            ->where('start_date', '<=', $date->toDateString())
            ->where('end_date', '>=', $date->toDateString())
            ->exists();
    }

    /**
     * Get the applicable schedule for a given date.
     */
    public function getApplicableSchedule(Carbon $date): ?AdorationChapelSchedule
    {
        return $this->schedules()
            ->effectiveOn($date)
            ->matchesDayPattern($date)
            ->orderByRaw('
                CASE
                    WHEN effective_from IS NOT NULL AND effective_until IS NOT NULL THEN 1
                    WHEN effective_from IS NOT NULL OR effective_until IS NOT NULL THEN 2
                    ELSE 3
                END
            ')
            ->first();
    }

    /**
     * Get the opening hours for a given date.
     *
     * @return array{opens_at: string|null, closes_at: string|null, is_open_24_hours: bool, is_closed: bool}|null
     */
    public function getHoursForDate(Carbon $date): ?array
    {
        if ($this->hasExceptionOn($date)) {
            return ['is_closed' => true, 'opens_at' => null, 'closes_at' => null, 'is_open_24_hours' => false];
        }

        $schedule = $this->getApplicableSchedule($date);

        if (! $schedule) {
            return null;
        }

        if ($schedule->is_closed) {
            return ['is_closed' => true, 'opens_at' => null, 'closes_at' => null, 'is_open_24_hours' => false];
        }

        return $schedule->getResolvedHoursForDay($date->dayName);
    }

    /**
     * Check if a time is within the given hours.
     */
    protected function isTimeWithinHours(Carbon $dateTime, ?string $opensAt, ?string $closesAt): bool
    {
        if ($opensAt === null) {
            return false;
        }

        $time = $dateTime->format('H:i');

        // If no closes_at, assume closes at midnight (end of day)
        if ($closesAt === null) {
            $closesAt = '23:59';
        }

        // Handle overnight hours (e.g., opens 22:00, closes 06:00)
        if ($closesAt < $opensAt) {
            // Time is within if it's after opening OR before closing
            return $time >= $opensAt || $time < $closesAt;
        }

        // Normal hours (opens and closes same day)
        return $time >= $opensAt && $time < $closesAt;
    }

    /**
     * Get a formatted string of the chapel's hours for display.
     */
    public function getFormattedHours(): array
    {
        $baseSchedules = $this->schedules()
            ->whereNull('effective_from')
            ->whereNull('effective_until')
            ->with('dayDetails')
            ->get();

        $hours = [];

        foreach ($baseSchedules as $schedule) {
            $hours[] = $schedule->getFormattedSchedule();
        }

        return $hours;
    }
}
