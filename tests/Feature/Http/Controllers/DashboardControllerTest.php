<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Church;
use App\Models\Parish;
use App\Models\Priest;
use App\Models\Diocese;
use App\Models\SubParish;
use App\Models\ActivityTime;

use function Pest\Laravel\get;

test('it returns the expected stats.', function () {
    $dioceses = Diocese::factory(8)->create();
    $parishes = Parish::factory(10)->recycle($dioceses)->create();
    SubParish::factory(12)->recycle($parishes)->create();
    $churches = Church::factory(14)->recycle($parishes)->create(['parishable_id' => null, 'parishable_type' => null]);
    ActivityTime::factory(16)->recycle($churches)->create();
    Priest::factory(18)->recycle($parishes)->create();

    $this->actingAsRandomUser();
    get(route('dashboard.index'))
        ->assertOk()
        ->assertJsonPath('stats.dioceses', 8)
        ->assertJsonPath('stats.parishes', 10)
        ->assertJsonPath('stats.subParishes', 12)
        ->assertJsonPath('stats.churches', 14)
        ->assertJsonPath('stats.activities', 16)
        ->assertJsonPath('stats.priests', 18);
});
