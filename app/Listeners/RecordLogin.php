<?php

namespace App\Listeners;

use App\Models\Login;
use Illuminate\Support\Facades\Request;
use Illuminate\Auth\Events\Login as LoginEvent;

class RecordLogin
{
    /**
     * Handle the event.
     */
    public function handle(LoginEvent $event): void
    {
        Login::create([
            'user_id' => $event->user->id,
            'ip_address' => Request::ip(),
        ]);
    }
}
