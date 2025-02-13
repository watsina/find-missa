<?php

namespace App\Models;

use Illuminate\Support\Str;
use App\Models\Concerns\HasCodeName;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Church extends BaseModel
{
    use HasCodeName;
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

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->code_name = Str::kebab($model->name.' '.$model->city);
        });
    }

    /**
     * Get the parent parishable model (parish or sub-parish).
     */
    public function parishable(): MorphTo
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
