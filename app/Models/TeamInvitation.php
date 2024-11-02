<?php

namespace App\Models;

use App\Mail\TeamInvitationEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TeamInvitation extends Model
{
    /** @use HasFactory<\Database\Factories\TeamInvitationFactory> */
    use HasFactory;

    protected $guarded = [];

    public static function booted()
    {
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
        $user = User::firstOrCreate(
            ['email' => $this->email],
            ['name' => $this->name],
        );

        $this->team->members()->attach($user);

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
}
