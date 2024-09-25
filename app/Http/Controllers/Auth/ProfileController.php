<?php

namespace App\Http\Controllers\Auth;

use Laravel\Fortify\Features;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
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
            'email' => $data['email'],
        ]);

        if ($request->user()->isDirty('email') && $request->user() instanceof MustVerifyEmail && Features::enabled(Features::emailVerification())) {
            $request->user()->email_verified_at = null;
            $request->user()->sendEmailVerificationNotification();
        }

        $request->user()->save();

        return new UserResource($request->user());
    }
}
