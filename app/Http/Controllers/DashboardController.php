<?php

namespace App\Http\Controllers;

use App\Models\Church;
use App\Models\Parish;
use App\Models\Priest;
use App\Models\Diocese;
use App\Models\SubParish;
use App\Models\ActivityTime;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $dioceses = Diocese::count();
        $churches = Church::count();
        $priests = Priest::count();
        $parishes = Parish::count();
        $subParishes = SubParish::count();
        $activities = ActivityTime::count();

        return response()->json([
            'stats' => compact('dioceses', 'parishes', 'churches', 'priests', 'activities', 'subParishes'),
        ]);
    }
}
