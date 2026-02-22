<?php

use App\Models\User;
use Laravel\Fortify\Features;

// Set Referer header so Sanctum's stateful middleware applies session handling
// This is needed because these tests use password.confirm middleware which requires sessions
beforeEach(function () {
    $this->withHeader('Referer', config('app.url'));
});

test('two factor authentication cannot be enabled if the feature is disabled', function () {
    $this->actingAs($user = User::factory()->create());

    $this->withSession(['auth.password_confirmed_at' => time()]);

    $this->post('/api/v1/user/two-factor-authentication');

    expect($user->fresh()->two_factor_secret)->toBeNull();
})->skip(function () {
    return Features::canManageTwoFactorAuthentication();
}, 'Two factor authentication feature is enabled.');

test('two factor authentication can be enabled', function () {
    $this->actingAs($user = User::factory()->create());

    $this->withSession(['auth.password_confirmed_at' => time()]);

    $this->post(route('two-factor.enable'));

    expect($user->fresh()->two_factor_secret)->not->toBeNull();
    expect($user->fresh()->recoveryCodes())->toHaveCount(8);
})->skip(function () {
    return ! Features::canManageTwoFactorAuthentication();
}, 'Two factor authentication is not enabled.');

test('recovery codes can be regenerated', function () {
    $this->actingAs($user = User::factory()->create());

    $this->withSession(['auth.password_confirmed_at' => time()]);

    $this->post(route('two-factor.enable'));
    $this->post('two-factor.recovery-codes');

    $user = $user->fresh();

    $this->post(route('two-factor.recovery-codes'));

    expect($user->recoveryCodes())->toHaveCount(8);
    expect(array_diff($user->recoveryCodes(), $user->fresh()->recoveryCodes()))->toHaveCount(8);
})->skip(function () {
    return ! Features::canManageTwoFactorAuthentication();
}, 'Two factor authentication is not enabled.');

test('two factor authentication can be disabled', function () {
    $this->actingAs($user = User::factory()->create());

    $this->withSession(['auth.password_confirmed_at' => time()]);

    $this->post(route('two-factor.enable'));

    $this->assertNotNull($user->fresh()->two_factor_secret);

    $this->delete(route('two-factor.enable'));

    expect($user->fresh()->two_factor_secret)->toBeNull();
})->skip(function () {
    return ! Features::canManageTwoFactorAuthentication();
}, 'Two factor authentication is not enabled.');
