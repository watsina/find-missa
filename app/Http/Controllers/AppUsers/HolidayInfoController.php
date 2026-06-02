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
        $data = $request->validate([
            'date' => ['required', 'date'],
        ]);

        $date = $data['date'];

        $results = cache()->remember("holidays-info:{$date}", 12 * 60, function () use ($date) {

            $teamId = Team::select('id')->where('country', 'Uganda')->first()?->id ?? null;
            $isHoliday = $this->holidayService->isHoliday($date, $teamId);
            $isHolyDayOfObligation = $this->holidayService->isHolyDayOfObligation($date, $teamId);
            $holidays = $this->holidayService->getHolidaysOnDate($date, $teamId);

            return [
                'date' => $date,
                'is_holiday' => $isHoliday,
                'is_holy_day_of_obligation' => $isHolyDayOfObligation,
                'holidays' => HolidayInstanceResource::collection($holidays->load('holiday')),
            ];
        });

        return response()->json($results);
    }
}
