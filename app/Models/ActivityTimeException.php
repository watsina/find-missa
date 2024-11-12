<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActivityTimeException extends BaseModel
{
    use HasFactory;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'activity_time_id' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function activityTime(): BelongsTo
    {
        return $this->belongsTo(ActivityTime::class);
    }
}
