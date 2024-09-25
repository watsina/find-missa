<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JsonMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /**
         * Ensure API request has the correct header (application/json)
         * This Forces laravel to return json responses for validation errors
         * instead of rendering views when the users forget to set the right header
         * */
        if (! $request->headers->has('Content-Type')) {
            $request->headers->set('Content-Type', 'application/json');
        }

        $request->headers->set('accept', 'application/json');

        return $next($request);
    }
}
