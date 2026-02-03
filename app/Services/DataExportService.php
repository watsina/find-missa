<?php

namespace App\Services;

use App\Models\Church;
use App\Models\Parish;
use App\Models\Priest;
use App\Models\Diocese;
use App\Models\Holiday;
use App\Models\SubParish;
use App\Models\ActivityTime;
use App\Models\ScheduleRule;
use App\Models\SchedulePeriod;
use App\Models\HolidayInstance;
use App\Models\OneTimeActivity;
use App\Models\ActivitySchedule;
use App\Models\ScheduleOverride;
use App\Models\LiturgicalSchedule;
use App\Models\ActivityTimeException;
use App\Models\LiturgicalScheduleTime;
use App\Models\GlobalActivityTimeException;

class DataExportService
{
    /**
     * Export order - tables listed in dependency order.
     * Tables with no dependencies come first.
     *
     * @var array<string, class-string>
     */
    protected array $exportOrder = [
        'dioceses' => Diocese::class,
        'parishes' => Parish::class,
        'sub_parishes' => SubParish::class,
        'priests' => Priest::class,
        'churches' => Church::class,
        'schedule_periods' => SchedulePeriod::class,
        'activity_times' => ActivityTime::class,
        'activity_schedules' => ActivitySchedule::class,
        'activity_time_exceptions' => ActivityTimeException::class,
        'holidays' => Holiday::class,
        'holiday_instances' => HolidayInstance::class,
        'liturgical_schedules' => LiturgicalSchedule::class,
        'liturgical_schedule_times' => LiturgicalScheduleTime::class,
        'one_time_activities' => OneTimeActivity::class,
        'global_activity_time_exceptions' => GlobalActivityTimeException::class,
        'schedule_rules' => ScheduleRule::class,
        'schedule_overrides' => ScheduleOverride::class,
    ];

    /**
     * Export all domain data for a team.
     *
     * @return array<string, mixed>
     */
    public function export(int $teamId): array
    {
        $data = [
            'exported_at' => now()->toIso8601String(),
            'version' => '1.0',
            'team_id' => $teamId,
            'tables' => [],
        ];

        foreach ($this->exportOrder as $tableName => $modelClass) {
            $data['tables'][$tableName] = $this->exportTable($modelClass, $teamId);
        }

        return $data;
    }

    /**
     * Export a single table's data.
     *
     * @param  class-string  $modelClass
     * @return array<int, array<string, mixed>>
     */
    protected function exportTable(string $modelClass, int $teamId): array
    {
        $query = $modelClass::query();

        // Most models have team_id, but some don't (like LiturgicalScheduleTime)
        if ($this->modelHasTeamScope($modelClass)) {
            $query->where('team_id', $teamId);
        } else {
            // For models without direct team_id, we need to filter by parent
            $query = $this->filterByParentTeam($modelClass, $teamId);
        }

        return $query->get()->map(fn ($model) => $this->transformForExport($model))->toArray();
    }

    /**
     * Check if a model has a team_id column for direct filtering.
     */
    protected function modelHasTeamScope(string $modelClass): bool
    {
        // These models have team_id directly
        $modelsWithTeamId = [
            Diocese::class,
            Parish::class,
            SubParish::class,
            Priest::class,
            Church::class,
            SchedulePeriod::class,
            ActivityTime::class,
            ActivitySchedule::class,
            ActivityTimeException::class,
            Holiday::class,
            LiturgicalSchedule::class,
            OneTimeActivity::class,
            GlobalActivityTimeException::class,
            ScheduleRule::class,
            ScheduleOverride::class,
        ];

        return in_array($modelClass, $modelsWithTeamId);
    }

    /**
     * Filter models that don't have direct team_id by their parent relationship.
     *
     * @param  class-string  $modelClass
     */
    protected function filterByParentTeam(string $modelClass, int $teamId): \Illuminate\Database\Eloquent\Builder
    {
        return match ($modelClass) {
            HolidayInstance::class => HolidayInstance::query()
                ->whereHas('holiday', fn ($q) => $q->where('team_id', $teamId)),
            LiturgicalScheduleTime::class => LiturgicalScheduleTime::query()
                ->whereHas('liturgicalSchedule', fn ($q) => $q->where('team_id', $teamId)),
            default => $modelClass::query(),
        };
    }

    /**
     * Transform a model for export, handling special cases.
     *
     * @return array<string, mixed>
     */
    protected function transformForExport(\Illuminate\Database\Eloquent\Model $model): array
    {
        $attributes = $model->attributesToArray();

        // Remove auto-generated timestamps if you want a cleaner export
        // (keeping them for accurate restoration)

        return $attributes;
    }

    /**
     * Get the export order for documentation/testing.
     *
     * @return array<string, class-string>
     */
    public function getExportOrder(): array
    {
        return $this->exportOrder;
    }

    /**
     * Get the reverse order for deletion during import.
     *
     * @return array<string, class-string>
     */
    public function getDeletionOrder(): array
    {
        return array_reverse($this->exportOrder, true);
    }
}
