<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class ProfileController extends Controller
{
    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): UserResource
    {
        $data = $request->validated();

        if (isset($data['photo'])) {
            $request->user()->updateprofilephoto($data['photo']);
        }

        $request->user()->fill([
            'name' => $data['name'],
            // 'email' => $data['email'],
        ]);

        // if ($request->user()->isDirty('email') && $request->user() instanceof MustVerifyEmail) {
        //     $request->user()->email_verified_at = null;
        //     $request->user()->sendEmailVerificationNotification();
        // }

        $request->user()->save();

        return new UserResource($request->user());
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): Response
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->noContent();
    }
}
