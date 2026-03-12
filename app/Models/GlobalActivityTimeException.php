<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class GlobalActivityTimeException extends BaseModel
{
    /** @use HasFactory<\Database\Factories\GlobalActivityTimeExceptionFactory> */
    use HasFactory;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];
}
