<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Actions\ClearTeamData;
use Illuminate\Support\Facades\App;

class ClearDataController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, ClearTeamData $action)
    {
        // Only allow in local environment
        if (! App::environment('local')) {
            abort(403, 'This action is only allowed in local environment');
        }

        // Only super admins can clear data
        if (! $request->user()?->is_super_admin) {
            abort(403, 'Only super administrators can clear data');
        }

        $action->execute();

        return response()->noContent();
    }
}
