<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use App\Enums\HolidayType;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\HolidayService;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\HolidayResource;
use App\Http\Resources\HolidayInstanceResource;
use App\Http\Requests\Holiday\StoreHolidayRequest;
use App\Http\Requests\Holiday\UpdateHolidayRequest;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class HolidayController extends Controller
{
    public function __construct(
        private readonly HolidayService $holidayService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Holiday::class);

        $items = abs(intval($request->itemsPerPage)) ?? 20;
        $itemsPerPage = $items > 100 ? 100 : $items;
        $teamId = $request->user()->currentTeam?->id;

        $holidays = Holiday::search($request->search)
            ->query(function ($query) use ($request, $teamId) {
                $query->availableForTeam($teamId);

                // Filter by type
                if ($request->has('type')) {
                    $type = HolidayType::tryFrom($request->type);
                    if ($type) {
                        $query->ofType($type);
                    }
                }

                // Filter by holy days of obligation
                if ($request->has('is_holy_day_of_obligation')) {
                    if ($request->boolean('is_holy_day_of_obligation')) {
                        $query->holyDaysOfObligation();
                    }
                }

                // Filter by universal/team-specific
                if ($request->has('is_universal')) {
                    $request->boolean('is_universal')
                        ? $query->universal()
                        : $query->teamSpecific();
                }

                // Filter by moveable feasts
                if ($request->has('is_moveable')) {
                    if ($request->boolean('is_moveable')) {
                        $query->moveable();
                    }
                }

                $query->orderBy('name');
            })
            ->paginate($perPage = $itemsPerPage, $pageName = 'page', $page = $request->page ?? 1);

        return HolidayResource::collection($holidays);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreHolidayRequest $request): HolidayResource
    {
        Gate::authorize('create', Holiday::class);

        $data = $request->validated();

        // Team-specific holidays get the current team's ID
        if (! ($data['is_universal'] ?? true)) {
            $data['team_id'] = $request->user()->currentTeam?->id;
        }

        $holiday = Holiday::create($data);

        return new HolidayResource($holiday);
    }

    /**
     * Display the specified resource.
     */
    public function show(Holiday $holiday): HolidayResource
    {
        Gate::authorize('view', $holiday);

        return new HolidayResource($holiday);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateHolidayRequest $request, Holiday $holiday): HolidayResource
    {
        Gate::authorize('update', $holiday);

        $holiday->update($request->validated());

        // Clear cached instances since the definition changed
        $this->holidayService->clearInstancesForHoliday($holiday);

        return new HolidayResource($holiday);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Holiday $holiday): Response
    {
        Gate::authorize('delete', $holiday);

        // Instances are cascaded via foreign key
        $holiday->delete();

        return response()->noContent();
    }

    /**
     * Get holiday instances for a specific year.
     */
    public function instances(Request $request, int $year): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Holiday::class);

        $teamId = $request->user()->currentTeam?->id;
        $instances = $this->holidayService->computeInstancesForYear($year, $teamId);

        // Load the holiday relationship for each instance
        $instances->each(fn ($instance) => $instance->load('holiday'));

        return HolidayInstanceResource::collection($instances);
    }

    /**
     * Get holidays for a date range.
     */
    public function inRange(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Holiday::class);

        $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $teamId = $request->user()->currentTeam?->id;
        $instances = $this->holidayService->getInstancesInRange(
            $request->start_date,
            $request->end_date,
            $teamId
        );

        return HolidayInstanceResource::collection($instances);
    }

    /**
     * Check if a date is a holiday.
     */
    public function checkDate(Request $request): \Illuminate\Http\JsonResponse
    {
        Gate::authorize('viewAny', Holiday::class);

        $request->validate([
            'date' => ['required', 'date'],
        ]);

        $teamId = $request->user()->currentTeam?->id;
        $isHoliday = $this->holidayService->isHoliday($request->date, $teamId);
        $isHolyDayOfObligation = $this->holidayService->isHolyDayOfObligation($request->date, $teamId);
        $holidays = $this->holidayService->getHolidaysOnDate($request->date, $teamId);

        return response()->json([
            'date' => $request->date,
            'is_holiday' => $isHoliday,
            'is_holy_day_of_obligation' => $isHolyDayOfObligation,
            'holidays' => HolidayInstanceResource::collection($holidays->load('holiday')),
        ]);
    }
}
