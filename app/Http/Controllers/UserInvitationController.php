<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserInvitation;
use App\Http\Resources\UserInvitationResource;
use App\Http\Resources\UserInvitationCollection;

class UserInvitationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $invitations = UserInvitation::all();

        return new UserInvitationCollection($invitations);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users|unique:user_invitations',
        ]);

        $invitation = UserInvitation::create($data);

        return new UserInvitationResource($invitation);
    }

    /**
     * Display the specified resource.
     */
    public function show(UserInvitation $userInvitation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UserInvitation $userInvitation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserInvitation $userInvitation)
    {
        $userInvitation->delete();

        return response()->noContent();
    }
}
