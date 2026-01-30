<?php

namespace App\Console\Commands;

use App\Models\Church;
use Illuminate\Console\Command;
use App\Services\ActivityTimeMigrationService;

class MigrateActivityTimesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activity-times:migrate 
                            {--church= : Optional Church ID to migrate specific church} 
                            {--force : Overwrite existing new-schema data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate legacy ActivityTimes to the new ActivitySchedule schema.';

    /**
     * Execute the console command.
     */
    public function handle(ActivityTimeMigrationService $service)
    {
        $churchId = $this->option('church');
        $force = $this->option('force');

        if ($force && ! $this->confirm('This will DELETE all existing data in activity_schedules, one_time_activities, and schedule_overrides for the target(s). Continue?')) {
            $this->info('Aborted.');

            return;
        }

        if ($churchId) {
            $church = Church::find($churchId);
            if (! $church) {
                $this->error("Church with ID {$churchId} not found.");

                return;
            }

            $this->info("Migrating Church ID: {$churchId}...");
            $stats = $service->migrateChurch($church, $force);
            $this->displayStats($stats);
        } else {
            $this->info('Migrating ALL churches...');
            $stats = $service->migrateAll($force);
            $this->displayStats($stats);
        }

        $this->info('Migration complete.');
    }

    protected function displayStats(array $stats): void
    {
        $this->table(
            ['Metric', 'Count'],
            [
                ['Schedules Created', $stats['schedules_created']],
                ['One-Time Created', $stats['one_time_created']],
                ['Overrides Created', $stats['overrides_created']],
            ]
        );
    }
}
