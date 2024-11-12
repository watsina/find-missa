<?php

namespace Tests;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use JMac\Testing\Traits\AdditionalAssertions;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use AdditionalAssertions;
    use LazilyRefreshDatabase;

    public function actingAsUser(User $user)
    {
        Auth::login($user);

        return $this;
    }

    public function actingAsRandomUser(array $attributes = [])
    {
        $user = User::factory()->create($attributes);

        Auth::login($user);

        return $this;
    }

    public function actingAsUserWithTeam(array $attributes = [])
    {
        $user = User::factory()->withTeam()->create($attributes);

        Auth::login($user);

        return $this;
    }

    public function actingAsSuperAdmin()
    {
        return $this->actingAsRandomUser(['is_super_admin' => true]);
    }

    public function actingAsSuperAdminWithTeam()
    {
        return $this->actingAsUserWithTeam(['is_super_admin' => true]);
    }
}
