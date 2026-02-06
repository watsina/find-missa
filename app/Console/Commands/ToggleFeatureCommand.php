<?php

namespace App\Console\Commands;

use App\Models\Team;
use Laravel\Pennant\Feature;
use Illuminate\Console\Command;

class ToggleFeatureCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'feature:toggle
                            {feature : The feature class name (e.g., DataImportExport)}
                            {--team= : The team ID to toggle the feature for}
                            {--activate : Activate the feature}
                            {--deactivate : Deactivate the feature}
                            {--status : Show the current status of the feature}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Toggle a Pennant feature flag for a specific team';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $featureName = $this->argument('feature');
        $featureClass = $this->resolveFeatureClass($featureName);

        if (! class_exists($featureClass)) {
            $this->error("Feature class not found: {$featureClass}");

            return self::FAILURE;
        }

        $teamId = $this->option('team');

        // If no team is specified, show status for all teams
        if (! $teamId && $this->option('status')) {
            return $this->showAllTeamsStatus($featureClass, $featureName);
        }

        // If no team is specified for activate/deactivate, require it
        if (! $teamId && ($this->option('activate') || $this->option('deactivate'))) {
            $this->error('The --team option is required for activating or deactivating features.');

            return self::FAILURE;
        }

        $team = Team::find($teamId);

        if (! $team) {
            $this->error("Team not found with ID: {$teamId}");

            return self::FAILURE;
        }

        if ($this->option('status')) {
            return $this->showStatus($featureClass, $featureName, $team);
        }

        if ($this->option('activate')) {
            Feature::for($team)->activate($featureClass);
            $this->info("Feature '{$featureName}' activated for team '{$team->name}' (ID: {$team->id})");

            return self::SUCCESS;
        }

        if ($this->option('deactivate')) {
            Feature::for($team)->deactivate($featureClass);
            $this->info("Feature '{$featureName}' deactivated for team '{$team->name}' (ID: {$team->id})");

            return self::SUCCESS;
        }

        // No action specified - show usage
        $this->error('Please specify an action: --activate, --deactivate, or --status');

        return self::FAILURE;
    }

    /**
     * Resolve the feature class name from a short name.
     */
    protected function resolveFeatureClass(string $name): string
    {
        // If already a fully qualified class name, return it
        if (str_contains($name, '\\')) {
            return $name;
        }

        // Otherwise, assume it's in the App\Features namespace
        return "App\\Features\\{$name}";
    }

    /**
     * Show the status of a feature for a specific team.
     */
    protected function showStatus(string $featureClass, string $featureName, Team $team): int
    {
        $isActive = Feature::for($team)->active($featureClass);
        $status = $isActive ? '<info>active</info>' : '<comment>inactive</comment>';

        $this->line("Feature '{$featureName}' is {$status} for team '{$team->name}' (ID: {$team->id})");

        return self::SUCCESS;
    }

    /**
     * Show the status of a feature for all teams.
     */
    protected function showAllTeamsStatus(string $featureClass, string $featureName): int
    {
        $teams = Team::all();

        if ($teams->isEmpty()) {
            $this->warn('No teams found.');

            return self::SUCCESS;
        }

        $this->info("Feature '{$featureName}' status:");
        $this->newLine();

        $rows = $teams->map(function (Team $team) use ($featureClass) {
            $isActive = Feature::for($team)->active($featureClass);

            return [
                $team->id,
                $team->name,
                $isActive ? 'Active' : 'Inactive',
            ];
        });

        $this->table(['ID', 'Team Name', 'Status'], $rows);

        return self::SUCCESS;
    }
}
