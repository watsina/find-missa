<?php

namespace App\Http\Controllers\AppUsers;

use PostHog\PostHog;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use App\Services\ActivityScheduleService;
use App\Http\Resources\ComputedActivityResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Search activities using the new Activity Schedules v2 system.
 *
 * This controller uses the ActivityScheduleService which handles:
 * - Regular activity schedules with day variations
 * - Liturgical schedules for holidays
 * - One-time activities
 * - Schedule rules and overrides
 */
class SearchActivitiesV2Controller extends Controller
{
    public function __construct(
        protected ActivityScheduleService $activityScheduleService,
    ) {}

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $data = $request->validate([
            'type' => 'required|string|in:mass,confession,adoration',
            'date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'church_name' => 'nullable|string',
            'district' => 'nullable|string',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $this->trackSearchEvent($request, $data);

        $date = Carbon::createFromDate($data['date']);

        // Map type to activity types (mass includes anticipation-mass)
        if ($data['type'] === 'mass') {
            $activityTypes = ['mass', 'anticipation-mass'];
        } else {
            $activityTypes = [$data['type']];
        }

        $activities = $this->activityScheduleService->searchActivities(
            date: $date,
            activityTypes: $activityTypes,
            churchName: $data['church_name'] ?? null,
            district: $data['district'] ?? null,
            startTime: $data['start_time'] ?? null,
            limit: $data['limit'] ?? 50,
        );

        return ComputedActivityResource::collection($activities);
    }

    /**
     * Track the search event in PostHog.
     *
     * @param  array<string, mixed>  $data
     */
    protected function trackSearchEvent(Request $request, array $data): void
    {
        if (config('services.posthog.enabled') && config('services.posthog.key')) {
            PostHog::capture([
                'distinctId' => $request->user()?->email ?? 'guest-user:'.sha1(session()->getId()),
                'event' => 'home-page-search-v2',
                'properties' => [
                    'ip' => $request->getClientIp(),
                    'request_body' => $data,
                ],
                '$groups' => [
                    'environment' => config('app.env'),
                ],
            ]);
        }
    }
}
