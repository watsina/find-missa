<?php

namespace App\Console\Commands;

use App\Models\ActivityTime;
use Illuminate\Console\Command;

class AutoTagActivitiesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activities:auto-tag
                            {--team= : Only process activities for a specific team ID}
                            {--chunk=100 : Number of activities to process per chunk}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-assign system tags to all existing activities based on their start_time and day_of_week';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting auto-tagging of activities...');

        $query = ActivityTime::withoutGlobalScopes();

        if ($teamId = $this->option('team')) {
            $query->where('team_id', $teamId);
            $this->info("Filtering by team ID: {$teamId}");
        }

        $chunkSize = (int) $this->option('chunk');
        $totalProcessed = 0;
        $totalTagged = 0;

        $query->chunkById($chunkSize, function ($activities) use (&$totalProcessed, &$totalTagged) {
            foreach ($activities as $activity) {
                $totalProcessed++;

                if (method_exists($activity, 'autoAssignSystemTags')) {
                    $activity->autoAssignSystemTags();
                    $totalTagged++;
                }
            }

            $this->output->write('.');
        });

        $this->newLine();
        $this->info("Completed! Processed {$totalProcessed} activities, tagged {$totalTagged}.");

        return Command::SUCCESS;
    }
}
