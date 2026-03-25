<?php

namespace App\Models;

use App\Casts\Timecast;
use App\Enums\DayPattern;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdorationChapelException extends Model
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
            'start_date' => 'date:Y-m-d',
            'end_date' => 'date:Y-m-d',
            'day_pattern' => DayPattern::class,
            'days_of_week' => 'array',
            'opens_at' => Timecast::class,
            'closes_at' => Timecast::class,
            'is_open_24_hours' => 'boolean',
            'is_closed' => 'boolean',
        ];
    }

    /**
     * Get the chapel that owns this exception.
     */
    public function adorationChapel(): BelongsTo
    {
        return $this->belongsTo(AdorationChapel::class);
    }

    /**
     * Check if this exception is active on the given date (within date range AND matches day pattern).
     */
    public function appliesToDate(Carbon $date): bool
    {
        // Check date range
        if ($this->start_date->gt($date) || $this->end_date->lt($date)) {
            return false;
        }

        // Check day pattern
        return $this->occursOn($date);
    }

    /**
     * Check if this exception's day pattern matches the given date.
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
     * Get the resolved hours for this exception.
     *
     * @return array{opens_at: string|null, closes_at: string|null, is_open_24_hours: bool, is_closed: bool}
     */
    public function getResolvedHours(): array
    {
        return [
            'opens_at' => $this->opens_at,
            'closes_at' => $this->closes_at,
            'is_open_24_hours' => $this->is_open_24_hours,
            'is_closed' => $this->is_closed,
        ];
    }

    /**
     * Get a formatted representation of this exception for display.
     */
    public function getFormattedSchedule(): array
    {
        $dayPattern = $this->day_pattern instanceof DayPattern
            ? $this->day_pattern
            : DayPattern::tryFrom($this->day_pattern);

        $patternLabel = $dayPattern?->label() ?? $this->day_pattern;

        if ($this->is_closed) {
            $hours = 'Closed';
        } elseif ($this->is_open_24_hours) {
            $hours = 'Open 24 hours';
        } else {
            $opensAt = $this->opens_at ? Carbon::parse($this->opens_at)->format('g:i A') : null;
            $closesAt = $this->closes_at ? Carbon::parse($this->closes_at)->format('g:i A') : null;
            $hours = $opensAt;
            if ($closesAt) {
                $hours .= ' - '.$closesAt;
            }
        }

        return [
            'pattern' => $patternLabel,
            'hours' => $hours,
            'start_date' => $this->start_date?->format('M j, Y'),
            'end_date' => $this->end_date?->format('M j, Y'),
            'reason' => $this->reason,
        ];
    }
}
