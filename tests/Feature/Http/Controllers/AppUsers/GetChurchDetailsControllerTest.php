<?php

namespace Tests\Feature\Http\Controllers\AppUsers;

use App\Models\Church;
use App\Models\ActivityTime;

use function Pest\Laravel\getJson;

test('it shows the church details with the activities grouped.', function () {
    $church = Church::factory()->create();
    ActivityTime::factory(3)->recycle($church)->create(['type' => 'mass']);
    ActivityTime::factory(2)->recycle($church)->create(['type' => 'adoration']);
    ActivityTime::factory(1)->recycle($church)->create(['type' => 'confession']);

    getJson(route('church-details', ['church' => $church->id]))
        ->assertOk()
        ->assertJsonPath('data.details.id', $church->id)
        ->assertJsonCount(3, 'data.mass')
        ->assertJsonCount(2, 'data.adoration')
        ->assertJsonCount(1, 'data.confession');
});
