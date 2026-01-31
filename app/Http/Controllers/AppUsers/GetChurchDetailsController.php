<?php

namespace App\Http\Controllers\AppUsers;

use App\Models\Church;
use Illuminate\Http\Request;
use App\Models\SchedulePeriod;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use App\Http\Resources\ChurchDetailsResource;

class GetChurchDetailsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Church $church)
    {
        $church->loadMissing([
            'activitySchedules.schedulePeriod',
            'activitySchedules.dayDetails',
            'activitySchedules.tags',
            'oneTimeActivities.tags',
            'liturgicalSchedules.holiday',
            'liturgicalSchedules.times',
            'parishable',
        ]);

        // Get the church's team_id for fetching relevant schedule periods
        $teamId = $church->team_id;

        // Get current and upcoming schedule periods
        $today = Carbon::today();
        $schedulePeriods = SchedulePeriod::query()
            ->availableForTeam($teamId)
            ->active()
            ->where(function ($query) use ($today) {
                // Current periods
                $query->current($today->toDateString())
                    // Or upcoming in next 60 days
                    ->orWhere(function ($q) use ($today) {
                        $q->where('start_date', '>', $today)
                            ->where('start_date', '<=', $today->copy()->addDays(60));
                    });
            })
            ->orderBy('start_date')
            ->get();

        return new ChurchDetailsResource($church, $schedulePeriods);
    }
}
