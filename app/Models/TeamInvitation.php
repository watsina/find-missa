<?php

namespace App\Models;

use App\Models\Scopes\TeamScope;
use App\Mail\TeamInvitationEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Eloquent\Model;
use Database\Factories\TeamInvitationFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TeamInvitation extends Model
{
    /** @use HasFactory<TeamInvitationFactory> */
    use HasFactory;

    protected $guarded = [];

    public static function booted()
    {
        static::addGlobalScope(new TeamScope);

        static::created(fn ($invitation) => Mail::to($invitation->email)->send(new TeamInvitationEmail($invitation)));
    }

    public function acceptUrl()
    {
        return url()->signedRoute('team-invitations.accept', [
            'invitation' => $this,
        ]);
    }

    public function convertToMembership()
    {
        if (! $this->team || ! $this->role) {
            throw new \RuntimeException('Invalid invitation: missing team or role');
        }

        $user = User::firstOrCreate(
            ['email' => $this->email],
            ['name' => $this->name],
        );

        $this->team->members()->attach($user);

        $user->roles()->attach($this->role, ['team_id' => $this->team->id]);

        $this->delete();

        return true;
    }

    /**
     * Get the team that the invitation belongs to.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the role that the invitation invited user was given
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
