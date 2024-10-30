<?php

namespace App\Models;

use App\Mail\UserInvitationEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserInvitation extends Model
{
    /** @use HasFactory<\Database\Factories\UserInvitationFactory> */
    use HasFactory;

    public static function booted()
    {
        static::created(fn ($invitation) => Mail::to($invitation->email)->send(new UserInvitationEmail($invitation)));
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
