<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\RecordsUserActions;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Login extends Model
{
    /** @use HasFactory<\Database\Factories\LoginFactory> */
    use HasFactory;

    use RecordsUserActions;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
}
