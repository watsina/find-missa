<?php

use App\Models\User;
use Laravel\Fortify\Features;

use function PHPUnit\Framework\assertNotNull;
use function PHPUnit\Framework\assertNotSame;

test('profile information cannot be updated if feature is disabled', function () {
    $user = User::factory()->create();

    $this
        ->actingAs($user)
        ->patch('api/v1/user/profile-information', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ])
        ->assertNotFound();

    $user->refresh();

    assertNotSame('Test User', $user->name);
    assertNotSame('test@example.com', $user->email);
    assertNotNull($user->email_verified_at);
})->skip(function () {
    return Features::enabled(Features::updateProfileInformation());
}, 'Update profile information support is enabled.');

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $this
        ->actingAs($user)
        ->put(route('user-profile-information.update'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ])
        ->assertOk();

    $user->refresh();

    $this->assertSame('Test User', $user->name);
    $this->assertSame('test@example.com', $user->email);

    if (Features::enabled(Features::emailVerification())) {
        $this->assertNull($user->email_verified_at);
    }
})->skip(function () {
    return ! Features::enabled(Features::updateProfileInformation());
}, 'Update profile information support is not enabled.');

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->put(route('user-profile-information.update'), [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertOk();

    if (Features::enabled(Features::emailVerification())) {
        $this->assertNotNull($user->refresh()->email_verified_at);
    }
})->skip(function () {
    return ! Features::enabled(Features::updateProfileInformation());
}, 'Update profile information support is not enabled.');
