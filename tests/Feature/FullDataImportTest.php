<?php

use App\Models\Team;
use App\Models\User;
use App\Models\Church;
use App\Models\Parish;
use App\Models\Diocese;
use App\Models\SubParish;
use Laravel\Pennant\Feature;
use App\Services\ImportResult;
use Illuminate\Http\UploadedFile;
use App\Features\DataImportExport;
use App\Jobs\ProcessFullDataImport;
use App\Services\DataExportService;
use App\Services\DataImportService;
use App\Services\ImportStatusService;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

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

describe('Full Data Import Controller', function () {
    it('requires authentication', function () {
        $file = UploadedFile::fake()->create('data.json', 100);

        $this->postJson('/api/v1/full-import', ['file' => $file])
            ->assertUnauthorized();
    });

    it('requires data.import permission', function () {
        $file = UploadedFile::fake()->create('data.json', 100);

        $this->actingAs($this->regularUser)
            ->postJson('/api/v1/full-import', ['file' => $file])
            ->assertForbidden();
    });

    it('returns 403 when feature is disabled', function () {
        // Deactivate the feature for the team
        Feature::for($this->team)->deactivate(DataImportExport::class);

        $file = UploadedFile::fake()->create('data.json', 100);

        $this->actingAs($this->adminUser)
            ->postJson('/api/v1/full-import', ['file' => $file])
            ->assertForbidden()
            ->assertJson(['message' => 'This feature is not available.']);
    });

    it('requires a file', function () {
        $this->actingAs($this->adminUser)
            ->postJson('/api/v1/full-import', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['file']);
    });

    it('validates file is json', function () {
        // Create an image file which is not allowed
        $file = UploadedFile::fake()->image('data.png');

        $this->actingAs($this->adminUser)
            ->postJson('/api/v1/full-import', ['file' => $file])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['file']);
    });

    it('queues import job for valid json data', function () {
        Queue::fake();

        // Create export data
        $exportData = [
            'version' => '1.0',
            'exported_at' => now()->toIso8601String(),
            'team_id' => 999,
            'tables' => [
                'dioceses' => [
                    ['id' => 100, 'name' => 'Test Diocese', 'type' => 'diocese'],
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

        $json = json_encode($exportData);
        $file = UploadedFile::fake()->createWithContent('data.json', $json);

        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/v1/full-import', ['file' => $file]);

        $response->assertStatus(202);
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure(['import_id']);

        // Verify job was dispatched
        Queue::assertPushed(ProcessFullDataImport::class, function ($job) {
            return $job->teamId === $this->team->id
                && $job->userId === $this->adminUser->id;
        });
    });

    it('stores file temporarily for the job', function () {
        Queue::fake();
        Storage::fake('local');

        $exportData = [
            'version' => '1.0',
            'exported_at' => now()->toIso8601String(),
            'team_id' => 999,
            'tables' => [
                'dioceses' => [
                    ['id' => 100, 'name' => 'Test Diocese', 'type' => 'diocese'],
                ],
            ],
        ];

        $json = json_encode($exportData);
        $file = UploadedFile::fake()->createWithContent('data.json', $json);

        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/v1/full-import', ['file' => $file]);

        $response->assertStatus(202);
        $importId = $response->json('import_id');

        // Verify file was stored
        Storage::disk('local')->assertExists("imports/{$importId}.json");
    });

    it('creates import status entry', function () {
        Queue::fake();

        $exportData = [
            'version' => '1.0',
            'exported_at' => now()->toIso8601String(),
            'team_id' => 999,
            'tables' => [
                'dioceses' => [],
            ],
        ];

        $json = json_encode($exportData);
        $file = UploadedFile::fake()->createWithContent('data.json', $json);

        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/v1/full-import', ['file' => $file]);

        $response->assertStatus(202);
        $importId = $response->json('import_id');

        // Verify status was created
        $statusService = app(ImportStatusService::class);
        $status = $statusService->getStatus($importId);

        expect($status)->not->toBeNull();
        expect($status['status'])->toBe('pending');
        expect($status['team_id'])->toBe($this->team->id);
        expect($status['user_id'])->toBe($this->adminUser->id);
    });

    it('rejects invalid json structure', function () {
        $json = json_encode(['invalid' => 'structure']);
        $file = UploadedFile::fake()->createWithContent('data.json', $json);

        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/v1/full-import', ['file' => $file]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
    });
});

describe('Data Import Service', function () {
    it('remaps foreign keys during import', function () {
        $exportData = [
            'version' => '1.0',
            'exported_at' => now()->toIso8601String(),
            'team_id' => 999,
            'tables' => [
                'dioceses' => [
                    ['id' => 100, 'name' => 'Test Diocese', 'type' => 'diocese'],
                ],
                'parishes' => [
                    [
                        'id' => 200,
                        'name' => 'Test Parish',
                        'diocese_id' => 100, // Old diocese ID
                    ],
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

        $service = new DataImportService;
        $result = $service->import($exportData, $this->team->id);

        expect($result->isSuccessful())->toBeTrue();
        expect($result->getImported()['dioceses'])->toBe(1);
        expect($result->getImported()['parishes'])->toBe(1);

        // Verify the diocese was created
        $diocese = Diocese::where('team_id', $this->team->id)->first();
        expect($diocese)->not->toBeNull();
        expect($diocese->name)->toBe('Test Diocese');

        // Verify the parish was created with the new diocese ID
        $parish = Parish::where('team_id', $this->team->id)->first();
        expect($parish)->not->toBeNull();
        expect($parish->name)->toBe('Test Parish');
        expect($parish->diocese_id)->toBe($diocese->id);
    });

    it('handles polymorphic relationships for churches', function () {
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
                'churches' => [
                    [
                        'id' => 300,
                        'name' => 'Test Church',
                        'parishable_type' => 'App\\Models\\Parish',
                        'parishable_id' => 200, // Old parish ID
                    ],
                ],
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

        $service = new DataImportService;
        $result = $service->import($exportData, $this->team->id);

        expect($result->isSuccessful())->toBeTrue();

        // Verify relationships
        $parish = Parish::where('team_id', $this->team->id)->first();
        $church = Church::where('team_id', $this->team->id)->first();

        expect($church)->not->toBeNull();
        expect($church->parishable_id)->toBe($parish->id);
        expect($church->parishable_type)->toBe(Parish::class);
    });

    it('handles sub_parish polymorphic relationship', function () {
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
                'sub_parishes' => [
                    ['id' => 250, 'name' => 'Test SubParish', 'parish_id' => 200],
                ],
                'priests' => [],
                'churches' => [
                    [
                        'id' => 300,
                        'name' => 'SubParish Church',
                        'parishable_type' => 'App\\Models\\SubParish',
                        'parishable_id' => 250, // Old sub_parish ID
                    ],
                ],
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

        $service = new DataImportService;
        $result = $service->import($exportData, $this->team->id);

        expect($result->isSuccessful())->toBeTrue();

        // Verify relationships
        $subParish = SubParish::where('team_id', $this->team->id)->first();
        $church = Church::where('team_id', $this->team->id)->first();

        expect($church)->not->toBeNull();
        expect($church->parishable_id)->toBe($subParish->id);
        expect($church->parishable_type)->toBe(SubParish::class);
    });

    it('handles morph alias format for parish polymorphic relationship', function () {
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
                'churches' => [
                    [
                        'id' => 300,
                        'name' => 'Morph Alias Church',
                        'parishable_type' => 'parish', // Morph alias format (not full class name)
                        'parishable_id' => 200,
                    ],
                ],
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

        $service = new DataImportService;
        $result = $service->import($exportData, $this->team->id);

        expect($result->isSuccessful())->toBeTrue();

        $parish = Parish::where('team_id', $this->team->id)->first();
        $church = Church::where('team_id', $this->team->id)->first();

        expect($church)->not->toBeNull();
        expect($church->parishable_id)->toBe($parish->id);
        expect($church->parishable_type)->toBe('parish');
    });

    it('handles morph alias format for subParish polymorphic relationship', function () {
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
                'sub_parishes' => [
                    ['id' => 250, 'name' => 'Test SubParish', 'parish_id' => 200],
                ],
                'priests' => [],
                'churches' => [
                    [
                        'id' => 300,
                        'name' => 'SubParish Morph Alias Church',
                        'parishable_type' => 'subParish', // Morph alias format (not full class name)
                        'parishable_id' => 250,
                    ],
                ],
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

        $service = new DataImportService;
        $result = $service->import($exportData, $this->team->id);

        expect($result->isSuccessful())->toBeTrue();

        $subParish = SubParish::where('team_id', $this->team->id)->first();
        $church = Church::where('team_id', $this->team->id)->first();

        expect($church)->not->toBeNull();
        expect($church->parishable_id)->toBe($subParish->id);
        expect($church->parishable_type)->toBe('subParish');
    });

    it('preserves polymorphic relationships through full export/import cycle with morph aliases', function () {
        // Create original data - Laravel will store using morph aliases
        $diocese = Diocese::factory()->create(['team_id' => $this->team->id]);
        $parish = Parish::factory()->create([
            'team_id' => $this->team->id,
            'diocese_id' => $diocese->id,
        ]);
        $subParish = SubParish::factory()->create([
            'team_id' => $this->team->id,
            'parish_id' => $parish->id,
        ]);

        // Create churches with both parish and sub-parish relationships
        $churchWithParish = Church::factory()->create([
            'team_id' => $this->team->id,
            'parishable_id' => $parish->id,
            'parishable_type' => 'parish', // Using morph alias directly
        ]);
        $churchWithSubParish = Church::factory()->create([
            'team_id' => $this->team->id,
            'parishable_id' => $subParish->id,
            'parishable_type' => 'subParish', // Using morph alias directly
        ]);

        // Export the data
        $exportService = new DataExportService;
        $exportedData = $exportService->export($this->team->id);

        // Verify exported data uses morph alias format
        $exportedChurches = $exportedData['tables']['churches'];
        expect($exportedChurches)->toHaveCount(2);

        $parishChurch = collect($exportedChurches)->firstWhere('name', $churchWithParish->name);
        $subParishChurch = collect($exportedChurches)->firstWhere('name', $churchWithSubParish->name);

        expect($parishChurch['parishable_type'])->toBe('parish');
        expect($subParishChurch['parishable_type'])->toBe('subParish');

        // Import back into the same team
        $importService = new DataImportService;
        $result = $importService->import($exportedData, $this->team->id);

        expect($result->isSuccessful())->toBeTrue();

        // Verify relationships are preserved after import
        $importedParish = Parish::where('team_id', $this->team->id)->first();
        $importedSubParish = SubParish::where('team_id', $this->team->id)->first();
        $importedChurches = Church::where('team_id', $this->team->id)->get();

        expect($importedChurches)->toHaveCount(2);

        $reimportedParishChurch = $importedChurches->firstWhere('name', $churchWithParish->name);
        $reimportedSubParishChurch = $importedChurches->firstWhere('name', $churchWithSubParish->name);

        expect($reimportedParishChurch->parishable_id)->toBe($importedParish->id);
        expect($reimportedParishChurch->parishable_type)->toBe('parish');

        expect($reimportedSubParishChurch->parishable_id)->toBe($importedSubParish->id);
        expect($reimportedSubParishChurch->parishable_type)->toBe('subParish');
    });

    it('deletes existing data before import', function () {
        // Create existing data
        $existingDiocese = Diocese::factory()->create(['team_id' => $this->team->id]);
        $existingParish = Parish::factory()->create([
            'team_id' => $this->team->id,
            'diocese_id' => $existingDiocese->id,
        ]);

        $exportData = [
            'version' => '1.0',
            'exported_at' => now()->toIso8601String(),
            'team_id' => 999,
            'tables' => [
                'dioceses' => [
                    ['id' => 100, 'name' => 'Imported Diocese', 'type' => 'diocese'],
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

        $service = new DataImportService;
        $result = $service->import($exportData, $this->team->id);

        expect($result->isSuccessful())->toBeTrue();

        // Old data should be deleted
        expect(Diocese::find($existingDiocese->id))->toBeNull();
        expect(Parish::find($existingParish->id))->toBeNull();

        // New data should exist
        expect(Diocese::where('name', 'Imported Diocese')->first())->not->toBeNull();

        // Check deleted counts
        expect($result->getDeleted()['parishes'])->toBe(1);
        expect($result->getDeleted()['dioceses'])->toBe(1);
    });

    it('does not affect other teams data', function () {
        // Create data for another team
        $otherTeam = Team::factory()->create();
        $otherDiocese = Diocese::factory()->create(['team_id' => $otherTeam->id]);

        $exportData = [
            'version' => '1.0',
            'exported_at' => now()->toIso8601String(),
            'team_id' => 999,
            'tables' => [
                'dioceses' => [
                    ['id' => 100, 'name' => 'Imported Diocese', 'type' => 'diocese'],
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

        $service = new DataImportService;
        $result = $service->import($exportData, $this->team->id);

        expect($result->isSuccessful())->toBeTrue();

        // Other team's data should be untouched
        expect(Diocese::find($otherDiocese->id))->not->toBeNull();
        expect(Diocese::where('team_id', $otherTeam->id)->count())->toBe(1);
    });

    it('returns error for invalid structure', function () {
        $service = new DataImportService;
        $result = $service->import(['invalid' => 'data'], $this->team->id);

        expect($result->isSuccessful())->toBeFalse();
        expect($result->getErrors())->toHaveCount(1);
        expect($result->getErrors()[0])->toContain('Invalid export file structure');
    });

    it('returns import statistics', function () {
        $exportData = [
            'version' => '1.0',
            'exported_at' => now()->toIso8601String(),
            'team_id' => 999,
            'tables' => [
                'dioceses' => [
                    ['id' => 100, 'name' => 'Diocese 1', 'type' => 'diocese'],
                    ['id' => 101, 'name' => 'Diocese 2', 'type' => 'archdiocese'],
                ],
                'parishes' => [
                    ['id' => 200, 'name' => 'Parish 1', 'diocese_id' => 100],
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

        $service = new DataImportService;
        $result = $service->import($exportData, $this->team->id);

        expect($result->isSuccessful())->toBeTrue();
        expect($result->getImported()['dioceses'])->toBe(2);
        expect($result->getImported()['parishes'])->toBe(1);
        expect($result->getTotalImported())->toBe(3);
    });
});

describe('ImportResult class', function () {
    it('tracks success state', function () {
        $result = new ImportResult;

        expect($result->isSuccessful())->toBeFalse();

        $result->setSuccess(true);

        expect($result->isSuccessful())->toBeTrue();
    });

    it('tracks errors', function () {
        $result = new ImportResult;

        $result->addError('Error 1');
        $result->addError('Error 2');

        expect($result->getErrors())->toHaveCount(2);
        expect($result->getErrors()[0])->toBe('Error 1');
    });

    it('tracks imported counts', function () {
        $result = new ImportResult;

        $result->addImported('dioceses');
        $result->addImported('dioceses');
        $result->addImported('parishes');

        expect($result->getImported()['dioceses'])->toBe(2);
        expect($result->getImported()['parishes'])->toBe(1);
        expect($result->getTotalImported())->toBe(3);
    });

    it('tracks deleted counts', function () {
        $result = new ImportResult;

        $result->addDeleted('dioceses', 5);
        $result->addDeleted('parishes', 10);

        expect($result->getDeleted()['dioceses'])->toBe(5);
        expect($result->getDeleted()['parishes'])->toBe(10);
        expect($result->getTotalDeleted())->toBe(15);
    });

    it('converts to array', function () {
        $result = new ImportResult;
        $result->setSuccess(true);
        $result->addImported('dioceses');
        $result->addDeleted('parishes', 2);

        $array = $result->toArray();

        expect($array)->toHaveKeys(['success', 'errors', 'imported', 'deleted', 'total_imported', 'total_deleted']);
        expect($array['success'])->toBeTrue();
        expect($array['total_imported'])->toBe(1);
        expect($array['total_deleted'])->toBe(2);
    });
});

describe('End-to-end export/import cycle', function () {
    it('can export and reimport data into the same team', function () {
        // Create original data
        $diocese = Diocese::factory()->create(['team_id' => $this->team->id]);
        $parish = Parish::factory()->create([
            'team_id' => $this->team->id,
            'diocese_id' => $diocese->id,
        ]);
        $church = Church::factory()->create([
            'team_id' => $this->team->id,
            'parishable_id' => $parish->id,
            'parishable_type' => Parish::class,
        ]);

        // Export the data
        $exportService = new DataExportService;
        $exportedData = $exportService->export($this->team->id);

        // Store original values
        $originalDioceseName = $diocese->name;
        $originalParishName = $parish->name;
        $originalChurchName = $church->name;
        $originalDioceseId = $diocese->id;
        $originalParishId = $parish->id;
        $originalChurchId = $church->id;

        // Import back into the same team (simulates backup/restore)
        $importService = new DataImportService;
        $result = $importService->import($exportedData, $this->team->id);

        expect($result->isSuccessful())->toBeTrue();

        // Verify old data was deleted
        expect(Diocese::find($originalDioceseId))->toBeNull();
        expect(Parish::find($originalParishId))->toBeNull();
        expect(Church::find($originalChurchId))->toBeNull();

        // Verify new data was imported correctly with new IDs
        $importedDiocese = Diocese::where('team_id', $this->team->id)->first();
        $importedParish = Parish::where('team_id', $this->team->id)->first();
        $importedChurch = Church::where('team_id', $this->team->id)->first();

        expect($importedDiocese)->not->toBeNull();
        expect($importedDiocese->name)->toBe($originalDioceseName);
        expect($importedDiocese->id)->not->toBe($originalDioceseId);

        expect($importedParish)->not->toBeNull();
        expect($importedParish->name)->toBe($originalParishName);
        expect($importedParish->diocese_id)->toBe($importedDiocese->id);

        expect($importedChurch)->not->toBeNull();
        expect($importedChurch->name)->toBe($originalChurchName);
        expect($importedChurch->parishable_id)->toBe($importedParish->id);
    });
});
