<?php

namespace App\Models;

use App\Mail\TeamInvitationEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TeamInvitation extends Model
{
    /** @use HasFactory<\Database\Factories\TeamInvitationFactory> */
    use HasFactory;

    public static function booted()
    {
        static::created(fn ($invitation) => Mail::to($invitation->email)->send(new TeamInvitationEmail($invitation)));
    }

    public function acceptUrl()
    {
        return url()->signedRoute('user.accept-invitation', [
            'invitation' => $this,
        ]);
    }

    public function convertToMembership()
    {
        User::create($this->only(['name', 'email']));

        $this->delete();

        return true;
    }
}
