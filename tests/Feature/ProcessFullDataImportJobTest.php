<?php

use App\Models\Team;
use App\Models\User;
use App\Models\Parish;
use App\Models\Diocese;
use Illuminate\Support\Str;
use App\Jobs\ProcessFullDataImport;
use App\Services\DataImportService;
use App\Services\ImportStatusService;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->team = Team::factory()->create();
    $this->user = User::factory()->create([
        'is_super_admin' => true,
        'current_team_id' => $this->team->id,
    ]);
});

describe('ProcessFullDataImport Job', function () {
    it('imports data successfully', function () {
        $importId = Str::uuid()->toString();
        $filePath = "imports/{$importId}.json";

        // Create import data
        $exportData = [
            'version' => '1.0',
            'exported_at' => now()->toIso8601String(),
            'team_id' => 999,
            'tables' => [
                'dioceses' => [
                    ['id' => 100, 'name' => 'Test Diocese', 'type' => 'diocese'],
                ],
                'parishes' => [
                    ['id' => 200, 'name' => 'Test Parish', 'diocese_id' => 100],
                ],
                'sub_parishes' => [],
                'priests' => [],
                'churches' => [],
                'schedule_periods' => [],
                'activity_times' => [],
                'activity_schedules' => [],
                'activity_time_exceptions' => [],
                'holidays' => [],
                'holiday_instances' => [],
                'liturgical_schedules' => [],
                'liturgical_schedule_times' => [],
                'one_time_activities' => [],
                'global_activity_time_exceptions' => [],
                'schedule_rules' => [],
                'schedule_overrides' => [],
            ],
        ];

        // Store the file
        Storage::disk('local')->put($filePath, json_encode($exportData));

        // Create initial status
        $statusService = app(ImportStatusService::class);
        $statusService->create($importId, $this->team->id, $this->user->id);

        // Run the job
        $job = new ProcessFullDataImport(
            $importId,
            $this->team->id,
            $this->user->id,
            $filePath
        );
        $job->handle(
            app(DataImportService::class),
            $statusService
        );

        // Verify data was imported
        $this->assertDatabaseHas('dioceses', [
            'name' => 'Test Diocese',
            'team_id' => $this->team->id,
        ]);
        $this->assertDatabaseHas('parishes', [
            'name' => 'Test Parish',
            'team_id' => $this->team->id,
        ]);

        // Verify status is completed
        $status = $statusService->getStatus($importId);
        expect($status['status'])->toBe('completed');
        expect($status['records_imported'])->toBe(2);
        expect($status['completed_at'])->not->toBeNull();

        // Verify file was cleaned up
        Storage::disk('local')->assertMissing($filePath);
    });

    it('marks status as failed on error', function () {
        $importId = Str::uuid()->toString();
        $filePath = "imports/{$importId}.json";

        // Store invalid JSON
        Storage::disk('local')->put($filePath, 'not valid json');

        // Create initial status
        $statusService = app(ImportStatusService::class);
        $statusService->create($importId, $this->team->id, $this->user->id);

        // Run the job and expect exception
        $job = new ProcessFullDataImport(
            $importId,
            $this->team->id,
            $this->user->id,
            $filePath
        );

        try {
            $job->handle(
                app(DataImportService::class),
                $statusService
            );
        } catch (RuntimeException $e) {
            // Expected
        }

        // Verify status is failed
        $status = $statusService->getStatus($importId);
        expect($status['status'])->toBe('failed');
        expect($status['error'])->toContain('Invalid JSON');

        // Verify file was cleaned up
        Storage::disk('local')->assertMissing($filePath);
    });

    it('marks status as failed for missing file', function () {
        $importId = Str::uuid()->toString();
        $filePath = "imports/{$importId}.json";

        // Don't create the file

        // Create initial status
        $statusService = app(ImportStatusService::class);
        $statusService->create($importId, $this->team->id, $this->user->id);

        // Run the job and expect exception
        $job = new ProcessFullDataImport(
            $importId,
            $this->team->id,
            $this->user->id,
            $filePath
        );

        try {
            $job->handle(
                app(DataImportService::class),
                $statusService
            );
        } catch (RuntimeException $e) {
            // Expected
        }

        // Verify status is failed
        $status = $statusService->getStatus($importId);
        expect($status['status'])->toBe('failed');
        expect($status['error'])->toContain('Failed to read import file');
    });

    it('marks status as failed for invalid structure', function () {
        $importId = Str::uuid()->toString();
        $filePath = "imports/{$importId}.json";

        // Store valid JSON but invalid structure
        Storage::disk('local')->put($filePath, json_encode(['invalid' => 'structure']));

        // Create initial status
        $statusService = app(ImportStatusService::class);
        $statusService->create($importId, $this->team->id, $this->user->id);

        // Run the job and expect exception
        $job = new ProcessFullDataImport(
            $importId,
            $this->team->id,
            $this->user->id,
            $filePath
        );

        try {
            $job->handle(
                app(DataImportService::class),
                $statusService
            );
        } catch (RuntimeException $e) {
            // Expected
        }

        // Verify status is failed
        $status = $statusService->getStatus($importId);
        expect($status['status'])->toBe('failed');
        expect($status['error'])->toContain('missing version or tables');

        // Verify file was cleaned up
        Storage::disk('local')->assertMissing($filePath);
    });

    it('updates progress during import', function () {
        $importId = Str::uuid()->toString();
        $filePath = "imports/{$importId}.json";

        // Create import data with multiple tables
        $exportData = [
            'version' => '1.0',
            'exported_at' => now()->toIso8601String(),
            'team_id' => 999,
            'tables' => [
                'dioceses' => [
                    ['id' => 100, 'name' => 'Diocese 1', 'type' => 'diocese'],
                    ['id' => 101, 'name' => 'Diocese 2', 'type' => 'archdiocese'],
                ],
                'parishes' => [],
                'sub_parishes' => [],
                'priests' => [],
                'churches' => [],
                'schedule_periods' => [],
                'activity_times' => [],
                'activity_schedules' => [],
                'activity_time_exceptions' => [],
                'holidays' => [],
                'holiday_instances' => [],
                'liturgical_schedules' => [],
                'liturgical_schedule_times' => [],
                'one_time_activities' => [],
                'global_activity_time_exceptions' => [],
                'schedule_rules' => [],
                'schedule_overrides' => [],
            ],
        ];

        Storage::disk('local')->put($filePath, json_encode($exportData));

        // Create initial status
        $statusService = app(ImportStatusService::class);
        $statusService->create($importId, $this->team->id, $this->user->id);

        // Run the job
        $job = new ProcessFullDataImport(
            $importId,
            $this->team->id,
            $this->user->id,
            $filePath
        );
        $job->handle(
            app(DataImportService::class),
            $statusService
        );

        // Verify final status shows correct totals
        $status = $statusService->getStatus($importId);
        expect($status['status'])->toBe('completed');
        expect($status['records_imported'])->toBe(2);
        expect($status['tables_total'])->toBeGreaterThan(0);
    });

    it('deletes existing data before importing', function () {
        $importId = Str::uuid()->toString();
        $filePath = "imports/{$importId}.json";

        // Create existing data
        $existingDiocese = Diocese::factory()->create(['team_id' => $this->team->id]);
        $existingParish = Parish::factory()->create([
            'team_id' => $this->team->id,
            'diocese_id' => $existingDiocese->id,
        ]);

        // Create import data
        $exportData = [
            'version' => '1.0',
            'exported_at' => now()->toIso8601String(),
            'team_id' => 999,
            'tables' => [
                'dioceses' => [
                    ['id' => 100, 'name' => 'New Diocese', 'type' => 'diocese'],
                ],
                'parishes' => [],
                'sub_parishes' => [],
                'priests' => [],
                'churches' => [],
                'schedule_periods' => [],
                'activity_times' => [],
                'activity_schedules' => [],
                'activity_time_exceptions' => [],
                'holidays' => [],
                'holiday_instances' => [],
                'liturgical_schedules' => [],
                'liturgical_schedule_times' => [],
                'one_time_activities' => [],
                'global_activity_time_exceptions' => [],
                'schedule_rules' => [],
                'schedule_overrides' => [],
            ],
        ];

        Storage::disk('local')->put($filePath, json_encode($exportData));

        // Create initial status
        $statusService = app(ImportStatusService::class);
        $statusService->create($importId, $this->team->id, $this->user->id);

        // Run the job
        $job = new ProcessFullDataImport(
            $importId,
            $this->team->id,
            $this->user->id,
            $filePath
        );
        $job->handle(
            app(DataImportService::class),
            $statusService
        );

        // Verify old data was deleted
        expect(Diocese::find($existingDiocese->id))->toBeNull();
        expect(Parish::find($existingParish->id))->toBeNull();

        // Verify new data exists
        $this->assertDatabaseHas('dioceses', [
            'name' => 'New Diocese',
            'team_id' => $this->team->id,
        ]);

        // Verify status shows deletion count
        $status = $statusService->getStatus($importId);
        expect($status['records_deleted'])->toBeGreaterThan(0);
    });
});
