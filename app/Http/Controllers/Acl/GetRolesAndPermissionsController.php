<?php

namespace App\Http\Controllers\Acl;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GetRolesAndPermissionsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        return response()->json([
            'data' => [
                'permissions' => Permission::all()->toArray(),
                'roles' => Role::all()->toArray(),
            ],
        ]);
    }
}
