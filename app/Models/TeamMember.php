<?php

namespace App\Models;

use App\Models\Scopes\TeamScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TeamMember extends Pivot
{
    /** @use HasFactory<\Database\Factories\TeamMemberFactory> */
    use HasFactory;

    protected $table = 'team_members';

    protected static function booted()
    {
        static::addGlobalScope(new TeamScope);

        static::deleting(function ($model) {
            // Remove only the roles belonging to this team, not all user roles
            if ($model->team && $model->user) {
                $teamRoleIds = Role::where('team_id', $model->team->id)->pluck('id');

                DB::table('user_roles')
                    ->where('user_id', $model->user->id)
                    ->whereIn('role_id', $teamRoleIds)
                    ->delete();

                $model->user->flushPermissionCache();
            }
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
