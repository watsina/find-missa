<?php

use App\Models\Team;
use App\Models\User;
use App\Models\Church;
use App\Models\Parish;
use App\Models\Diocese;
use App\Services\DataExportService;

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
});

describe('Full Data Export Controller', function () {
    it('requires authentication', function () {
        $this->getJson('/api/v1/full-export')
            ->assertUnauthorized();
    });

    it('requires admin access', function () {
        $this->actingAs($this->regularUser)
            ->getJson('/api/v1/full-export')
            ->assertForbidden();
    });

    it('allows admin users to export data', function () {
        // Create some test data
        $diocese = Diocese::factory()->create(['team_id' => $this->team->id]);
        $parish = Parish::factory()->create([
            'team_id' => $this->team->id,
            'diocese_id' => $diocese->id,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get('/api/v1/full-export');

        $response->assertSuccessful();

        // Get the streamed content
        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        // Parse the JSON response
        $data = json_decode($content, true);

        expect($data)->toHaveKeys(['exported_at', 'version', 'team_id', 'tables']);
        expect($data['team_id'])->toBe($this->team->id);
        expect($data['tables'])->toHaveKeys(['dioceses', 'parishes', 'churches']);
        expect($data['tables']['dioceses'])->toHaveCount(1);
        expect($data['tables']['parishes'])->toHaveCount(1);
    });

    it('only exports data for the current team', function () {
        // Create data for the admin user's team
        $diocese1 = Diocese::factory()->create(['team_id' => $this->team->id]);

        // Create data for another team
        $otherTeam = Team::factory()->create();
        $diocese2 = Diocese::factory()->create(['team_id' => $otherTeam->id]);

        $response = $this->actingAs($this->adminUser)
            ->get('/api/v1/full-export');

        $response->assertSuccessful();

        // Get the streamed content
        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        $data = json_decode($content, true);

        // Should only include the diocese from the admin's team
        expect($data['tables']['dioceses'])->toHaveCount(1);
        expect($data['tables']['dioceses'][0]['id'])->toBe($diocese1->id);
    });
});

describe('Data Export Service', function () {
    it('exports all domain tables in correct order', function () {
        $service = new DataExportService;

        $exportOrder = $service->getExportOrder();

        expect($exportOrder)->toHaveKeys([
            'dioceses',
            'parishes',
            'sub_parishes',
            'priests',
            'churches',
            'schedule_periods',
            'activity_times',
            'activity_schedules',
            'activity_time_exceptions',
            'holidays',
            'holiday_instances',
            'liturgical_schedules',
            'liturgical_schedule_times',
            'one_time_activities',
            'global_activity_time_exceptions',
            'schedule_rules',
            'schedule_overrides',
        ]);
    });

    it('returns deletion order as reverse of export order', function () {
        $service = new DataExportService;

        $exportOrder = array_keys($service->getExportOrder());
        $deletionOrder = array_keys($service->getDeletionOrder());

        expect($deletionOrder)->toBe(array_reverse($exportOrder));
    });

    it('exports data with proper structure', function () {
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

        $service = new DataExportService;
        $data = $service->export($this->team->id);

        expect($data['exported_at'])->not->toBeEmpty();
        expect($data['version'])->toBe('1.0');
        expect($data['team_id'])->toBe($this->team->id);
        expect($data['tables']['dioceses'])->toHaveCount(1);
        expect($data['tables']['parishes'])->toHaveCount(1);
        expect($data['tables']['churches'])->toHaveCount(1);

        // Verify the exported data contains correct IDs
        expect($data['tables']['dioceses'][0]['id'])->toBe($diocese->id);
        expect($data['tables']['parishes'][0]['diocese_id'])->toBe($diocese->id);
        expect($data['tables']['churches'][0]['parishable_id'])->toBe($parish->id);
    });
});
