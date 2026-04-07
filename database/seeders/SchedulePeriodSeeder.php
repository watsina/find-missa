<?php

namespace Database\Seeders;

use App\Models\SchedulePeriod;
use Illuminate\Database\Seeder;
use App\Services\SchedulePeriodService;

class SchedulePeriodSeeder extends Seeder
{
    public function __construct(protected SchedulePeriodService $schedulePeriodService) {}

    /**
     * Run the database seeds.
     *
     * Seeds liturgical schedule periods for the current and next liturgical year.
     */
    public function run(): void
    {
        $currentYear = (int) now()->year;

        $this->seedPeriodsForYear($currentYear);
        $this->seedPeriodsForYear($currentYear + 1);
    }

    /**
     * Seed all liturgical schedule periods for a given year.
     */
    public function seedPeriodsForYear(int $year): void
    {
        $periods = $this->schedulePeriodService->getLiturgicalPeriodsForYear($year);

        foreach ($periods as $period) {
            SchedulePeriod::updateOrCreate(
                ['slug' => $period['slug']],
                $period
            );
        }
    }
}
