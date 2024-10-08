<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\StatefulGuard;
use Laravel\Fortify\Actions\ConfirmPassword;
use Illuminate\Validation\ValidationException;

class OtherBrowserSessionsController extends Controller
{
    /**
     * Log out from other browser sessions.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request, StatefulGuard $guard)
    {
        $confirmed = app(ConfirmPassword::class)(
            $guard,
            $request->user(),
            $request->password
        );

        if (! $confirmed) {
            throw ValidationException::withMessages([
                'password' => __('The password is incorrect.'),
            ]);
        }

        $guard->logoutOtherDevices($request->password);

        $this->deleteOtherSessionRecords($request);

        return response()->noContent();
    }

    /**
     * Delete the other browser session records from storage.
     *
     * @return void
     */
    protected function deleteOtherSessionRecords(Request $request)
    {
        if (config('session.driver') !== 'database') {
            return;
        }

        DB::connection(config('session.connection'))->table(config('session.table', 'sessions'))
            ->where('user_id', $request->user()->getAuthIdentifier())
            ->where('id', '!=', $request->session()->getId())
            ->delete();
    }
}
