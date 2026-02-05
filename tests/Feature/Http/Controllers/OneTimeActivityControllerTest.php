<?php

use App\Models\Church;

use function Pest\Laravel\get;
use function Pest\Laravel\put;

use App\Models\OneTimeActivity;

use function Pest\Laravel\post;
use function Pest\Laravel\delete;
use function Pest\Laravel\assertModelMissing;

test('user can list one-time activities for a church', function () {
    $this->actingAsSuperAdminWithTeam();

    $church = Church::factory()->create();

    // Create activities for this church
    OneTimeActivity::factory()->count(3)->create([
        'church_id' => $church->id,
    ]);

    // Create activity for another church
    OneTimeActivity::factory()->create();

    $response = get(route('one-time-activities.index', ['church_id' => $church->id]));

    $response->assertSuccessful()
        ->assertJsonCount(3, 'data');
})->group('one-time-activities');

test('user can create a one-time activity', function () {
    $this->actingAsSuperAdminWithTeam();

    $church = Church::factory()->create();

    $data = [
        'church_id' => $church->id,
        'type' => 'mass',
        'activity_type_description' => 'easter-mass',
        'start_time' => '10:00',
        'end_time' => '11:00',
        'language' => 'English',
        'date' => '2025-04-20',
    ];

    $response = post(route('one-time-activities.store'), $data);

    $response->assertCreated();

    $activity = OneTimeActivity::where('church_id', $church->id)
        ->where('date', '2025-04-20')
        ->first();

    expect($activity)->not->toBeNull();
    expect($activity->type)->toBe('mass');
    expect($activity->activity_type_description)->toBe('easter-mass');
})->group('one-time-activities');

test('user can view a one-time activity', function () {
    $this->actingAsSuperAdminWithTeam();

    $activity = OneTimeActivity::factory()->create();

    $response = get(route('one-time-activities.show', $activity));

    $response->assertSuccessful()
        ->assertJsonPath('data.id', $activity->id);
})->group('one-time-activities');

test('user can update a one-time activity', function () {
    $this->actingAsSuperAdminWithTeam();

    $activity = OneTimeActivity::factory()->create([
        'start_time' => '08:00',
    ]);

    $data = [
        'church_id' => $activity->church_id,
        'type' => $activity->type,
        'activity_type_description' => $activity->activity_type_description,
        'start_time' => '09:00',
        'language' => 'Spanish',
        'date' => $activity->date,
    ];

    $response = put(route('one-time-activities.update', $activity), $data);

    $response->assertSuccessful();

    $activity->refresh();

    expect($activity->start_time)->toBe('09:00');
    expect($activity->language)->toBe('Spanish');
})->group('one-time-activities');

test('admin can delete a one-time activity', function () {
    $this->actingAsSuperAdminWithTeam();

    $activity = OneTimeActivity::factory()->create();

    $response = delete(route('one-time-activities.destroy', $activity));

    $response->assertNoContent();

    assertModelMissing($activity);
})->group('one-time-activities');

test('index can filter by date range', function () {
    $this->actingAsSuperAdminWithTeam();

    $activity1 = OneTimeActivity::factory()->onDate('2025-04-01')->create();
    $activity2 = OneTimeActivity::factory()->onDate('2025-04-15')->create();
    $activity3 = OneTimeActivity::factory()->onDate('2025-05-01')->create();

    $response = get(route('one-time-activities.index', [
        'date_from' => '2025-04-01',
        'date_to' => '2025-04-30',
    ]));

    $response->assertSuccessful()
        ->assertJsonCount(2, 'data');

    $ids = collect($response->json('data'))->pluck('id')->toArray();
    expect($ids)->toContain($activity1->id);
    expect($ids)->toContain($activity2->id);
    expect($ids)->not->toContain($activity3->id);
})->group('one-time-activities');

test('index can filter by activity type', function () {
    $this->actingAsSuperAdminWithTeam();

    $massActivity = OneTimeActivity::factory()->ofType('mass')->create();
    $confessionActivity = OneTimeActivity::factory()->ofType('confession')->create();

    $response = get(route('one-time-activities.index', ['type' => 'mass']));

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $massActivity->id);
})->group('one-time-activities');
