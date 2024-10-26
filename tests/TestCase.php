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

    public function signIn(?User $user = null)
    {
        $user = $user ?? User::factory()->create();

        $this->actingAs($user);

        return $this;
    }
}
