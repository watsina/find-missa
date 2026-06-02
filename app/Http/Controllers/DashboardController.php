<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * Uses a single subselect query wrapped in stale-while-revalidate cache
     * (fresh for 60s, stale for up to 300s) to reduce 6 queries to 1.
     */
    public function __invoke(Request $request)
    {
        $stats = Cache::flexible('dashboard-stats', [60, 300], function () {
            return (array) DB::query()
                ->selectRaw('(SELECT COUNT(*) FROM dioceses) as dioceses')
                ->selectRaw('(SELECT COUNT(*) FROM churches) as churches')
                ->selectRaw('(SELECT COUNT(*) FROM priests) as priests')
                ->selectRaw('(SELECT COUNT(*) FROM parishes) as parishes')
                ->selectRaw('(SELECT COUNT(*) FROM sub_parishes) as subParishes')
                ->selectRaw('(SELECT COUNT(*) FROM activity_times) as activities')
                ->first();
        });

        return response()->json([
            'stats' => $stats,
        ]);
    }
}
