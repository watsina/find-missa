<?php

namespace App\Http\Controllers;

use App\Models\Church;
use App\Models\Parish;
use App\Models\Priest;
use App\Models\Diocese;
use App\Models\SubParish;
use App\Models\ActivityTime;
use App\Models\ScheduleRule;
use Illuminate\Http\Request;
use App\Models\OneTimeActivity;
use App\Models\ActivitySchedule;
use App\Models\ScheduleOverride;
use App\Models\LiturgicalSchedule;
use Illuminate\Support\Facades\DB;
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

        // Disable foreign key checks to allow truncation
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            // Truncate pivot tables first
            DB::table('activity_time_tag')->truncate();
            DB::table('activity_schedule_tag')->truncate();
            DB::table('one_time_activity_tag')->truncate();
            DB::table('liturgical_schedule_times')->truncate();
            DB::table('schedule_day_details')->truncate();

            // V2 Schedule tables (children of churches)
            ActivitySchedule::truncate();
            OneTimeActivity::truncate();
            ScheduleOverride::truncate();
            ScheduleRule::truncate();
            LiturgicalSchedule::truncate();

            // Legacy tables
            ActivityTimeException::truncate();
            ActivityTime::truncate();

            // Core tables (churches depend on parishes/subparishes)
            Church::truncate();
            SubParish::truncate();
            Parish::truncate();
            Diocese::truncate();
            Priest::truncate();
        } finally {
            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        return response()->noContent();
    }
}
