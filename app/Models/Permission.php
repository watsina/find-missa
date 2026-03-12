<?php

namespace App\Models;

use Database\Factories\AbilityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    /** @use HasFactory<AbilityFactory> */
    use HasFactory;
}
