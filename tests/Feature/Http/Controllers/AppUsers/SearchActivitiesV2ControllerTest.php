<?php

namespace Tests\Feature\Http\Controllers\AppUsers;

use App\Models\Church;
use App\Models\Parish;
use App\Models\Diocese;
use App\Models\SubParish;
use App\Models\ActivitySchedule;

use function Pest\Laravel\getJson;

test('search v2 returns activities with church hierarchy for parish', function () {
    $diocese = Diocese::factory()->create(['name' => 'Kampala Archdiocese']);
    $parish = Parish::factory()->create([
        'name' => 'Christ the King Parish',
        'diocese_id' => $diocese->id,
    ]);
    $church = Church::factory()->create([
        'parishable_id' => $parish->id,
        'parishable_type' => Parish::class,
    ]);

    ActivitySchedule::factory()->recycle($church)->sunday()->create([
        'type' => 'mass',
        'start_time' => '10:00',
    ]);

    $response = getJson(route('search.v2', [
        'type' => 'mass',
        'date' => now()->next('Sunday')->toDateString(),
    ]))->assertOk();

    $churchData = $response->json('data.0.church');
    expect($churchData)->toHaveKey('hierarchy');
    expect($churchData['hierarchy'])->toBe([
        'sub_parish' => null,
        'parish' => 'Christ the King Parish',
        'diocese' => 'Kampala Archdiocese',
    ]);
});

test('search v2 returns activities with church hierarchy for sub_parish', function () {
    $diocese = Diocese::factory()->create(['name' => 'Jinja Diocese']);
    $parish = Parish::factory()->create([
        'name' => 'St. James Parish',
        'diocese_id' => $diocese->id,
    ]);
    $subParish = SubParish::factory()->create([
        'name' => 'Holy Cross Sub-Parish',
        'parish_id' => $parish->id,
    ]);
    $church = Church::factory()->create([
        'parishable_id' => $subParish->id,
        'parishable_type' => SubParish::class,
    ]);

    ActivitySchedule::factory()->recycle($church)->sunday()->create([
        'type' => 'mass',
        'start_time' => '10:00',
    ]);

    $response = getJson(route('search.v2', [
        'type' => 'mass',
        'date' => now()->next('Sunday')->toDateString(),
    ]))->assertOk();

    $churchData = $response->json('data.0.church');
    expect($churchData)->toHaveKey('hierarchy');
    expect($churchData['hierarchy'])->toBe([
        'sub_parish' => 'Holy Cross Sub-Parish',
        'parish' => 'St. James Parish',
        'diocese' => 'Jinja Diocese',
    ]);
});

test('search v2 does not expose internal IDs in church data', function () {
    $church = Church::factory()->create();

    ActivitySchedule::factory()->recycle($church)->sunday()->create([
        'type' => 'mass',
        'start_time' => '10:00',
    ]);

    $response = getJson(route('search.v2', [
        'type' => 'mass',
        'date' => now()->next('Sunday')->toDateString(),
    ]))->assertOk();

    $churchData = $response->json('data.0.church');

    // Should NOT expose internal IDs
    expect($churchData)->not->toHaveKey('parishable_id');
    expect($churchData)->not->toHaveKey('parishable_type');
    expect($churchData)->not->toHaveKey('priest_id');
    expect($churchData)->not->toHaveKey('parish');

    // Should have hierarchy instead
    expect($churchData)->toHaveKey('hierarchy');
});

test('search v2 returns activities sorted by start time', function () {
    $church = Church::factory()->create();

    ActivitySchedule::factory()->recycle($church)->sunday()->create([
        'type' => 'mass',
        'start_time' => '18:00',
    ]);
    ActivitySchedule::factory()->recycle($church)->sunday()->create([
        'type' => 'mass',
        'start_time' => '08:00',
    ]);
    ActivitySchedule::factory()->recycle($church)->sunday()->create([
        'type' => 'mass',
        'start_time' => '12:00',
    ]);

    $response = getJson(route('search.v2', [
        'type' => 'mass',
        'date' => now()->next('Sunday')->toDateString(),
    ]))->assertOk();

    $startTimes = collect($response->json('data'))->pluck('start_time')->all();
    expect($startTimes)->toBe(['08:00', '12:00', '18:00']);
});
