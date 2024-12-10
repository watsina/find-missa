<?php

namespace App\Http\Controllers;

use App\Models\Church;
use App\Models\Parish;
use App\Models\Priest;
use App\Models\Diocese;
use App\Models\SubParish;
use App\Models\ActivityTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Models\ActivityTimeException;

class ClearDataController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        if (App::environment('production')) {
            abort(500);
        }

        Diocese::truncate();
        Parish::truncate();
        SubParish::truncate();
        ActivityTime::truncate();
        ActivityTimeException::truncate();
        Church::truncate();
        Priest::truncate();

        return response()->noContent();
    }
}
