<?php

namespace App\Http\Controllers\AppUsers;

use PostHog\PostHog;
use App\Models\ActivityTime;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Resources\ActivityTimeDetailsResource;

class SearchActivitiesController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        if (App::environment('staging', 'production') && config('services.posthog.key')) {
            PostHog::capture([
                'distinctId' => $request->user()?->email ?? 'guest-user',
                'event' => 'home-page-search',
                'properties' => [
                    'ip' => $request->getClientIp(),
                    'request_body' => $request->all(),
                ],
                '$groups' => [
                    'environment' => config('app.env'),
                ],
            ]);
        }

        $data = $request->validate([
            'type' => 'required|string|in:mass,confession,adoration',
            'date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'church_name' => 'nullable|string',
            'district_name' => 'nullable|string',
        ]);

        $date = Carbon::createFromDate($data['date']);

        if ($data['type'] === 'mass') {
            $types = ['mass', 'anticipation-mass'];
        } else {
            $types = [$data['type']];
        }

        $activities = ActivityTime::ofType($types)
            ->with('church')
            ->when(
                isset($data['church_name']),
                fn (Builder $query) => $query->whereHas(
                    'church',
                    fn (Builder $query) => $query->where('name', 'like', "%{$data['church_name']}%")
                )
            )
            ->when(
                isset($data['district_name']),
                fn (Builder $query) => $query->whereHas(
                    'church',
                    fn (Builder $query) => $query->where('district', 'like', "%{$data['district_name']}%")
                )
            )
            ->when(
                isset($data['start_time']),
                fn (Builder $query) => $query->where('start_time', '>=', $data['start_time'])
            )
            ->where(
                fn (Builder $query) => $query->where(
                    fn (Builder $query) => $query->where('occurrence', 'one-time')
                        ->whereDate('date', $date)
                )
                    ->orWhere('day_of_week', $date->dayName)
                    ->orWhere('occurrence', 'daily')
            )
            ->orderBy('start_time', 'asc')
            ->paginate(50);

        return ActivityTimeDetailsResource::collection($activities);
    }
}
