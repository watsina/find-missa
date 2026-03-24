<?php

namespace App\Http\Controllers\Acl;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;

class GetRolesAndPermissionsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        Gate::authorize('roles.read');

        return response()->json([
            'data' => [
                'permissions' => Permission::all()->toArray(),
                'roles' => Role::all()->toArray(),
            ],
        ]);
    }
}
