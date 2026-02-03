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
use Illuminate\Support\Facades\DB;
use App\Models\ActivityTimeException;
use App\Models\LiturgicalScheduleTime;
use App\Models\GlobalActivityTimeException;

class DataImportService
{
    /**
     * Maps old IDs to new IDs for each table.
     *
     * @var array<string, array<int, int>>
     */
    protected array $idMappings = [];

    /**
     * Import order - must match the dependency order from export.
     *
     * @var array<string, class-string>
     */
    protected array $importOrder = [
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
     * Foreign key mappings: column => source table for ID remapping.
     *
     * @var array<string, string>
     */
    protected array $foreignKeyMappings = [
        'diocese_id' => 'dioceses',
        'parish_id' => 'parishes',
        'sub_parish_id' => 'sub_parishes',
        'church_id' => 'churches',
        'priest_id' => 'priests',
        'schedule_period_id' => 'schedule_periods',
        'activity_time_id' => 'activity_times',
        'holiday_id' => 'holidays',
        'liturgical_schedule_id' => 'liturgical_schedules',
    ];

    /**
     * Import data from an exported JSON structure.
     *
     * @param  array<string, mixed>  $data
     */
    public function import(array $data, int $teamId): ImportResult
    {
        $result = new ImportResult;

        // Validate structure
        if (! isset($data['version']) || ! isset($data['tables'])) {
            $result->addError('Invalid export file structure');

            return $result;
        }

        DB::beginTransaction();

        try {
            // Step 1: Delete existing data (reverse order to respect FK constraints)
            $this->deleteExistingData($teamId, $result);

            // Step 2: Import new data in dependency order
            foreach ($this->importOrder as $tableName => $modelClass) {
                if (! isset($data['tables'][$tableName])) {
                    continue;
                }

                $this->importTable($tableName, $modelClass, $data['tables'][$tableName], $teamId, $result);
            }

            // Step 3: Reset PostgreSQL sequences
            $this->resetSequences();

            DB::commit();
            $result->setSuccess(true);
        } catch (\Throwable $e) {
            DB::rollBack();
            $result->addError('Import failed: '.$e->getMessage());
            $result->setSuccess(false);
        }

        return $result;
    }

    /**
     * Delete all existing domain data for a team.
     */
    protected function deleteExistingData(int $teamId, ImportResult $result): void
    {
        // Delete in reverse order to respect foreign key constraints
        $deletionOrder = array_reverse($this->importOrder, true);

        foreach ($deletionOrder as $tableName => $modelClass) {
            $deleted = $this->deleteTableData($modelClass, $teamId);
            $result->addDeleted($tableName, $deleted);
        }
    }

    /**
     * Delete data from a single table for a team.
     */
    protected function deleteTableData(string $modelClass, int $teamId): int
    {
        $query = $modelClass::query();

        // Models with direct team_id
        if ($this->modelHasTeamId($modelClass)) {
            return $query->where('team_id', $teamId)->delete();
        }

        // Models without team_id - delete based on parent relationship
        return match ($modelClass) {
            HolidayInstance::class => HolidayInstance::query()
                ->whereHas('holiday', fn ($q) => $q->where('team_id', $teamId))
                ->delete(),
            LiturgicalScheduleTime::class => LiturgicalScheduleTime::query()
                ->whereHas('liturgicalSchedule', fn ($q) => $q->where('team_id', $teamId))
                ->delete(),
            default => 0,
        };
    }

    /**
     * Check if a model has team_id column.
     */
    protected function modelHasTeamId(string $modelClass): bool
    {
        return ! in_array($modelClass, [
            HolidayInstance::class,
            LiturgicalScheduleTime::class,
        ]);
    }

    /**
     * Import a single table's data.
     *
     * @param  class-string  $modelClass
     * @param  array<int, array<string, mixed>>  $rows
     */
    protected function importTable(
        string $tableName,
        string $modelClass,
        array $rows,
        int $teamId,
        ImportResult $result
    ): void {
        $this->idMappings[$tableName] = [];

        foreach ($rows as $row) {
            $oldId = $row['id'] ?? null;

            // Transform the row data (remap foreign keys, set team_id)
            $transformedRow = $this->transformForImport($tableName, $row, $teamId);

            // Remove the old ID so a new one is generated
            unset($transformedRow['id']);

            // Create the record
            $model = $modelClass::create($transformedRow);

            // Store the ID mapping
            if ($oldId !== null) {
                $this->idMappings[$tableName][$oldId] = $model->id;
            }

            $result->addImported($tableName);
        }
    }

    /**
     * Transform a row for import, remapping foreign keys and setting team_id.
     *
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    protected function transformForImport(string $tableName, array $row, int $teamId): array
    {
        // Set team_id if the model uses it
        if ($this->tableHasTeamId($tableName)) {
            $row['team_id'] = $teamId;
        }

        // Remap foreign keys
        foreach ($this->foreignKeyMappings as $column => $sourceTable) {
            if (isset($row[$column]) && $row[$column] !== null) {
                $row[$column] = $this->remapId($sourceTable, $row[$column]);
            }
        }

        // Handle polymorphic relationships (churches.parishable_id)
        if ($tableName === 'churches' && isset($row['parishable_type']) && isset($row['parishable_id'])) {
            $row['parishable_id'] = $this->remapPolymorphicId($row['parishable_type'], $row['parishable_id']);
        }

        // Handle polymorphic relationships (schedule_rules.scope_id and schedule_overrides.scope_id)
        if (in_array($tableName, ['schedule_rules', 'schedule_overrides']) && isset($row['scope_type']) && isset($row['scope_id'])) {
            $row['scope_id'] = $this->remapScopeId($row['scope_type'], $row['scope_id']);
        }

        return $row;
    }

    /**
     * Check if a table has team_id column.
     */
    protected function tableHasTeamId(string $tableName): bool
    {
        return ! in_array($tableName, [
            'holiday_instances',
            'liturgical_schedule_times',
        ]);
    }

    /**
     * Remap an old ID to a new ID using stored mappings.
     */
    protected function remapId(string $sourceTable, int $oldId): ?int
    {
        return $this->idMappings[$sourceTable][$oldId] ?? null;
    }

    /**
     * Remap a polymorphic ID based on the morph type.
     */
    protected function remapPolymorphicId(string $morphType, int $oldId): ?int
    {
        // Determine the source table based on the morph type
        $sourceTable = match ($morphType) {
            'App\\Models\\Parish', Parish::class => 'parishes',
            'App\\Models\\SubParish', SubParish::class => 'sub_parishes',
            default => null,
        };

        if ($sourceTable === null) {
            return null;
        }

        return $this->idMappings[$sourceTable][$oldId] ?? null;
    }

    /**
     * Remap scope_id for ScheduleRule and ScheduleOverride models.
     */
    protected function remapScopeId(string $scopeType, int $oldId): ?int
    {
        // scope_type can be 'church' or 'diocese' (from ScheduleScope enum)
        $sourceTable = match ($scopeType) {
            'church' => 'churches',
            'diocese' => 'dioceses',
            default => null,
        };

        if ($sourceTable === null) {
            return null;
        }

        return $this->idMappings[$sourceTable][$oldId] ?? null;
    }

    /**
     * Reset PostgreSQL sequences after bulk import.
     * Only runs on PostgreSQL connections.
     */
    protected function resetSequences(): void
    {
        // Only reset sequences for PostgreSQL
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        foreach ($this->importOrder as $tableName => $modelClass) {
            $table = (new $modelClass)->getTable();

            // Reset the sequence to the max ID + 1
            DB::statement("
                SELECT setval(
                    pg_get_serial_sequence('{$table}', 'id'),
                    COALESCE((SELECT MAX(id) FROM \"{$table}\"), 0) + 1,
                    false
                )
            ");
        }
    }

    /**
     * Get the import order for documentation/testing.
     *
     * @return array<string, class-string>
     */
    public function getImportOrder(): array
    {
        return $this->importOrder;
    }
}

/**
 * Import result tracking class.
 */
class ImportResult
{
    protected bool $success = false;

    /** @var array<string> */
    protected array $errors = [];

    /** @var array<string, int> */
    protected array $imported = [];

    /** @var array<string, int> */
    protected array $deleted = [];

    public function setSuccess(bool $success): void
    {
        $this->success = $success;
    }

    public function isSuccessful(): bool
    {
        return $this->success;
    }

    public function addError(string $error): void
    {
        $this->errors[] = $error;
    }

    /**
     * @return array<string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function addImported(string $table): void
    {
        $this->imported[$table] = ($this->imported[$table] ?? 0) + 1;
    }

    /**
     * @return array<string, int>
     */
    public function getImported(): array
    {
        return $this->imported;
    }

    public function getTotalImported(): int
    {
        return array_sum($this->imported);
    }

    public function addDeleted(string $table, int $count): void
    {
        $this->deleted[$table] = $count;
    }

    /**
     * @return array<string, int>
     */
    public function getDeleted(): array
    {
        return $this->deleted;
    }

    public function getTotalDeleted(): int
    {
        return array_sum($this->deleted);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'errors' => $this->errors,
            'imported' => $this->imported,
            'deleted' => $this->deleted,
            'total_imported' => $this->getTotalImported(),
            'total_deleted' => $this->getTotalDeleted(),
        ];
    }
}
