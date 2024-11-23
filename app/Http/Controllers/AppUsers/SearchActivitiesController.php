<?php

namespace App\Http\Controllers\AppUsers;

use App\Models\ActivityTime;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Resources\ActivityTimeCollection;

class SearchActivitiesController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|string|in:mass,confession,adoration',
            'date' => 'required|date',
        ]);

        $date = Carbon::createFromDate($data['date']);

        $activities = ActivityTime::ofType($data['type'])
            ->where(
                fn (Builder $query) => $query->where([
                    ['occurrence', '=', 'one-time'],
                    ['date', '=', $date],
                ])
                    ->orWhere('day_of_week', $date->dayName)
                    ->orWhere('occurrence', 'daily')
            )->get();

        return new ActivityTimeCollection($activities);
    }
}
