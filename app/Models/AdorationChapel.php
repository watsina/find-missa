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
        // Check for applicable exception first (overrides regular schedule)
        $exception = $this->getApplicableException($dateTime);

        if ($exception) {
            $hours = $exception->getResolvedHours();

            if ($hours['is_closed']) {
                return false;
            }

            if ($hours['is_open_24_hours']) {
                return true;
            }

            return $this->isTimeWithinHours($dateTime, $hours['opens_at'], $hours['closes_at']);
        }

        // Fall back to regular schedule
        $schedule = $this->getApplicableSchedule($dateTime);

        if (! $schedule) {
            return false;
        }

        if ($schedule->is_closed) {
            return false;
        }

        $hours = $schedule->getResolvedHoursForDay($dateTime->dayName);

        if ($hours['is_closed']) {
            return false;
        }

        if ($hours['is_open_24_hours']) {
            return true;
        }

        return $this->isTimeWithinHours($dateTime, $hours['opens_at'], $hours['closes_at']);
    }

    /**
     * Get the applicable exception for a given date (within date range AND matches day pattern).
     */
    public function getApplicableException(Carbon $date): ?AdorationChapelException
    {
        return $this->exceptions()
            ->where('start_date', '<=', $date->toDateString())
            ->where('end_date', '>=', $date->toDateString())
            ->get()
            ->first(fn (AdorationChapelException $exception) => $exception->occursOn($date));
    }

    /**
     * Get the applicable schedule for a given date.
     */
    public function getApplicableSchedule(Carbon $date): ?AdorationChapelSchedule
    {
        return $this->schedules()
            ->matchesDayPattern($date)
            ->first();
    }

    /**
     * Get the opening hours for a given date.
     *
     * @return array{opens_at: string|null, closes_at: string|null, is_open_24_hours: bool, is_closed: bool}|null
     */
    public function getHoursForDate(Carbon $date): ?array
    {
        // Check for applicable exception first (overrides regular schedule)
        $exception = $this->getApplicableException($date);

        if ($exception) {
            return $exception->getResolvedHours();
        }

        // Fall back to regular schedule
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
        $schedules = $this->schedules()
            ->with('dayDetails')
            ->get();

        $hours = [];

        foreach ($schedules as $schedule) {
            $hours[] = $schedule->getFormattedSchedule();
        }

        return $hours;
    }
}
