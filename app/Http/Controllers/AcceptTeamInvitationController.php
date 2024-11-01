<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TeamInvitation;
use Illuminate\Support\Facades\Auth;
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

        if ($invitation->convertToMembership()) {
            return redirect(route('password.request', [
                'email' => $invitation->email,
            ]));
        }

        throw new NotFoundHttpException;
    }
}
