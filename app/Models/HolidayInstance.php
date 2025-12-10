<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * HolidayInstance represents a computed date for a holiday in a specific year.
 * This caches the calculated dates for moveable feasts and Easter-relative holidays.
 */
class HolidayInstance extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<int, string>
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
            'year' => 'integer',
            'date' => 'date',
        ];
    }

    /**
     * Get the holiday that this instance belongs to.
     */
    public function holiday(): BelongsTo
    {
        return $this->belongsTo(Holiday::class);
    }

    /**
     * Scope to filter by year.
     */
    public function scopeForYear(Builder $query, int $year): void
    {
        $query->where('year', $year);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeInDateRange(Builder $query, string $startDate, string $endDate): void
    {
        $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope to get instances for a specific date.
     */
    public function scopeOnDate(Builder $query, string $date): void
    {
        $query->whereDate('date', $date);
    }

    /**
     * Get the display name (custom or from holiday).
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->attributes['display_name'] ?? $this->holiday->name;
    }
}
