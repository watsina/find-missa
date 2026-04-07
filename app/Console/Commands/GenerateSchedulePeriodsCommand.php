<?php

namespace App\Console\Commands;

use App\Models\SchedulePeriod;
use Illuminate\Console\Command;
use App\Services\SchedulePeriodService;

class GenerateSchedulePeriodsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule-periods:generate
                            {--year= : Generate periods for a specific year (defaults to current and next year)}
                            {--force : Overwrite existing periods for the specified year(s)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate liturgical schedule periods for upcoming years';

    public function __construct(protected SchedulePeriodService $schedulePeriodService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $specificYear = $this->option('year');
        $force = $this->option('force');

        if ($specificYear) {
            $years = [(int) $specificYear];
            $this->info("Generating schedule periods for year: {$specificYear}");
        } else {
            $currentYear = (int) now()->year;
            $years = [$currentYear, $currentYear + 1];
            $this->info('Generating schedule periods for current and next year: '.implode(', ', $years));
        }

        $createdCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;

        foreach ($years as $year) {
            $this->info("Processing year {$year}...");

            $periods = $this->schedulePeriodService->getLiturgicalPeriodsForYear($year);

            foreach ($periods as $period) {
                $existing = SchedulePeriod::where('slug', $period['slug'])->first();

                if ($existing && ! $force) {
                    $skippedCount++;
                    $this->line("  - Skipped: {$period['name']} (already exists)");

                    continue;
                }

                if ($existing && $force) {
                    $existing->update($period);
                    $updatedCount++;
                    $this->line("  - Updated: {$period['name']}");
                } else {
                    SchedulePeriod::create($period);
                    $createdCount++;
                    $this->line("  + Created: {$period['name']}");
                }
            }
        }

        $this->newLine();
        $this->info("Summary: {$createdCount} created, {$updatedCount} updated, {$skippedCount} skipped.");

        return Command::SUCCESS;
    }
}
