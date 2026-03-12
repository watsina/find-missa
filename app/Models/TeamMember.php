<?php

namespace App\Models;

use App\Models\Scopes\TeamScope;
use Database\Factories\TeamMemberFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TeamMember extends Pivot
{
    /** @use HasFactory<TeamMemberFactory> */
    use HasFactory;

    protected $table = 'team_members';

    protected static function booted()
    {
        static::addGlobalScope(new TeamScope);

        static::deleting(function ($model) {
            // remove the assigned roles on that team
            if ($model->team) {
                setPermissionsTeamId($model->team->id);
            }

            $model->user?->syncRoles([]);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
