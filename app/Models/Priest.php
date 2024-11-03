<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Priest extends BaseModel
{
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

    public function parish(): BelongsTo
    {
        return $this->belongsTo(Parish::class);
    }
}
