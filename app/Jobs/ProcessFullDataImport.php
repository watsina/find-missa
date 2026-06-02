<?php

namespace App\Jobs;

use Throwable;
use Illuminate\Bus\Queueable;
use App\Services\DataImportService;
use Illuminate\Support\Facades\Log;
use App\Services\ImportStatusService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessFullDataImport implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 600; // 10 minutes

    /**
     * The number of times the job may be attempted.
     * Set to 1 because this is a destructive operation - no retries.
     */
    public int $tries = 1;

    /**
     * Indicate if the job should be marked as failed on timeout.
     */
    public bool $failOnTimeout = true;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $importId,
        public int $teamId,
        public int $userId,
        public string $filePath,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(DataImportService $importService, ImportStatusService $statusService): void
    {
        Log::info('Starting data import job', [
            'import_id' => $this->importId,
            'team_id' => $this->teamId,
            'file_path' => $this->filePath,
        ]);

        try {
            // Update status: starting
            $statusService->updateStage($this->importId, 'reading');

            // Read and decode file
            $contents = Storage::disk('local')->get($this->filePath);

            if ($contents === null) {
                throw new \RuntimeException("Failed to read import file: {$this->filePath}");
            }

            $data = json_decode($contents, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Invalid JSON: '.json_last_error_msg());
            }

            // Validate required structure
            if (! isset($data['version']) || ! isset($data['tables'])) {
                throw new \RuntimeException('Invalid import file structure: missing version or tables');
            }

            // Count total tables for progress tracking
            $totalTables = count($data['tables']);
            $statusService->updateProgress($this->importId, 0, $totalTables, 0, 0);

            // Run import with progress callback
            $result = $importService->import(
                $data,
                $this->teamId,
                function (string $stage, string $table, int $tablesProcessed, int $tablesTotal, int $recordsImported, int $recordsDeleted) use ($statusService) {
                    $statusService->updateStageAndProgress(
                        $this->importId,
                        $stage,
                        $table,
                        $tablesProcessed,
                        $tablesTotal,
                        $recordsImported,
                        $recordsDeleted
                    );
                }
            );

            // Mark completed
            $statusService->markCompleted($this->importId, [
                'records_imported' => $result->getTotalImported(),
                'records_deleted' => $result->getTotalDeleted(),
                'tables_processed' => $totalTables,
                'tables_total' => $totalTables,
            ]);

            Log::info('Data import completed successfully', [
                'import_id' => $this->importId,
                'team_id' => $this->teamId,
                'records_imported' => $result->getTotalImported(),
                'records_deleted' => $result->getTotalDeleted(),
            ]);

        } catch (Throwable $e) {
            Log::error('Data import failed', [
                'import_id' => $this->importId,
                'team_id' => $this->teamId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $statusService->markFailed($this->importId, $e->getMessage());

            throw $e;
        } finally {
            // Clean up temp file
            $this->cleanupFile();
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Throwable $exception): void
    {
        Log::error('Data import job failed', [
            'import_id' => $this->importId,
            'team_id' => $this->teamId,
            'error' => $exception?->getMessage(),
        ]);

        // Mark as failed in status service
        app(ImportStatusService::class)->markFailed(
            $this->importId,
            $exception?->getMessage() ?? 'Unknown error'
        );

        // Clean up temp file
        $this->cleanupFile();
    }

    /**
     * Clean up the temporary import file.
     */
    protected function cleanupFile(): void
    {
        try {
            if (Storage::disk('local')->exists($this->filePath)) {
                Storage::disk('local')->delete($this->filePath);
                Log::info('Cleaned up import file', ['file_path' => $this->filePath]);
            }
        } catch (Throwable $e) {
            Log::warning('Failed to clean up import file', [
                'file_path' => $this->filePath,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
