<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserInvitation;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AcceptUserInvitationController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, UserInvitation $invitation)
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
