<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AcceptTeamInvitationController;

Route::get('team-invitations/{invitation}/accept', AcceptTeamInvitationController::class)
    ->middleware(['signed'])
    ->name('team-invitations.accept');

Route::get('auth/reset-password/{token}', fn () => view('index'))
    ->middleware(['guest:'.config('fortify.guard')])
    ->name('password.reset');

Route::get('auth/forgot-password', fn () => view('index'))
    ->middleware(['guest:'.config('fortify.guard')])
    ->name('forgot-password');

Route::get('{view?}', function (Request $request) {
    return view('index')->with(['auth' => with($request->user())?->info()]);
})
    ->where('view', '^(?!(api)).*$');
