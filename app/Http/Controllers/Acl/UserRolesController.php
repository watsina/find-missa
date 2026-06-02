<?php

namespace App\Http\Controllers\Acl;

use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Resources\RoleCollection;
use App\Http\Requests\StoreUserRolesRequest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserRolesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(User $user)
    {
        $this->userBelongsToTeam($user);

        $roles = $user->roles()->get();

        return new RoleCollection($roles);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRolesRequest $request, User $user)
    {
        $this->userBelongsToTeam($user);

        $user->syncRoles($request->validated('role_ids'));

        return new RoleCollection($user->roles);
    }

    protected function userBelongsToTeam(User $user)
    {
        if (! $user->hasAuthUserTeam()) {
            throw new NotFoundHttpException;
        }
    }
}
