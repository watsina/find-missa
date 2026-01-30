<?php

namespace App\Models;

use App\Casts\Timecast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ScheduleDayDetail extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The table associated with the model.
     */
    protected $table = 'schedule_day_details';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'activity_schedule_id' => 'integer',
            'start_time' => Timecast::class,
            'end_time' => Timecast::class,
            'is_skipped' => 'boolean',
        ];
    }

    /**
     * Get the activity schedule this detail belongs to.
     */
    public function activitySchedule(): BelongsTo
    {
        return $this->belongsTo(ActivitySchedule::class);
    }

    /**
     * Check if this day detail has any overrides.
     */
    public function hasOverrides(): bool
    {
        return $this->language !== null
            || $this->start_time !== null
            || $this->end_time !== null
            || $this->activity_type_description !== null
            || $this->is_skipped;
    }
}
