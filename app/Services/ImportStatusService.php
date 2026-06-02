<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class ImportStatusService
{
    private const CACHE_PREFIX = 'import_status:';

    private const TTL_HOURS = 12;

    /**
     * Create a new import status entry.
     */
    public function create(string $importId, int $teamId, int $userId): void
    {
        $status = [
            'id' => $importId,
            'team_id' => $teamId,
            'user_id' => $userId,
            'status' => 'pending',
            'stage' => 'queued',
            'current_table' => null,
            'tables_processed' => 0,
            'tables_total' => 0,
            'records_imported' => 0,
            'records_deleted' => 0,
            'error' => null,
            'started_at' => now()->toIso8601String(),
            'completed_at' => null,
        ];

        $this->saveStatus($importId, $status);
    }

    /**
     * Update the current stage of the import.
     */
    public function updateStage(string $importId, string $stage, ?string $currentTable = null): void
    {
        $status = $this->getStatus($importId);

        if (! $status) {
            return;
        }

        $status['status'] = 'processing';
        $status['stage'] = $stage;

        if ($currentTable !== null) {
            $status['current_table'] = $currentTable;
        }

        $this->saveStatus($importId, $status);
    }

    /**
     * Update the progress counters.
     */
    public function updateProgress(
        string $importId,
        int $tablesProcessed,
        int $tablesTotal,
        int $recordsImported,
        int $recordsDeleted
    ): void {
        $status = $this->getStatus($importId);

        if (! $status) {
            return;
        }

        $status['tables_processed'] = $tablesProcessed;
        $status['tables_total'] = $tablesTotal;
        $status['records_imported'] = $recordsImported;
        $status['records_deleted'] = $recordsDeleted;

        $this->saveStatus($importId, $status);
    }

    /**
     * Update both stage and progress in a single call.
     */
    public function updateStageAndProgress(
        string $importId,
        string $stage,
        ?string $currentTable,
        int $tablesProcessed,
        int $tablesTotal,
        int $recordsImported,
        int $recordsDeleted
    ): void {
        $status = $this->getStatus($importId);

        if (! $status) {
            return;
        }

        $status['status'] = 'processing';
        $status['stage'] = $stage;
        $status['current_table'] = $currentTable;
        $status['tables_processed'] = $tablesProcessed;
        $status['tables_total'] = $tablesTotal;
        $status['records_imported'] = $recordsImported;
        $status['records_deleted'] = $recordsDeleted;

        $this->saveStatus($importId, $status);
    }

    /**
     * Mark the import as completed.
     */
    public function markCompleted(string $importId, array $summary): void
    {
        $status = $this->getStatus($importId);

        if (! $status) {
            return;
        }

        $status['status'] = 'completed';
        $status['stage'] = 'completed';
        $status['current_table'] = null;
        $status['completed_at'] = now()->toIso8601String();

        // Merge summary data
        if (isset($summary['records_imported'])) {
            $status['records_imported'] = $summary['records_imported'];
        }
        if (isset($summary['records_deleted'])) {
            $status['records_deleted'] = $summary['records_deleted'];
        }
        if (isset($summary['tables_processed'])) {
            $status['tables_processed'] = $summary['tables_processed'];
        }
        if (isset($summary['tables_total'])) {
            $status['tables_total'] = $summary['tables_total'];
        }

        $this->saveStatus($importId, $status);
    }

    /**
     * Mark the import as failed.
     */
    public function markFailed(string $importId, string $error): void
    {
        $status = $this->getStatus($importId);

        if (! $status) {
            // Create a minimal failed status if none exists
            $status = [
                'id' => $importId,
                'team_id' => 0,
                'user_id' => 0,
                'status' => 'failed',
                'stage' => 'failed',
                'current_table' => null,
                'tables_processed' => 0,
                'tables_total' => 0,
                'records_imported' => 0,
                'records_deleted' => 0,
                'error' => $error,
                'started_at' => now()->toIso8601String(),
                'completed_at' => now()->toIso8601String(),
            ];
        } else {
            $status['status'] = 'failed';
            $status['stage'] = 'failed';
            $status['error'] = $error;
            $status['completed_at'] = now()->toIso8601String();
        }

        $this->saveStatus($importId, $status);
    }

    /**
     * Get the current status of an import.
     */
    public function getStatus(string $importId): ?array
    {
        return Cache::get($this->getCacheKey($importId));
    }

    /**
     * Delete an import status.
     */
    public function delete(string $importId): void
    {
        Cache::forget($this->getCacheKey($importId));
    }

    /**
     * Save status to cache.
     */
    protected function saveStatus(string $importId, array $status): void
    {
        Cache::put(
            $this->getCacheKey($importId),
            $status,
            now()->addHours(self::TTL_HOURS)
        );
    }

    /**
     * Get the cache key for an import.
     */
    protected function getCacheKey(string $importId): string
    {
        return self::CACHE_PREFIX.$importId;
    }
}
