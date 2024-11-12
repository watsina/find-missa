<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Church extends BaseModel
{
    use HasFactory;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'priest_id' => 'integer',
        'latitude' => 'decimal:4',
        'longitude' => 'decimal:4',
    ];

    /**
     * Get the parent parishable model (parish or sub-parish).
     */
    public function parishable(): BelongsTo
    {
        return $this->morphTo();
    }

    public function priest(): BelongsTo
    {
        return $this->belongsTo(Priest::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ActivityTime::class);
    }
}
