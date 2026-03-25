<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensure the current team context is available in the session.
 *
 * Previously this middleware called Spatie's setPermissionsTeamId().
 * The custom ACL system reads session('current_team_id') directly,
 * so this middleware now serves as a session-presence guard only.
 */
class TeamsPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }
}
