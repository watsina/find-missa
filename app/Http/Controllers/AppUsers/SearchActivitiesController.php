<?php

namespace App\Http\Controllers\AppUsers;

use PostHog\PostHog;
use App\Models\ActivityTime;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use App\Models\GlobalActivityTimeException;
use App\Http\Resources\ActivityTimeDetailsResource;

class SearchActivitiesController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        if (config('services.posthog.enabled') && config('services.posthog.key')) {
            PostHog::capture([
                'distinctId' => $request->user()?->email ?? 'guest-user:'.sha1(session()->getId()),
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
            'district' => 'nullable|string',
        ]);

        $date = Carbon::createFromDate($data['date']);
        $dayHasGlobalException = GlobalActivityTimeException::whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->exists();

        if ($data['type'] === 'mass') {
            $types = ['mass', 'anticipation-mass'];
        } else {
            $types = [$data['type']];
        }

        $activities = ActivityTime::ofType($types)
            ->with('church')
            ->effectiveOn($date)
            ->whereDoesntHave('exceptions', function (Builder $query) use ($data) {
                $query->whereDate('start_date', '<=', $data['date'])
                    ->whereDate('end_date', '>=', $data['date']);
            })
            ->when(
                isset($data['church_name']),
                fn (Builder $query) => $query->whereHas(
                    'church',
                    fn (Builder $query) => $query->where('name', 'like', "%{$data['church_name']}%")
                )
            )
            ->when(
                isset($data['district']),
                fn (Builder $query) => $query->whereHas(
                    'church',
                    fn (Builder $query) => $query
                        ->where('district', 'like', "%{$data['district']}%")
                        ->orWhere('city', 'like', "%{$data['district']}%")
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
                )->when(
                    ! $dayHasGlobalException,
                    fn (Builder $query) => $query->orWhere('day_of_week', $date->dayName)
                        ->orWhere('occurrence', 'daily')
                )

            )
            ->orderBy('start_time', 'asc')
            ->paginate(50)
            ->withQueryString();

        return ActivityTimeDetailsResource::collection($activities);
    }
}
