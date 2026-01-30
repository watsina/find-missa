<?php

namespace App\Models;

use App\Casts\Timecast;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LiturgicalScheduleTime extends BaseModel
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
            'liturgical_schedule_id' => 'integer',
            'start_time' => Timecast::class,
            'end_time' => Timecast::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (LiturgicalScheduleTime $model) {
            if (! $model->activity_type_description) {
                $model->activity_type_description = $model->type;
            }
        });
    }

    /**
     * Get the liturgical schedule this time belongs to.
     */
    public function liturgicalSchedule(): BelongsTo
    {
        return $this->belongsTo(LiturgicalSchedule::class);
    }

    /**
     * Get the tags for this liturgical schedule time.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(ActivityTag::class, 'liturgical_schedule_time_tag');
    }
}
