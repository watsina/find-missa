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
                            {--force : Overwrite existing new-schema data}
                            {--dry-run : Preview migration without writing changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate legacy ActivityTimes to the new ActivitySchedule schema.';

    /**
     * Execute the console command.
     */
    public function handle(ActivityTimeMigrationService $service): int
    {
        $churchId = $this->option('church');
        $force = $this->option('force');
        $dryRun = $this->option('dry-run');

        if ($force && ! $dryRun && ! $this->confirm('This will DELETE all existing data in activity_schedules, one_time_activities, and schedule_overrides for the target(s). Continue?')) {
            $this->info('Aborted.');

            return Command::FAILURE;
        }

        if ($churchId) {
            $church = Church::find($churchId);
            if (! $church) {
                $this->error("Church with ID {$churchId} not found.");

                return Command::FAILURE;
            }

            $this->info("Migrating Church ID: {$churchId}...");
            $stats = $service->migrateChurch($church, $force, $dryRun);
            $this->displayStats($stats, $dryRun, $force);
        } else {
            $this->info('Migrating ALL churches...');
            $stats = $service->migrateAll($force, $dryRun);
            $this->displayStats($stats, $dryRun, $force);
        }

        $this->info($dryRun ? 'Dry run complete. No changes were written.' : 'Migration complete.');

        return Command::SUCCESS;
    }

    protected function displayStats(array $stats, bool $dryRun, bool $force): void
    {
        if ($dryRun) {
            $this->table(
                ['Model', 'Action', 'Count'],
                [
                    ['ActivitySchedule', 'create', $stats['schedules_created']],
                    ['OneTimeActivity', 'create', $stats['one_time_created']],
                    ['ScheduleOverride', 'create', $stats['overrides_created']],
                    ['ActivityTag pivot', 'sync', $stats['tag_syncs']],
                    ['ActivitySchedule', 'delete', $force ? $stats['deleted_schedules'] : 0],
                    ['OneTimeActivity', 'delete', $force ? $stats['deleted_one_time'] : 0],
                    ['ScheduleOverride', 'delete', $force ? $stats['deleted_overrides'] : 0],
                ]
            );

            $this->line(sprintf(
                'Dry run summary: ActivitySchedule create=%d delete=%d; OneTimeActivity create=%d delete=%d; ScheduleOverride create=%d delete=%d; ActivityTag pivot sync=%d',
                $stats['schedules_created'],
                $force ? $stats['deleted_schedules'] : 0,
                $stats['one_time_created'],
                $force ? $stats['deleted_one_time'] : 0,
                $stats['overrides_created'],
                $force ? $stats['deleted_overrides'] : 0,
                $stats['tag_syncs']
            ));

            if (! empty($stats['by_church'])) {
                $this->newLine();
                $this->info('Per-church summary');

                $rows = [];
                ksort($stats['by_church']);
                foreach ($stats['by_church'] as $churchId => $churchStats) {
                    $rows[] = [
                        $churchId,
                        $churchStats['schedules_created'] ?? 0,
                        $churchStats['one_time_created'] ?? 0,
                        $churchStats['overrides_created'] ?? 0,
                        $churchStats['tag_syncs'] ?? 0,
                        $force ? ($churchStats['deleted_schedules'] ?? 0) : 0,
                        $force ? ($churchStats['deleted_one_time'] ?? 0) : 0,
                        $force ? ($churchStats['deleted_overrides'] ?? 0) : 0,
                    ];
                }

                $this->table(
                    [
                        'Church ID',
                        'Schedules Create',
                        'One-Time Create',
                        'Overrides Create',
                        'Tag Syncs',
                        'Schedules Delete',
                        'One-Time Delete',
                        'Overrides Delete',
                    ],
                    $rows
                );
            }

            $this->outputTypeBreakdown($stats['type_counts']);

            return;
        }

        $this->table(
            ['Metric', 'Count'],
            [
                ['Schedules Created', $stats['schedules_created']],
                ['One-Time Created', $stats['one_time_created']],
                ['Overrides Created', $stats['overrides_created']],
            ]
        );
    }

    protected function outputTypeBreakdown(array $typeCounts): void
    {
        $sections = [
            'ActivitySchedule' => $typeCounts['schedules'] ?? [],
            'OneTimeActivity' => $typeCounts['one_time'] ?? [],
            'ScheduleOverride' => $typeCounts['overrides'] ?? [],
        ];

        foreach ($sections as $label => $counts) {
            if (empty($counts)) {
                continue;
            }

            $this->newLine();
            $this->info("Activity type breakdown: {$label}");

            ksort($counts);
            $rows = [];
            foreach ($counts as $type => $count) {
                $rows[] = [$type, $count];
            }

            $this->table(['Type', 'Count'], $rows);
        }
    }
}
