<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\TeamInvitation;
use Illuminate\Support\Facades\Auth;
use App\Providers\AppServiceProvider;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AcceptTeamInvitationController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, TeamInvitation $invitation)
    {
        if ($request->user()) {
            Auth::logout();

            $request->session()->regenerate();

            // we don't want a logged in user
            return redirect($invitation->acceptUrl());
        }

        $hasUser = User::whereEmail($invitation->email)->exists();

        if ($invitation->convertToMembership()) {
            return $hasUser ? redirect(AppServiceProvider::HOME) : redirect(route('forgot-password', [
                'email' => $invitation->email,
            ]));
        }

        throw new NotFoundHttpException;
    }
}
