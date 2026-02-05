<?php

use App\Models\Church;
use App\Models\Holiday;
use App\Enums\ScheduleScope;
use App\Models\ScheduleRule;
use App\Enums\ScheduleAction;

use function Pest\Laravel\get;
use function Pest\Laravel\put;
use function Pest\Laravel\post;
use function Pest\Laravel\delete;

use App\Enums\ScheduleRuleCondition;

use function Pest\Laravel\assertModelMissing;

test('user can list schedule rules for a church', function () {
    $this->actingAsSuperAdminWithTeam();

    $church = Church::factory()->create();

    // Create rules for this church
    ScheduleRule::factory()->count(3)->forChurch($church)->create();

    // Create rule for another church
    ScheduleRule::factory()->create();

    $response = get(route('schedule-rules.index', [
        'scope_type' => ScheduleScope::Church->value,
        'scope_id' => $church->id,
    ]));

    $response->assertSuccessful()
        ->assertJsonCount(3, 'data');
})->group('schedule-rules');

test('user can create a schedule rule', function () {
    $this->actingAsSuperAdminWithTeam();

    $church = Church::factory()->create();
    $holiday = Holiday::factory()->goodFriday()->create();

    $data = [
        'scope_type' => ScheduleScope::Church->value,
        'scope_id' => $church->id,
        'name' => 'No Mass on Good Friday',
        'condition_type' => ScheduleRuleCondition::LiturgicalDay->value,
        'condition_holidays' => [$holiday->slug],
        'action' => ScheduleAction::Exclude->value,
        'activity_type' => 'mass',
        'is_active' => true,
        'priority' => 10,
    ];

    $response = post(route('schedule-rules.store'), $data);

    $response->assertCreated();

    $rule = ScheduleRule::where('scope_id', $church->id)
        ->where('name', 'No Mass on Good Friday')
        ->first();

    expect($rule)->not->toBeNull();
    expect($rule->condition_type->value)->toBe(ScheduleRuleCondition::LiturgicalDay->value);
    expect($rule->action->value)->toBe(ScheduleAction::Exclude->value);
})->group('schedule-rules');

test('user can view a schedule rule', function () {
    $this->actingAsSuperAdminWithTeam();

    $rule = ScheduleRule::factory()->create();

    $response = get(route('schedule-rules.show', $rule));

    $response->assertSuccessful()
        ->assertJsonPath('data.id', $rule->id);
})->group('schedule-rules');

test('user can update a schedule rule', function () {
    $this->actingAsSuperAdminWithTeam();

    $rule = ScheduleRule::factory()->create([
        'name' => 'Original Name',
        'priority' => 0,
    ]);

    $data = [
        'scope_type' => $rule->scope_type->value,
        'scope_id' => $rule->scope_id,
        'name' => 'Updated Name',
        'condition_type' => $rule->condition_type->value,
        'action' => $rule->action->value,
        'is_active' => true,
        'priority' => 20,
    ];

    $response = put(route('schedule-rules.update', $rule), $data);

    $response->assertSuccessful();

    $rule->refresh();

    expect($rule->name)->toBe('Updated Name');
    expect($rule->priority)->toBe(20);
})->group('schedule-rules');

test('admin can delete a schedule rule', function () {
    $this->actingAsSuperAdminWithTeam();

    $rule = ScheduleRule::factory()->create();

    $response = delete(route('schedule-rules.destroy', $rule));

    $response->assertNoContent();

    assertModelMissing($rule);
})->group('schedule-rules');

test('index can filter by active status', function () {
    $this->actingAsSuperAdminWithTeam();

    $activeRule = ScheduleRule::factory()->create(['is_active' => true]);
    $inactiveRule = ScheduleRule::factory()->inactive()->create();

    $response = get(route('schedule-rules.index', ['is_active' => 'true']));

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $activeRule->id);
})->group('schedule-rules');

test('index can filter by condition type', function () {
    $this->actingAsSuperAdminWithTeam();

    $publicHolidayRule = ScheduleRule::factory()->onPublicHolidays()->create();
    $liturgicalRule = ScheduleRule::factory()->onLiturgicalDays(['easter'])->create();

    $response = get(route('schedule-rules.index', [
        'condition_type' => ScheduleRuleCondition::PublicHoliday->value,
    ]));

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $publicHolidayRule->id);
})->group('schedule-rules');
