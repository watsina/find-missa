<?php

use App\Models\Team;
use App\Models\Church;
use App\Models\Parish;
use App\Models\Diocese;
use App\Rules\ExistsInTeam;
use Illuminate\Support\Facades\Validator;

beforeEach(function () {
    $this->actingAsSuperAdminWithTeam();
});

test('passes when model exists in current team', function () {
    $church = Church::factory()->create();

    $validator = Validator::make(
        ['church_id' => $church->id],
        ['church_id' => ['required', new ExistsInTeam(Church::class)]]
    );

    expect($validator->passes())->toBeTrue();
});

test('fails when model exists but in different team', function () {
    // Create a church in the current team
    $currentTeamChurch = Church::factory()->create();

    // Create another team and a church in that team
    $otherTeam = Team::factory()->create();
    session()->put('current_team_id', $otherTeam->id);
    $otherTeamChurch = Church::factory()->create();

    // Switch back to original team
    session()->put('current_team_id', $currentTeamChurch->team_id);

    // Try to validate the other team's church - should fail
    $validator = Validator::make(
        ['church_id' => $otherTeamChurch->id],
        ['church_id' => ['required', new ExistsInTeam(Church::class)]]
    );

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->first('church_id'))->toBe('The selected church id is invalid.');
});

test('fails when model does not exist at all', function () {
    $validator = Validator::make(
        ['church_id' => 99999],
        ['church_id' => ['required', new ExistsInTeam(Church::class)]]
    );

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->first('church_id'))->toBe('The selected church id is invalid.');
});

test('passes when value is null', function () {
    $validator = Validator::make(
        ['church_id' => null],
        ['church_id' => ['nullable', new ExistsInTeam(Church::class)]]
    );

    expect($validator->passes())->toBeTrue();
});

test('works with different model types', function () {
    $diocese = Diocese::factory()->create();

    $validator = Validator::make(
        ['diocese_id' => $diocese->id],
        ['diocese_id' => ['required', new ExistsInTeam(Diocese::class)]]
    );

    expect($validator->passes())->toBeTrue();
});

test('works with custom column name', function () {
    $parish = Parish::factory()->create(['name' => 'St. Johns Parish']);

    $validator = Validator::make(
        ['parish_name' => 'St. Johns Parish'],
        ['parish_name' => ['required', new ExistsInTeam(Parish::class, 'name')]]
    );

    expect($validator->passes())->toBeTrue();
});

test('fails with custom column when value does not match', function () {
    Parish::factory()->create(['name' => 'St. Johns Parish']);

    $validator = Validator::make(
        ['parish_name' => 'Non-existent Parish'],
        ['parish_name' => ['required', new ExistsInTeam(Parish::class, 'name')]]
    );

    expect($validator->fails())->toBeTrue();
});
