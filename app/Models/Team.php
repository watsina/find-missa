<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Team extends Model
{
    /** @use HasFactory<\Database\Factories\TeamFactory> */
    use HasFactory;

    protected $guarded = [];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            //
        ];
    }

    /**
     * Get all of the team's members including its owner.
     *
     * @return \Illuminate\Support\Collection
     */
    public function allMembers()
    {
        return $this->members;
    }

    /**
     * Get all of the members that belong to the team.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function members()
    {
        return $this->belongsToMany(User::class, TeamMember::class)
            ->withTimestamps()
            ->as('membership');
    }

    /**
     * Determine if the given member belongs to the team.
     *
     * @param  \App\Models\User  $member
     * @return bool
     */
    public function hasMember($member)
    {
        return $this->members->contains($member);
    }

    /**
     * Determine if the given email address belongs to a member on the team.
     *
     * @return bool
     */
    public function hasMemberWithEmail(string $email)
    {
        return $this->allMembers()->contains(function ($member) use ($email) {
            return $member->email === $email;
        });
    }

    /**
     * Determine if the given member has the given permission on the team.
     *
     * @param  \App\Models\User  $member
     * @param  string  $permission
     * @return bool
     */
    public function memberHasPermission($member, $permission)
    {
        return $member->hasTeamPermission($this, $permission);
    }

    /**
     * Get all of the pending member invitations for the team.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function invitations()
    {
        return $this->hasMany(TeamInvitation::class);
    }

    /**
     * Remove the given member from the team.
     *
     * @param  \App\Models\User  $member
     * @return void
     */
    public function removeMember($member)
    {
        if ($member->current_team_id === $this->id) {
            $member->forceFill([
                'current_team_id' => null,
            ])->save();
        }

        $this->members()->detach($member);
    }

    /**
     * Purge all of the team's resources.
     *
     * @return void
     */
    public function purge()
    {
        $this->members()->where('current_team_id', $this->id)
            ->update(['current_team_id' => null]);

        $this->members()->detach();

        $this->delete();
    }
}
