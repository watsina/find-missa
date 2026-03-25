<?php

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Pennant\Feature;
use App\Features\DataImportExport;
use App\Services\ImportStatusService;

beforeEach(function () {
    $this->team = Team::factory()->create();
    $this->adminUser = User::factory()->create([
        'is_super_admin' => true,
        'current_team_id' => $this->team->id,
    ]);
    $this->regularUser = User::factory()->create([
        'is_super_admin' => false,
        'current_team_id' => $this->team->id,
    ]);

    // Enable the DataImportExport feature for the team
    Feature::for($this->team)->activate(DataImportExport::class);
});

describe('Import Status Controller', function () {
    it('requires authentication', function () {
        $importId = Str::uuid()->toString();

        $this->getJson("/api/v1/import-status/{$importId}")
            ->assertUnauthorized();
    });

    it('requires data.import permission', function () {
        $importId = Str::uuid()->toString();

        $this->actingAs($this->regularUser)
            ->getJson("/api/v1/import-status/{$importId}")
            ->assertForbidden();
    });

    it('returns 404 for non-existent import', function () {
        $importId = Str::uuid()->toString();

        $this->actingAs($this->adminUser)
            ->getJson("/api/v1/import-status/{$importId}")
            ->assertNotFound()
            ->assertJson(['message' => 'Import not found']);
    });

    it('returns 404 for import belonging to another team', function () {
        $otherTeam = Team::factory()->create();
        $importId = Str::uuid()->toString();

        // Create status for another team
        $statusService = app(ImportStatusService::class);
        $statusService->create($importId, $otherTeam->id, 999);

        $this->actingAs($this->adminUser)
            ->getJson("/api/v1/import-status/{$importId}")
            ->assertNotFound()
            ->assertJson(['message' => 'Import not found']);
    });

    it('returns import status for valid import', function () {
        $importId = Str::uuid()->toString();

        // Create status
        $statusService = app(ImportStatusService::class);
        $statusService->create($importId, $this->team->id, $this->adminUser->id);

        $response = $this->actingAs($this->adminUser)
            ->getJson("/api/v1/import-status/{$importId}");

        $response->assertOk();
        $response->assertJsonStructure([
            'id',
            'team_id',
            'user_id',
            'status',
            'stage',
            'current_table',
            'tables_processed',
            'tables_total',
            'records_imported',
            'records_deleted',
            'error',
            'started_at',
            'completed_at',
        ]);
        $response->assertJson([
            'id' => $importId,
            'status' => 'pending',
            'stage' => 'queued',
        ]);
    });

    it('returns updated status when processing', function () {
        $importId = Str::uuid()->toString();

        // Create and update status
        $statusService = app(ImportStatusService::class);
        $statusService->create($importId, $this->team->id, $this->adminUser->id);
        $statusService->updateStageAndProgress(
            $importId,
            'importing',
            'dioceses',
            5,
            10,
            100,
            50
        );

        $response = $this->actingAs($this->adminUser)
            ->getJson("/api/v1/import-status/{$importId}");

        $response->assertOk();
        $response->assertJson([
            'status' => 'processing',
            'stage' => 'importing',
            'current_table' => 'dioceses',
            'tables_processed' => 5,
            'tables_total' => 10,
            'records_imported' => 100,
            'records_deleted' => 50,
        ]);
    });

    it('returns completed status', function () {
        $importId = Str::uuid()->toString();

        // Create and complete status
        $statusService = app(ImportStatusService::class);
        $statusService->create($importId, $this->team->id, $this->adminUser->id);
        $statusService->markCompleted($importId, [
            'records_imported' => 500,
            'records_deleted' => 100,
            'tables_processed' => 10,
            'tables_total' => 10,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->getJson("/api/v1/import-status/{$importId}");

        $response->assertOk();
        $response->assertJson([
            'status' => 'completed',
            'stage' => 'completed',
            'records_imported' => 500,
            'records_deleted' => 100,
        ]);
        expect($response->json('completed_at'))->not->toBeNull();
    });

    it('returns failed status with error message', function () {
        $importId = Str::uuid()->toString();

        // Create and fail status
        $statusService = app(ImportStatusService::class);
        $statusService->create($importId, $this->team->id, $this->adminUser->id);
        $statusService->markFailed($importId, 'Database connection failed');

        $response = $this->actingAs($this->adminUser)
            ->getJson("/api/v1/import-status/{$importId}");

        $response->assertOk();
        $response->assertJson([
            'status' => 'failed',
            'stage' => 'failed',
            'error' => 'Database connection failed',
        ]);
    });
});

describe('Import Status Service', function () {
    it('creates status with correct initial values', function () {
        $importId = Str::uuid()->toString();
        $statusService = app(ImportStatusService::class);

        $statusService->create($importId, $this->team->id, $this->adminUser->id);
        $status = $statusService->getStatus($importId);

        expect($status)->not->toBeNull();
        expect($status['id'])->toBe($importId);
        expect($status['team_id'])->toBe($this->team->id);
        expect($status['user_id'])->toBe($this->adminUser->id);
        expect($status['status'])->toBe('pending');
        expect($status['stage'])->toBe('queued');
        expect($status['current_table'])->toBeNull();
        expect($status['tables_processed'])->toBe(0);
        expect($status['records_imported'])->toBe(0);
        expect($status['records_deleted'])->toBe(0);
        expect($status['error'])->toBeNull();
        expect($status['started_at'])->not->toBeNull();
        expect($status['completed_at'])->toBeNull();
    });

    it('updates stage correctly', function () {
        $importId = Str::uuid()->toString();
        $statusService = app(ImportStatusService::class);

        $statusService->create($importId, $this->team->id, $this->adminUser->id);
        $statusService->updateStage($importId, 'deleting', 'dioceses');

        $status = $statusService->getStatus($importId);

        expect($status['status'])->toBe('processing');
        expect($status['stage'])->toBe('deleting');
        expect($status['current_table'])->toBe('dioceses');
    });

    it('updates progress correctly', function () {
        $importId = Str::uuid()->toString();
        $statusService = app(ImportStatusService::class);

        $statusService->create($importId, $this->team->id, $this->adminUser->id);
        $statusService->updateProgress($importId, 5, 10, 250, 100);

        $status = $statusService->getStatus($importId);

        expect($status['tables_processed'])->toBe(5);
        expect($status['tables_total'])->toBe(10);
        expect($status['records_imported'])->toBe(250);
        expect($status['records_deleted'])->toBe(100);
    });

    it('deletes status', function () {
        $importId = Str::uuid()->toString();
        $statusService = app(ImportStatusService::class);

        $statusService->create($importId, $this->team->id, $this->adminUser->id);
        expect($statusService->getStatus($importId))->not->toBeNull();

        $statusService->delete($importId);
        expect($statusService->getStatus($importId))->toBeNull();
    });

    it('handles marking failed for non-existent status', function () {
        $importId = Str::uuid()->toString();
        $statusService = app(ImportStatusService::class);

        // Should create a minimal failed status
        $statusService->markFailed($importId, 'Some error');

        $status = $statusService->getStatus($importId);
        expect($status)->not->toBeNull();
        expect($status['status'])->toBe('failed');
        expect($status['error'])->toBe('Some error');
    });
});
