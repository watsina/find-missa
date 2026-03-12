<?php

namespace Tests\Feature\Http\Controllers;

use function Pest\Laravel\get;
use function Pest\Laravel\put;
use function Pest\Laravel\post;
use function Pest\Laravel\delete;

use App\Models\GlobalActivityTimeException;

use function Pest\Laravel\assertModelMissing;

use App\Http\Requests\GlobalActivityTimeExceptionRequest;
use App\Http\Controllers\GlobalActivityTimeExceptionController;

test('index behaves as expected returns the expected number of items', function (): void {
    $this->actingAsSuperAdminWithTeam();

    GlobalActivityTimeException::factory()->count(3)->create();

    get(route('global-activity-time-exceptions.index'))
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

test('store uses form request validation')
    ->assertActionUsesFormRequest(
        GlobalActivityTimeExceptionController::class,
        'store',
        GlobalActivityTimeExceptionRequest::class
    );

test('store saves', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $data = GlobalActivityTimeException::factory()->raw();

    post(route('global-activity-time-exceptions.store'), $data)
        ->assertCreated()
        ->assertJsonStructure([]);

    $globalActivityTimeExceptions = GlobalActivityTimeException::query()
        ->where('reason', $data['reason'])
        ->get();

    expect($globalActivityTimeExceptions)->toHaveCount(1);
});

test('show behaves as expected', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $globalActivityTimeException = GlobalActivityTimeException::factory()->create();

    get(route('global-activity-time-exceptions.show', $globalActivityTimeException))->assertOk()->assertJsonStructure([]);
});

test('update uses form request validation')
    ->assertActionUsesFormRequest(
        GlobalActivityTimeExceptionController::class,
        'update',
        GlobalActivityTimeExceptionRequest::class
    );

test('update behaves as expected', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $globalActivityTimeException = GlobalActivityTimeException::factory()->create();
    $data = GlobalActivityTimeException::factory()->raw();

    put(route('global-activity-time-exceptions.update', $globalActivityTimeException), $data)
        ->assertOk()
        ->assertJsonStructure([]);

    $globalActivityTimeException->refresh();

    expect($data['start_date'])->toEqual($globalActivityTimeException->start_date->format('Y-m-d'));
    expect($data['end_date'])->toEqual($globalActivityTimeException->end_date->format('Y-m-d'));
});

test('destroy deletes and responds with', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $globalActivityTimeException = GlobalActivityTimeException::factory()->create();

    delete(route('global-activity-time-exceptions.destroy', $globalActivityTimeException))->assertNoContent();

    assertModelMissing($globalActivityTimeException);
});
