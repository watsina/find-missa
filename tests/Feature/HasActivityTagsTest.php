<?php

namespace Tests\Feature;

use App\Models\ActivityTag;
use App\Models\ActivityTime;
use Database\Seeders\ActivityTagSeeder;

beforeEach(function () {
    $this->seed(ActivityTagSeeder::class);
});

test('activity time auto-assigns time of day tags on create', function (string $startTime, string $expectedSlug): void {
    $this->actingAsSuperAdminWithTeam();

    $activityTime = ActivityTime::factory()->create([
        'start_time' => $startTime,
        'occurrence' => 'daily',
    ]);

    expect($activityTime->hasTag($expectedSlug))->toBeTrue();
})->with([
    'early morning (5:00)' => ['05:00', 'early-morning'],
    'morning (7:00)' => ['07:00', 'morning'],
    'morning (10:30)' => ['10:30', 'morning'],
    'lunch hour (12:00)' => ['12:00', 'lunch-hour'],
    'lunch hour (13:30)' => ['13:30', 'lunch-hour'],
    'afternoon (14:00)' => ['14:00', 'afternoon'],
    'afternoon (16:00)' => ['16:00', 'afternoon'],
    'evening (17:00)' => ['17:00', 'evening'],
    'evening (19:00)' => ['19:00', 'evening'],
    'night (20:00)' => ['20:00', 'night'],
    'night (23:00)' => ['23:00', 'night'],
]);

test('activity time auto-assigns day type tags on create for weekly occurrence', function (string $dayOfWeek, string $expectedSlug): void {
    $this->actingAsSuperAdminWithTeam();

    $activityTime = ActivityTime::factory()->weekly()->create([
        'day_of_week' => $dayOfWeek,
        'start_time' => '10:00',
    ]);

    expect($activityTime->hasTag($expectedSlug))->toBeTrue();
})->with([
    'monday' => ['Monday', 'weekday'],
    'tuesday' => ['Tuesday', 'weekday'],
    'wednesday' => ['Wednesday', 'weekday'],
    'thursday' => ['Thursday', 'weekday'],
    'friday' => ['Friday', 'weekday'],
    'saturday' => ['Saturday', 'saturday'],
    'sunday' => ['Sunday', 'sunday'],
]);

test('activity time auto-assigns daily tag for daily occurrence', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $activityTime = ActivityTime::factory()->create([
        'occurrence' => 'daily',
        'start_time' => '10:00',
    ]);

    expect($activityTime->hasTag('daily'))->toBeTrue();
});

test('activity time re-assigns tags when start_time changes', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $activityTime = ActivityTime::factory()->create([
        'start_time' => '10:00', // morning
        'occurrence' => 'daily',
    ]);

    expect($activityTime->hasTag('morning'))->toBeTrue();
    expect($activityTime->hasTag('evening'))->toBeFalse();

    $activityTime->update(['start_time' => '18:00']); // evening

    expect($activityTime->hasTag('morning'))->toBeFalse();
    expect($activityTime->hasTag('evening'))->toBeTrue();
});

test('activity time re-assigns tags when day_of_week changes', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $activityTime = ActivityTime::factory()->weekly()->create([
        'start_time' => '10:00',
        'day_of_week' => 'Monday',
    ]);

    expect($activityTime->hasTag('weekday'))->toBeTrue();
    expect($activityTime->hasTag('sunday'))->toBeFalse();

    $activityTime->update(['day_of_week' => 'Sunday']);

    expect($activityTime->hasTag('weekday'))->toBeFalse();
    expect($activityTime->hasTag('sunday'))->toBeTrue();
});

test('syncCustomTags preserves system tags', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $activityTime = ActivityTime::factory()->create([
        'start_time' => '10:00',
        'occurrence' => 'daily',
    ]);

    // Create a custom tag
    $customTag = ActivityTag::factory()->create([
        'is_system' => false,
        'slug' => 'custom-test-tag',
    ]);

    // Sync custom tag
    $activityTime->syncCustomTags([$customTag->id]);

    // Should have both system and custom tags
    expect($activityTime->hasTag('morning'))->toBeTrue();
    expect($activityTime->hasTag('daily'))->toBeTrue();
    expect($activityTime->hasTag('custom-test-tag'))->toBeTrue();
});

test('hasAnyTag returns true when activity has at least one matching tag', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $activityTime = ActivityTime::factory()->create([
        'start_time' => '10:00',
        'occurrence' => 'daily',
    ]);

    expect($activityTime->hasAnyTag(['morning', 'evening']))->toBeTrue();
    expect($activityTime->hasAnyTag(['evening', 'night']))->toBeFalse();
});

test('hasAllTags returns true only when activity has all matching tags', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $activityTime = ActivityTime::factory()->create([
        'start_time' => '10:00',
        'occurrence' => 'daily',
    ]);

    expect($activityTime->hasAllTags(['morning', 'daily']))->toBeTrue();
    expect($activityTime->hasAllTags(['morning', 'sunday']))->toBeFalse();
});

test('systemTags relationship returns only system tags', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $activityTime = ActivityTime::factory()->create([
        'start_time' => '10:00',
        'occurrence' => 'daily',
    ]);

    $customTag = ActivityTag::factory()->create(['is_system' => false]);
    $activityTime->syncCustomTags([$customTag->id]);

    $systemTags = $activityTime->systemTags;

    expect($systemTags)->each(fn ($tag) => $tag->is_system->toBeTrue());
});

test('customTags relationship returns only custom tags', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $activityTime = ActivityTime::factory()->create([
        'start_time' => '10:00',
        'occurrence' => 'daily',
    ]);

    $customTag = ActivityTag::factory()->create(['is_system' => false]);
    $activityTime->syncCustomTags([$customTag->id]);

    $customTags = $activityTime->customTags;

    expect($customTags)->toHaveCount(1);
    expect($customTags->first()->is_system)->toBeFalse();
});

test('userVisibleTags relationship returns only user visible tags', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $activityTime = ActivityTime::factory()->weekly()->create([
        'start_time' => '10:00',
        'day_of_week' => 'Sunday',
    ]);

    $userVisibleTags = $activityTime->userVisibleTags;

    // morning tag is user visible, sunday tag is not
    expect($userVisibleTags)->each(fn ($tag) => $tag->is_user_visible->toBeTrue());
    expect($userVisibleTags->pluck('slug'))->toContain('morning');
    expect($userVisibleTags->pluck('slug'))->not->toContain('sunday');
});
