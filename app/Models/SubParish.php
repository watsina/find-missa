<?php

namespace App\Models;

use Illuminate\Support\Str;
use App\Models\Concerns\HasCodeName;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SubParish extends BaseModel
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
        'parish_id' => 'integer',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->code_name = Str::kebab($model->name.' '.$model->city);
        });
    }

    public function parish(): BelongsTo
    {
        return $this->belongsTo(Parish::class);
    }

    public function churches(): MorphMany
    {
        return $this->morphMany(Church::class, 'parishable');
    }
}
