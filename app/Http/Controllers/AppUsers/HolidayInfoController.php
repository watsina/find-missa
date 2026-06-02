<?php

namespace App\Http\Controllers\AppUsers;

use App\Models\Team;
use Illuminate\Http\Request;
use App\Services\HolidayService;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\HolidayInstanceResource;

class HolidayInfoController extends Controller
{
    public function __construct(
        private readonly HolidayService $holidayService
    ) {}

    /**
     * Get holiday information for a given date.
     * Public endpoint - no authentication required.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'date' => ['required', 'date'],
        ]);

        // No team filtering for public endpoint - only show universal holidays
        $teamId = Team::select('id')->where('country', 'Uganda')->first()?->id ?? null;
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
