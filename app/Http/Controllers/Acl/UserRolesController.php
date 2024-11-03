<?php

namespace App\Http\Controllers\Acl;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\RoleCollection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserRolesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(User $user)
    {
        $this->userBelongsToTeam($user);

        return new RoleCollection($user->roles);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    protected function userBelongsToTeam(User $user)
    {
        if (! $user->hasAuthUserTeam()) {
            throw new NotFoundHttpException;
        }
    }
}
