<?php

namespace Tests;

use App\Models\User;
use JMac\Testing\Traits\AdditionalAssertions;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use AdditionalAssertions;
    use LazilyRefreshDatabase;

    public function actingAsRandomUser(array $attributes = [])
    {
        $user = User::factory()->create($attributes);

        /** @var \Illuminate\Contracts\Auth\Authenticatable $user */
        $this->actingAs($user);

        return $this;
    }

    public function actingAsSuperAdmin()
    {
        return $this->actingAsRandomUser(['is_super_admin' => true]);
    }
}
