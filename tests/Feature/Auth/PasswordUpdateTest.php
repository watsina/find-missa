<?php

use App\Models\User;
use Laravel\Fortify\Features;
use Illuminate\Support\Facades\Hash;

test('user cannot update password if feature is disabled', function () {
    $this->actingAs($user = User::factory()->create());

    $this->put('/api/v1/user/password', [
        'current_password' => 'password',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ])->assertNotFound();

    expect(Hash::check('password', $user->fresh()->password))->toBeTrue();
})->skip(function () {
    return Features::enabled(Features::updatePasswords());
}, 'Update passwords support is enabled.');

test('password can be updated', function () {
    $this->actingAs($user = User::factory()->create());

    $this->put(route('user-password.update'), [
        'current_password' => 'password',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    expect(Hash::check('new-password', $user->fresh()->password))->toBeTrue();
})->skip(function () {
    return ! Features::enabled(Features::updatePasswords());
}, 'Password update support is not enabled.');

test('current password must be correct', function () {
    $this->actingAs($user = User::factory()->create());

    $this->put(route('user-password.update'), [
        'current_password' => 'wrong-password',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ])->assertJsonValidationErrors(['current_password']);

    expect(Hash::check('password', $user->fresh()->password))->toBeTrue();
})->skip(function () {
    return ! Features::enabled(Features::updatePasswords());
}, 'Password update support is not enabled.');

test('new passwords must match', function () {
    $this->actingAs($user = User::factory()->create());

    $this->put(route('user-password.update'), [
        'current_password' => 'password',
        'password' => 'new-password',
        'password_confirmation' => 'wrong-password',
    ])->assertJsonValidationErrors(['password']);

    expect(Hash::check('password', $user->fresh()->password))->toBeTrue();
})->skip(function () {
    return ! Features::enabled(Features::updatePasswords());
}, 'Password update support is not enabled.');
