<?php

namespace App\Models;

use App\Casts\Timecast;
use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActivityTime extends BaseModel
{
    use HasFactory;
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
            'date' => 'date:Y-m-d',
            'start_time' => Timecast::class,
            'end_time' => Timecast::class,
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
     * Scope a query to only include users of a given type.
     */
    public function scopeOfType(Builder $query, array $types): void
    {
        $query->whereIn('type', $types);
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function exceptions(): HasMany
    {
        return $this->hasMany(ActivityTimeException::class);
    }
}
