<?php

namespace App\Models;

use App\Casts\Timecast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdorationChapelScheduleDetail extends Model
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
            'adoration_chapel_schedule_id' => 'integer',
            'opens_at' => Timecast::class,
            'closes_at' => Timecast::class,
            'is_open_24_hours' => 'boolean',
            'is_closed' => 'boolean',
        ];
    }

    /**
     * Get the schedule this detail belongs to.
     */
    public function adorationChapelSchedule(): BelongsTo
    {
        return $this->belongsTo(AdorationChapelSchedule::class);
    }

    /**
     * Check if this day detail has any overrides.
     */
    public function hasOverrides(): bool
    {
        return $this->opens_at !== null
            || $this->closes_at !== null
            || $this->is_open_24_hours
            || $this->is_closed;
    }
}
